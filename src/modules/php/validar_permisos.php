<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Acepta JSON o formulario
  $raw  = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = $_POST; }

  if (!isset($data['id_rol'])) {
    echo json_encode(['success' => false, 'message' => 'ID de rol no especificado']);
    exit;
  }

  $idRol = (int)$data['id_rol'];

  $stmt = $pdo->prepare('SELECT * FROM roles_y_permisos WHERE id_rol = :id LIMIT 1');
  $stmt->execute([':id' => $idRol]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No se encontraron permisos para este rol']);
    exit;
  }

  // Filtrar solo permisos (excluir ID y nombre) y normalizar a booleano
  $permisos = [];
  foreach ($row as $clave => $valor) {
    if ($clave === 'id_rol' || $clave === 'nombre_rol') { continue; }
    $permisos[$clave] = is_bool($valor)
      ? $valor
      : in_array(strtolower((string)$valor), ['t', 'true', '1', 'on', 'yes'], true);
  }

  echo json_encode(['success' => true, 'permisos' => $permisos], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  error_log('validar_permisos error: ' . $e->getMessage());
  echo json_encode(['success' => false, 'message' => 'Error al obtener los permisos']);
}
