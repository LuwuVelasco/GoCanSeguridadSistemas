<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

// --- CORS básico (útil si llamas desde Live Server 5500) ---
$allowed_origins = [
  'http://localhost', 'http://localhost:5500',
  'http://127.0.0.1', 'http://127.0.0.1:5500'
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

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // ---- Entrada: soporta JSON o x-www-form-urlencoded ----
  $raw = file_get_contents('php://input');
  $asJson = json_decode($raw, true);
  if (is_array($asJson)) {
    $id_cita    = (int)($asJson['id_cita']    ?? 0);
    $id_usuario = (int)($asJson['id_usuario'] ?? 0);
  } else {
    $id_cita    = isset($_POST['id_cita'])    ? (int)$_POST['id_cita']    : 0;
    $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
  }

  if ($id_cita <= 0 || $id_usuario <= 0) {
    echo json_encode(["estado"=>"error","mensaje"=>"No se proporcionaron los datos necesarios (id_cita o id_usuario)"]);
    exit;
  }

  // ---- Verificar que la cita pertenezca al cliente ----
  $stmt = $pdo->prepare('SELECT id_cita FROM cita WHERE id_cita = :id_cita AND id_usuario = :id_usuario LIMIT 1');
  $stmt->execute([':id_cita' => $id_cita, ':id_usuario' => $id_usuario]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode(["estado"=>"error","mensaje"=>"No tienes permiso para eliminar esta cita o no existe"]);
    exit;
  }

  // ---- Eliminar la cita ----
  $del = $pdo->prepare('DELETE FROM cita WHERE id_cita = :id_cita AND id_usuario = :id_usuario');
  $del->execute([':id_cita' => $id_cita, ':id_usuario' => $id_usuario]);

  if ($del->rowCount() > 0) {
    echo json_encode(["estado"=>"success","mensaje"=>"Cita eliminada correctamente"]);
  } else {
    // Muy raro: existía, pero no se borró (condición cambió en simultáneo)
    echo json_encode(["estado"=>"error","mensaje"=>"No se pudo eliminar la cita"]);
  }

} catch (Throwable $e) {
  error_log('eliminar_cita_cliente.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(["estado"=>"error","mensaje"=>"Error del servidor"]);
}
