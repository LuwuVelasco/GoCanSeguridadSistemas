<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
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
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $id_usuario = (int)$data['id_usuario'];

  // 1) Obtener configuración más reciente (tiempo_vida_util en días)
  $stmtCfg = $pdo->query("SELECT tiempo_vida_util FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
  $cfg = $stmtCfg->fetch(PDO::FETCH_ASSOC);
  if (!$cfg) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseñas"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $tiempo_vida_util = (int)$cfg['tiempo_vida_util'];

  // 2) Obtener la contraseña activa más reciente del usuario
  $stmtHist = $pdo->prepare(
    "SELECT fecha_creacion
       FROM historial_passwords
      WHERE id_usuario = :id AND estado = TRUE
      ORDER BY fecha_creacion DESC
      LIMIT 1"
  );
  $stmtHist->execute([':id' => $id_usuario]);
  $registro = $stmtHist->fetch(PDO::FETCH_ASSOC);

  if (!$registro || empty($registro['fecha_creacion'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró una contraseña activa para el usuario"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 3) Cálculo de expiración
  $fecha_creacion   = new DateTime($registro['fecha_creacion']); // viene en timestamp DB
  $fecha_expiracion = (clone $fecha_creacion)->add(new DateInterval("P{$tiempo_vida_util}D"));
  $ahora            = new DateTime();

  if ($ahora > $fecha_expiracion) {
    echo json_encode(["estado" => "error", "mensaje" => "La contraseña ha expirado"], JSON_UNESCAPED_UNICODE);
  } else {
    echo json_encode(["estado" => "success", "mensaje" => "La contraseña sigue siendo válida"], JSON_UNESCAPED_UNICODE);
  }
} catch (Throwable $e) {
  error_log('validar_tiempo_vida_password error: ' . $e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error del servidor"], JSON_UNESCAPED_UNICODE);
}
