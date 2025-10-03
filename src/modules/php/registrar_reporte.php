<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  try { $pdo->exec("SET TIME ZONE 'America/La_Paz'"); } catch (Throwable $e) {}

  // Soporta x-www-form-urlencoded y JSON
  $input = $_POST;
  if (empty($input)) {
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw, true);
    if (is_array($json)) $input = $json;
  }

  $propietario    = trim($input['propietario']    ?? '');
  $sintomas       = trim($input['sintomas']       ?? '');
  $diagnostico    = trim($input['diagnostico']    ?? '');
  $receta         = trim($input['receta']         ?? '');
  $fecha          = trim($input['fecha']          ?? '');
  $nombreMascota  = trim($input['nombre_mascota'] ?? '');

  // Validaciones
  if ($propietario === '' || $sintomas === '' || $diagnostico === '' || $receta === '' || $fecha === '' || $nombreMascota === '') {
    http_response_code(400);
    echo json_encode(['estado'=>'error','mensaje'=>'Todos los campos son obligatorios']);
    exit;
  }

  $dt = DateTime::createFromFormat('Y-m-d', $fecha);
  $errors = DateTime::getLastErrors();
  if (!$dt || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
    http_response_code(400);
    echo json_encode(['estado'=>'error','mensaje'=>'La fecha debe tener formato YYYY-MM-DD']);
    exit;
  }
  $fecha = $dt->format('Y-m-d');

  // 1) Verificar propietario por NOMBRE
  $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1");
  $stmt->execute([':nombre' => $propietario]);
  $owner = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$owner) {
    http_response_code(404);
    echo json_encode(['estado'=>'error','mensaje'=>'El propietario no existe']);
    exit;
  }
  $idUsuario = (int)$owner['id_usuario'];

  // 2) Verificar mascota por NOMBRE para ese propietario
  $stmt = $pdo->prepare("
    SELECT id_mascota
    FROM mascota
    WHERE nombre_mascota = :nombre_mascota AND id_usuario = :id_usuario
    LIMIT 1
  ");
  $stmt->execute([
    ':nombre_mascota' => $nombreMascota,
    ':id_usuario'     => $idUsuario,
  ]);
  $pet = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$pet) {
    http_response_code(404);
    echo json_encode(['estado'=>'error','mensaje'=>'La mascota no existe para ese propietario']);
    exit;
  }

  // 3) Insertar el reporte (sin id_cita)
  $stmt = $pdo->prepare("
    INSERT INTO reporte (propietario, sintomas, diagnostico, receta, fecha, nombre_mascota)
    VALUES (:propietario, :sintomas, :diagnostico, :receta, :fecha, :nombre_mascota)
    RETURNING id_reporte
  ");
  $stmt->execute([
    ':propietario'    => $propietario,
    ':sintomas'       => $sintomas,
    ':diagnostico'    => $diagnostico,
    ':receta'         => $receta,
    ':fecha'          => $fecha,
    ':nombre_mascota' => $nombreMascota,
  ]);

  $newId = $stmt->fetchColumn();
  if ($newId === false) {
    throw new RuntimeException('No se pudo obtener el ID del reporte creado.');
  }

  echo json_encode([
    'estado'     => 'success',
    'id_reporte' => (int)$newId,
    'mensaje'    => 'Reporte registrado correctamente'
  ]);

} catch (Throwable $e) {
  error_log('registrar_reporte.php: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error al registrar el reporte']);
}
