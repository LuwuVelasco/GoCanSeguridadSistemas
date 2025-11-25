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

$pdo = require __DIR__ . '/conexion.php';

$in = $_POST ?: (json_decode(file_get_contents('php://input'), true) ?: []);

$id      = isset($in['id_doctores']) ? (int)$in['id_doctores'] : (int)($in['id'] ?? 0);
$estadoR = $in['estado'] ?? null;

if ($id <= 0 || $estadoR === null) {
  echo json_encode(['success' => false, 'message' => 'Faltan parámetros: id / estado']);
  exit;
}

// Normaliza estado a 0/1
if (is_numeric($estadoR)) {
  $estado = ((int)$estadoR) ? 1 : 0;
} else {
  $s = strtolower(trim((string)$estadoR));
  $estado = in_array($s, ['1','true','sí','si','on','activo','activa'], true) ? 1 : 0;
}

try {
  // OJO: requiere que la tabla doctores tenga la columna "estado"
  // y la PK sea "id_doctores"
  $sql  = 'UPDATE doctores SET estado = :estado WHERE id_doctores = :id';
  $stmt = $pdo->prepare($sql);
  $stmt->execute([':estado' => $estado, ':id' => $id]);

  echo json_encode([
    'success'   => true,
    'affected'  => $stmt->rowCount()
  ]);
} catch (Throwable $e) {
  error_log('actualizar_estado: ' . $e->getMessage());
  // Si te aparece error de columna inexistente, crea la columna:
  // ALTER TABLE doctores ADD COLUMN estado boolean DEFAULT true;
  echo json_encode(['success' => false, 'message' => 'Error al ejecutar la actualización']);
}