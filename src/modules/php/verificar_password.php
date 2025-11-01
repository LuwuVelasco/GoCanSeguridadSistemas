<?php
declare(strict_types=1);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
  'http://127.0.0.1:5500',
  'http://localhost:5500'
];
if (in_array($origin, $allowed_origins)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

date_default_timezone_set('America/La_Paz');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Acepta JSON o x-www-form-urlencoded
  $raw  = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = $_POST; }

  if (!isset($data['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Falta el ID de usuario"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $id_usuario = (int)$data['id_usuario'];

  // 1) Rol del usuario
  $stmtRol = $pdo->prepare(
    "SELECT u.rol_id, r.nombre_rol
       FROM usuario u
       INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
      WHERE u.id_usuario = :id"
  );
  $stmtRol->execute([':id' => $id_usuario]);
  $rowRol = $stmtRol->fetch(PDO::FETCH_ASSOC);

  if (!$rowRol) {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $nombreRol = (string)$rowRol['nombre_rol'];

  // 2) Configuración de contraseñas más reciente
  $cfg = $pdo->query("SELECT id_configuracion, tiempo_vida_util
                        FROM configuracion_passwords
                       ORDER BY id_configuracion DESC
                       LIMIT 1")->fetch(PDO::FETCH_ASSOC);
  if (!$cfg) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $id_configuracion = (int)$cfg['id_configuracion'];
  $tiempo_vida_util = (int)$cfg['tiempo_vida_util']; // en días

  // 3) Contraseña vigente del usuario (última activa con esa configuración)
  $stmtHist = $pdo->prepare(
    "SELECT fecha_creacion
       FROM historial_passwords
      WHERE id_usuario = :id
        AND estado = TRUE
        AND id_configuracion = :cfg
      ORDER BY fecha_creacion DESC
      LIMIT 1"
  );
  $stmtHist->execute([':id' => $id_usuario, ':cfg' => $id_configuracion]);
  $hist = $stmtHist->fetch(PDO::FETCH_ASSOC);

  if (!$hist || empty($hist['fecha_creacion'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró contraseña vigente para el usuario"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 4) Revisar expiración
  $fecha_creacion   = new DateTime($hist['fecha_creacion']);
  $fecha_expiracion = (clone $fecha_creacion)->add(new DateInterval("P{$tiempo_vida_util}D"));
  $ahora            = new DateTime();

  if ($ahora > $fecha_expiracion) {
    echo json_encode([
      "estado"     => "expired",
      "mensaje"    => "Tu contraseña ha expirado. Por favor, cámbiala para continuar.",
      "id_usuario" => $id_usuario
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 5) Si NO es Cliente y solo tiene 1 registro en historial => forzar cambio
  $stmtTotal = $pdo->prepare("SELECT COUNT(*) AS total FROM historial_passwords WHERE id_usuario = :id");
  $stmtTotal->execute([':id' => $id_usuario]);
  $totalPasswords = (int)$stmtTotal->fetchColumn();

  if ($nombreRol !== 'Cliente' && $totalPasswords === 1) {
    echo json_encode([
      "estado"     => "change_required",
      "mensaje"    => "Debes cambiar tu contraseña generada automáticamente.",
      "id_usuario" => $id_usuario
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // OK
  echo json_encode(["estado" => "success", "mensaje" => "Contraseña vigente"], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  error_log('verificar_password error: ' . $e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error del servidor"], JSON_UNESCAPED_UNICODE);
}
