<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
session_start();

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // ====== Entradas ======
  $id_mascota        = isset($_POST['id_mascota']) ? (int)$_POST['id_mascota'] : 0;
  $nombre_mascota    = trim((string)($_POST['nombre_mascota'] ?? ''));
  $fecha_nacimiento  = trim((string)($_POST['fecha_nacimiento'] ?? '')); // esperado YYYY-MM-DD
  $tipo              = trim((string)($_POST['tipo'] ?? ''));
  $raza              = trim((string)($_POST['raza'] ?? ''));
  $nombre_propietario= trim((string)($_POST['nombre_propietario'] ?? ''));

  if (
    $id_mascota <= 0 ||
    $nombre_mascota === '' ||
    $fecha_nacimiento === '' ||
    $tipo === '' ||
    $raza === '' ||
    $nombre_propietario === ''
  ) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
  }

  // Validar fecha (formato Y-m-d)
  $dt = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
  if (!$dt || $dt->format('Y-m-d') !== $fecha_nacimiento) {
    echo json_encode(["estado" => "error", "mensaje" => "La fecha de nacimiento no tiene un formato válido (YYYY-MM-DD)"]);
    exit;
  }

  // ====== Buscar propietario por nombre (nota: nombre podría no ser único) ======
  $stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1');
  $stmt->execute([':nombre' => $nombre_propietario]);
  $rowUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$rowUsuario) {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
    exit;
  }
  $id_usuario_prop = (int)$rowUsuario['id_usuario'];

  // ====== Consultar datos actuales de la mascota ======
  $stmt = $pdo->prepare('SELECT * FROM mascota WHERE id_mascota = :id LIMIT 1');
  $stmt->execute([':id' => $id_mascota]);
  $mascota_actual = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$mascota_actual) {
    echo json_encode(["estado" => "error", "mensaje" => "La mascota no existe"]);
    exit;
  }

  // ====== Comparar cambios ======
  $cambios = [];

  if ((string)$mascota_actual['nombre_mascota'] !== $nombre_mascota) {
    $cambios['nombre_mascota'] = [
      'antes' => (string)$mascota_actual['nombre_mascota'],
      'después' => $nombre_mascota
    ];
  }
  if ((string)$mascota_actual['fecha_nacimiento'] !== $fecha_nacimiento) {
    $cambios['fecha_nacimiento'] = [
      'antes' => (string)$mascota_actual['fecha_nacimiento'],
      'después' => $fecha_nacimiento
    ];
  }
  if ((string)$mascota_actual['tipo'] !== $tipo) {
    $cambios['tipo'] = [
      'antes' => (string)$mascota_actual['tipo'],
      'después' => $tipo
    ];
  }
  if ((string)$mascota_actual['raza'] !== $raza) {
    $cambios['raza'] = [
      'antes' => (string)$mascota_actual['raza'],
      'después' => $raza
    ];
  }
  if ((int)$mascota_actual['id_usuario'] !== $id_usuario_prop) {
    $cambios['id_usuario'] = [
      'antes' => (string)$mascota_actual['id_usuario'],
      'después' => (string)$id_usuario_prop
    ];
  }

  // Si no hay cambios, respondemos éxito "sin cambios"
  if (empty($cambios)) {
    echo json_encode(["estado" => "success", "mensaje" => "No hubo cambios"]);
    exit;
  }

  // ====== Actualizar mascota (transacción por seguridad) ======
  $pdo->beginTransaction();

  $stmt = $pdo->prepare(
    'UPDATE mascota
        SET nombre_mascota   = :nombre_mascota,
            fecha_nacimiento = :fecha_nacimiento,
            tipo             = :tipo,
            raza             = :raza,
            id_usuario       = :id_usuario
      WHERE id_mascota       = :id_mascota'
  );

  $stmt->execute([
    ':nombre_mascota'   => $nombre_mascota,
    ':fecha_nacimiento' => $fecha_nacimiento,
    ':tipo'             => $tipo,
    ':raza'             => $raza,
    ':id_usuario'       => $id_usuario_prop,
    ':id_mascota'       => $id_mascota,
  ]);

  // ====== Registrar en log_aplicacion ======
  $idUsuarioActual      = $_SESSION['id_usuario']     ?? null;
  $nombreUsuarioActual  = $_SESSION['nombre_usuario'] ?? null;

  // Construir strings de cambios
  $camposModificados = implode(', ', array_keys($cambios));
  $detalles = [];
  foreach ($cambios as $campo => $v) {
    // Normalizar a strings cortos
    $antes = (string)$v['antes'];
    $desp  = (string)$v['después'];
    $detalles[] = "$campo: $antes -> $desp";
  }
  $valorOriginal = implode('; ', $detalles);

  registrarLogAplicacion(
    $pdo,
    $idUsuarioActual,
    $nombreUsuarioActual,
    'editar_mascota',
    "Se editó la mascota con ID $id_mascota",
    'editar_mascota',
    $camposModificados,
    $valorOriginal
  );

  $pdo->commit();

  echo json_encode(["estado" => "success", "mensaje" => "Mascota actualizada"]);
  exit;

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log('editar_mascota.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
  exit;
}

/**
 * Inserta un registro en log_aplicacion con PDO.
 */
function registrarLogAplicacion(
  PDO $pdo,
  $idUsuarioActual,
  $nombreUsuarioActual,
  string $accion,
  string $descripcion,
  string $funcionAfectada,
  string $datoModificado,
  string $valorOriginal
): void {
  try {
    $stmt = $pdo->prepare(
      'INSERT INTO log_aplicacion
         (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
       VALUES
         (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, NOW())'
    );
    $stmt->execute([
      ':id_usuario'       => $idUsuarioActual,
      ':nombre_usuario'   => $nombreUsuarioActual,
      ':accion'           => $accion,
      ':descripcion'      => $descripcion,
      ':funcion_afectada' => $funcionAfectada,
      ':dato_modificado'  => $datoModificado,
      ':valor_original'   => $valorOriginal,
    ]);
  } catch (Throwable $e) {
    // No romper el flujo si el log falla
    error_log('registrarLogAplicacion error: ' . $e->getMessage());
  }
}