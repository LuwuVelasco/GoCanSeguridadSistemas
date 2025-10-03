<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

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
