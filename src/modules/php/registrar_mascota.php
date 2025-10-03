<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  try { $pdo->exec("SET TIME ZONE 'America/La_Paz'"); } catch (Throwable $e) {}

  // Acepta JSON o x-www-form-urlencoded
  $raw  = file_get_contents('php://input') ?: '';
  $json = json_decode($raw, true);

  $nombre_mascota     = trim($json['nombre_mascota']     ?? ($_POST['nombre_mascota']     ?? ''));
  $fecha_nacimiento   = trim($json['fecha_nacimiento']   ?? ($_POST['fecha_nacimiento']   ?? ''));
  $tipo               = trim($json['tipo']               ?? ($_POST['tipo']               ?? ''));
  $raza               = trim($json['raza']               ?? ($_POST['raza']               ?? ''));
  $nombre_propietario = trim($json['nombre_propietario'] ?? ($_POST['nombre_propietario'] ?? ''));

  // Validaciones
  if ($nombre_mascota === '' || $fecha_nacimiento === '' || $tipo === '' || $raza === '' || $nombre_propietario === '') {
    http_response_code(400);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Todos los campos son obligatorios']);
    exit;
  }

  $dt = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
  $errors = DateTime::getLastErrors();
  if (!$dt || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
    http_response_code(400);
    echo json_encode(['estado' => 'error', 'mensaje' => 'La fecha de nacimiento debe tener formato YYYY-MM-DD']);
    exit;
  }
  $fecha_nacimiento = $dt->format('Y-m-d');

  // Buscar propietario por NOMBRE
  $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1");
  $stmt->execute([':nombre' => $nombre_propietario]);
  $owner = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$owner) {
    http_response_code(404);
    echo json_encode(['estado' => 'error', 'mensaje' => 'El propietario no existe']);
    exit;
  }
  $id_usuario = (int)$owner['id_usuario'];

  // Insertar mascota
  $stmt = $pdo->prepare("
    INSERT INTO mascota (nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
    VALUES (:nombre_mascota, :fecha_nacimiento, :tipo, :raza, :id_usuario)
    RETURNING id_mascota
  ");
  $stmt->execute([
    ':nombre_mascota'   => $nombre_mascota,
    ':fecha_nacimiento' => $fecha_nacimiento,
    ':tipo'             => $tipo,
    ':raza'             => $raza,
    ':id_usuario'       => $id_usuario,
  ]);

  $newId = $stmt->fetchColumn();
  if ($newId === false) {
    throw new RuntimeException('No se pudo obtener el ID de la mascota creada.');
  }

  echo json_encode([
    'estado'     => 'success',
    'mensaje'    => 'Mascota registrada exitosamente',
    'id_mascota' => (int)$newId,
  ]);

} catch (Throwable $e) {
  error_log('registrar_mascota.php: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error al registrar la mascota']);
}
