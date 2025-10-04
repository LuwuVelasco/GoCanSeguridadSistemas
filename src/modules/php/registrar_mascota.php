<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

try {
  /** Conexión PDO */
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Leer POST
  $nombre_mascota    = trim($_POST['nombre_mascota']    ?? '');
  $fecha_nacimiento  = trim($_POST['fecha_nacimiento']  ?? '');
  $tipo              = trim($_POST['tipo']              ?? '');
  $raza              = trim($_POST['raza']              ?? '');
  $nombre_propietario= trim($_POST['nombre_propietario']?? '');

  // Validaciones básicas
  if ($nombre_mascota === '' || $fecha_nacimiento === '' || $tipo === '' || $raza === '' || $nombre_propietario === '') {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // (Opcional) validar fecha simple: YYYY-MM-DD
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
    echo json_encode(["estado" => "error", "mensaje" => "Formato de fecha inválido (YYYY-MM-DD)"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Buscar propietario por NOMBRE (no por email)
  $stmtUser = $pdo->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1");
  $stmtUser->execute([':nombre' => $nombre_propietario]);
  $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

  if (!$rowUser) {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"], JSON_UNESCAPED_UNICODE);
    exit;
  }
  $id_usuario = (int)$rowUser['id_usuario'];

  // Insertar mascota y devolver id_mascota
  $stmtIns = $pdo->prepare(
    "INSERT INTO mascota (nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
     VALUES (:nombre_mascota, :fecha_nacimiento, :tipo, :raza, :id_usuario)
     RETURNING id_mascota"
  );
  $stmtIns->execute([
    ':nombre_mascota'   => $nombre_mascota,
    ':fecha_nacimiento' => $fecha_nacimiento,
    ':tipo'             => $tipo,
    ':raza'             => $raza,
    ':id_usuario'       => $id_usuario,
  ]);

  $newId = $stmtIns->fetchColumn();
  if ($newId === false) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar la mascota"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  echo json_encode([
    "estado" => "success",
    "mensaje" => "Mascota registrada exitosamente",
    "id_mascota" => (int)$newId
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  error_log('registrar_mascota error: ' . $e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error del servidor"], JSON_UNESCAPED_UNICODE);
}
