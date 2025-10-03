<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

// (Opcional) CORS para desarrollo con Live Server
$allowed_origins = [
  'http://localhost', 'http://127.0.0.1',
  'http://localhost:5500', 'http://127.0.0.1:5500'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['estado'=>'error','mensaje'=>'Método no permitido']);
  exit;
}

date_default_timezone_set('America/La_Paz');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Aceptar JSON o x-www-form-urlencoded
  $raw  = file_get_contents('php://input');
  $json = json_decode($raw, true);

  $id_usuario = (int)($json['id_usuario'] ?? ($_POST['id_usuario'] ?? 0));
  $password   = (string)($json['password']    ?? ($_POST['password']    ?? ''));

  if ($id_usuario <= 0 || $password === '') {
    echo json_encode(['estado'=>'error','mensaje'=>'Faltan campos requeridos (id_usuario, password)']);
    exit;
  }

  // ⚠️ Si quieres guardar SIEMPRE el hash en historial, descomenta:
  $password = password_hash($password, PASSWORD_BCRYPT);

  // Obtener la configuración más reciente
  $stmtCfg = $pdo->query('SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1');
  $id_configuracion = (int)($stmtCfg->fetchColumn() ?: 0);

  if ($id_configuracion <= 0) {
    echo json_encode(['estado'=>'error','mensaje'=>'No se encontró configuración de contraseña']);
    exit;
  }

  // Insertar en historial_passwords
  $stmt = $pdo->prepare(
    'INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
     VALUES (:id_usuario, :password, NOW(), :id_configuracion, :estado)'
  );
  $ok = $stmt->execute([
    ':id_usuario'       => $id_usuario,
    ':password'         => $password,
    ':id_configuracion' => $id_configuracion,
    ':estado'           => true, // PDO enviará boolean a PostgreSQL
  ]);

  if ($ok) {
    echo json_encode(['estado'=>'success','mensaje'=>'Historial de contraseña guardado correctamente']);
  } else {
    echo json_encode(['estado'=>'error','mensaje'=>'No se pudo guardar el historial']);
  }

} catch (PDOException $e) {
  error_log('guardar_historial_password.php PDO: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error al guardar el historial de contraseña']);
} catch (Throwable $t) {
  error_log('guardar_historial_password.php: '.$t->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error del servidor']);
}
