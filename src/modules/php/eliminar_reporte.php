<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/* --- CORS opcional para desarrollo con Live Server (puerto 5500) --- */
$allowed_origins = ['http://localhost:5500','http://127.0.0.1:5500','http://localhost','http://127.0.0.1'];
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

  // Aceptar JSON o x-www-form-urlencoded
  $raw = file_get_contents('php://input');
  $json = json_decode($raw, true);

  $id_reporte     = (int)($json['id_reporte'] ?? ($_POST['id_reporte'] ?? 0));
  $propietario    = trim((string)($json['propietario'] ?? ($_POST['propietario'] ?? '')));
  $nombre_mascota = trim((string)($json['nombre_mascota'] ?? ($_POST['nombre_mascota'] ?? '')));

  // Prioridad: eliminar por id_reporte si viene
  if ($id_reporte > 0) {
    $stmt = $pdo->prepare('DELETE FROM reporte WHERE id_reporte = :id');
    $stmt->execute([':id' => $id_reporte]);
    if ($stmt->rowCount() > 0) {
      echo json_encode(['estado'=>'success','mensaje'=>'Reporte eliminado exitosamente']);
    } else {
      echo json_encode(['estado'=>'error','mensaje'=>'No se encontró el reporte con ese ID']);
    }
    exit;
  }

  // Si no hay id, requerimos propietario + nombre_mascota
  if ($propietario === '' || $nombre_mascota === '') {
    echo json_encode([
      'estado'=>'error',
      'mensaje'=>'Datos de reporte inválidos: se requiere id_reporte o propietario y nombre_mascota'
    ]);
    exit;
  }

  $stmt = $pdo->prepare(
    'DELETE FROM reporte WHERE propietario = :propietario AND nombre_mascota = :nombre_mascota'
  );
  $stmt->execute([
    ':propietario'    => $propietario,
    ':nombre_mascota' => $nombre_mascota
  ]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(['estado'=>'success','mensaje'=>'Reporte eliminado exitosamente']);
  } else {
    // Puede que no exista o que haya múltiples con esos datos; lo ideal es usar id_reporte
    echo json_encode(['estado'=>'error','mensaje'=>'No se encontró reporte con esos datos']);
  }

} catch (PDOException $e) {
  error_log('eliminar_reporte.php PDO: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error al eliminar el reporte']);
} catch (Throwable $t) {
  error_log('eliminar_reporte.php: '.$t->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error del servidor']);
}
