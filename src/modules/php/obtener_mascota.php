<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/* --- CORS básico para desarrollo local (opcional) --- */
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
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { http_response_code(204); exit; }

/* --- Entrada --- */
$id_mascota_raw = $_GET['id_mascota'] ?? '';
if ($id_mascota_raw === '' || !ctype_digit((string)$id_mascota_raw)) {
  echo json_encode(['estado' => 'error', 'mensaje' => 'ID de mascota no proporcionado o inválido']);
  exit;
}
$id_mascota = (int)$id_mascota_raw;

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  $sql = "
    SELECT 
      m.id_mascota,
      m.nombre_mascota,
      m.fecha_nacimiento,
      m.tipo,
      m.raza,
      u.nombre AS nombre_propietario
    FROM mascota m
    INNER JOIN usuario u ON m.id_usuario = u.id_usuario
    WHERE m.id_mascota = :id
    LIMIT 1
  ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':id' => $id_mascota]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    echo json_encode(['estado' => 'success', 'mascota' => $row]);
  } else {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Mascota no encontrada']);
  }

} catch (Throwable $e) {
  error_log('obtener_mascota.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error al obtener la mascota']);
}