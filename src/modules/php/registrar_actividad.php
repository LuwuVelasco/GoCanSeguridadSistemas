<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Zona horaria (opcional)
  try { $pdo->exec("SET TIME ZONE 'America/La_Paz'"); } catch (Throwable $e) {}

  // Acepta JSON o x-www-form-urlencoded
  $raw  = file_get_contents('php://input') ?: '';
  $json = json_decode($raw, true);
  $id_usuario     = $json['id_usuario']    ?? ($_POST['id_usuario']    ?? null);
  $nombre_usuario = $json['nombre_usuario']?? ($_POST['nombre_usuario']?? null);

  // Validación
  if ($id_usuario === null || !is_numeric($id_usuario)) {
    http_response_code(400);
    echo json_encode(['estado' => 'error', 'mensaje' => 'ID de usuario inválido']);
    exit;
  }
  $id_usuario = (int)$id_usuario;

  $nombre_usuario = is_string($nombre_usuario) ? trim($nombre_usuario) : '';
  if ($nombre_usuario === '') {
    http_response_code(400);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Nombre de usuario requerido']);
    exit;
  }

  // Inserción (hora en DB con NOW())
  $stmt = $pdo->prepare(
    "INSERT INTO actividades (id_usuario, nombre_usuario, hora_ingreso)
     VALUES (:id_usuario, :nombre_usuario, NOW())"
  );
  $stmt->execute([
    ':id_usuario'     => $id_usuario,
    ':nombre_usuario' => $nombre_usuario,
  ]);

  echo json_encode(['estado' => 'success']);

} catch (Throwable $e) {
  error_log('registrar_actividad.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'No se pudo registrar la actividad']);
}
