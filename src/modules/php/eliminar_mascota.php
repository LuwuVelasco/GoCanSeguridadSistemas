<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

// (Opcional) CORS para pruebas con Live Server (puerto 5500). Puedes quitarlo si no lo necesitas.
$allowed_origins = ['http://localhost:5500', 'http://127.0.0.1:5500', 'http://localhost', 'http://127.0.0.1'];
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

  // Acepta JSON o form-data
  $raw = file_get_contents('php://input');
  $asJson = json_decode($raw, true);
  if (is_array($asJson)) {
    $id_mascota = (int)($asJson['id_mascota'] ?? 0);
  } else {
    $id_mascota = isset($_POST['id_mascota']) ? (int)$_POST['id_mascota'] : 0;
  }

  if ($id_mascota <= 0) {
    echo json_encode(['estado'=>'error','mensaje'=>'ID de mascota no válido']);
    exit;
  }

  $stmt = $pdo->prepare('DELETE FROM mascota WHERE id_mascota = :id');
  $stmt->execute([':id' => $id_mascota]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['estado'=>'success','mensaje'=>'Mascota eliminada exitosamente']);
  } else {
    echo json_encode(['estado'=>'error','mensaje'=>'No se encontró la mascota (o ya fue eliminada)']);
  }

} catch (PDOException $e) {
  // Si en el futuro hay FKs que impidan borrar, podrías detectar SQLSTATE 23503 aquí
  error_log('eliminar_mascota.php PDO: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error al eliminar la mascota']);
} catch (Throwable $t) {
  error_log('eliminar_mascota.php: ' . $t->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error del servidor']);
}
