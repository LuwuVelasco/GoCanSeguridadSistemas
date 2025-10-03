<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/* --- (Opcional) CORS para desarrollo local con Live Server --- */
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

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Traer todos los reportes
  $sql = "
    SELECT 
      propietario,
      sintomas,
      diagnostico,
      receta,
      fecha,
      nombre_mascota
    FROM public.reporte
    ORDER BY fecha DESC, id_reporte DESC
  ";

  $stmt = $pdo->query($sql);
  $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

  echo json_encode(['estado' => 'success', 'reportes' => $reportes]);

} catch (Throwable $e) {
  error_log('obtener_reporte.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo obtener los reportes']);
}
