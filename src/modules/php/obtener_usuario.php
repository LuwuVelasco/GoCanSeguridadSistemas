<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/* (Opcional) CORS para desarrollo local */
$allowed_origins = ['http://localhost','http://127.0.0.1','http://localhost:5500','http://127.0.0.1:5500'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
  header('Access-Control-Allow-Credentials: true');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(204); exit; }

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Acepta JSON o x-www-form-urlencoded
  $raw = file_get_contents('php://input') ?: '';
  $json = json_decode($raw, true);
  $id_usuario = $json['id_usuario'] ?? ($_POST['id_usuario'] ?? $_GET['id_usuario'] ?? null);

  if ($id_usuario === null || !is_numeric($id_usuario)) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'No se encontró un id_usuario válido']);
    exit;
  }

  $id = (int)$id_usuario;

  $stmt = $pdo->prepare('SELECT nombre FROM usuario WHERE id_usuario = :id LIMIT 1');
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    echo json_encode(['estado' => 'success', 'nombre' => $row['nombre']]);
  } else {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Usuario no encontrado']);
  }

} catch (Throwable $e) {
  error_log('obtener_usuario.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error del servidor']);
}
