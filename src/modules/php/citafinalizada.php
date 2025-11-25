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

$pdo = require __DIR__ . '/conexion.php'; // Debe devolver un PDO

$idCita = $_POST['id_cita'] ?? null;
if ($idCita === null) {
  $raw = file_get_contents('php://input');
  if ($raw) {
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($json['id_cita'])) {
      $idCita = $json['id_cita'];
    }
  }
}

// Validación básica
$idCita = filter_var($idCita, FILTER_VALIDATE_INT);
if (!$idCita || $idCita <= 0) {
  http_response_code(400);
  echo json_encode(['estado' => 'error', 'mensaje' => 'id_cita inválido']);
  exit;
}

try {
  // Eliminar la cita
  $stmt = $pdo->prepare('DELETE FROM cita WHERE id_cita = :id');
  $stmt->execute([':id' => $idCita]);

  if ($stmt->rowCount() === 0) {
    // No existía o ya fue eliminada
    echo json_encode(['estado' => 'error', 'mensaje' => 'La cita no existe']);
    exit;
  }

  echo json_encode(['estado' => 'success', 'mensaje' => 'Cita eliminada correctamente']);

} catch (Throwable $e) {
  error_log('citafinalizada.php: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error al eliminar la cita']);
}
