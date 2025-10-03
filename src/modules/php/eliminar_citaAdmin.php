<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
session_start();

/* --- CORS útil si llamas desde Live Server (opcional) --- */
$allowed_origins = [
  'http://localhost','http://localhost:5500',
  'http://127.0.0.1','http://127.0.0.1:5500'
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

/* --- Solo POST --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["estado"=>"error","mensaje"=>"Método no permitido"]);
  exit;
}

/* --- Verificar rol de Administrador --- */
$rol    = $_SESSION['rol']    ?? null;   // p.ej. "Administrador"
$rol_id = $_SESSION['rol_id'] ?? null;   // p.ej. 1
if (!($rol === 'Administrador' || (int)$rol_id === 1)) {
  http_response_code(403);
  echo json_encode(["estado"=>"error","mensaje"=>"No autorizado"]);
  exit;
}

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Acepta JSON o x-www-form-urlencoded
  $raw = file_get_contents('php://input');
  $asJson = json_decode($raw, true);
  if (is_array($asJson)) {
    $id_cita = (int)($asJson['id_cita'] ?? 0);
  } else {
    $id_cita = isset($_POST['id_cita']) ? (int)$_POST['id_cita'] : 0;
  }

  if ($id_cita <= 0) {
    echo json_encode(["estado"=>"error","mensaje"=>"No se proporcionó el ID de la cita"]);
    exit;
  }

  // Admin puede eliminar cualquier cita por id
  $stmt = $pdo->prepare('DELETE FROM cita WHERE id_cita = :id_cita');
  $stmt->execute([':id_cita' => $id_cita]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(["estado"=>"success","mensaje"=>"Cita eliminada correctamente"]);
  } else {
    echo json_encode(["estado"=>"error","mensaje"=>"No se encontró la cita o ya fue eliminada"]);
  }

} catch (Throwable $e) {
  error_log('eliminar_citaAdmin.php error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(["estado"=>"error","mensaje"=>"Error del servidor"]);
}
