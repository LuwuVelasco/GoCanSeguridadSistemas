<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Aseguramos zona horaria en la DB para NOW()/CURRENT_*
  try { $pdo->exec("SET TIME ZONE 'America/La_Paz'"); } catch (Throwable $e) {}

  $raw  = file_get_contents("php://input") ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = []; }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Validar fecha/horario futuros (no inserta en DB, solo valida y responde)
    if (isset($data['fecha'], $data['horario'])) {
      $fecha   = trim((string)$data['fecha']);
      $horario = trim((string)$data['horario']);

      if ($fecha === '' || $horario === '') {
        echo json_encode(['error' => 'Fecha u horario no válidos']);
        exit;
      }

      // Validación de fecha/hora futura
      $tz    = new DateTimeZone('America/La_Paz');
      $now   = new DateTime('now', $tz);
      $input = DateTime::createFromFormat('Y-m-d H:i', "$fecha $horario", $tz);
      if (!$input) {
        // Intento alterno con segundos
        $input = DateTime::createFromFormat('Y-m-d H:i:s', "$fecha $horario", $tz);
      }

      if (!$input) {
        echo json_encode(['error' => 'Formato de fecha u hora inválido']);
        exit;
      }

      if ($input <= $now) {
        echo json_encode(['error' => 'No puedes registrar una cita en una fecha u hora pasada.']);
        exit;
      }

      echo json_encode(['mensaje' => 'Cita registrada correctamente']);
      exit;
    }

    // 2) Listar citas futuras por usuario
    if (isset($data['id_usuario'])) {
      $id_usuario = (int)$data['id_usuario'];

      $sql = "
        SELECT id_cita, propietario, servicio, fecha, horario
        FROM cita
        WHERE id_usuario = :id_usuario
          AND (
            fecha > CURRENT_DATE
            OR (fecha = CURRENT_DATE AND horario > CURRENT_TIME)
          )
        ORDER BY fecha ASC, horario ASC
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':id_usuario' => $id_usuario]);
      $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Mantiene el contrato anterior: devuelve arreglo plano
      echo json_encode($citas ?: []);
      exit;
    }

    echo json_encode(['error' => 'Datos insuficientes proporcionados']);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Eliminar una cita por id
    if (!isset($data['id_cita']) || !is_numeric($data['id_cita'])) {
      echo json_encode(['error' => 'ID de cita no válido']);
      exit;
    }

    $id_cita = (int)$data['id_cita'];
    $stmt = $pdo->prepare("DELETE FROM cita WHERE id_cita = :id_cita");
    $ok = $stmt->execute([':id_cita' => $id_cita]);

    if ($ok && $stmt->rowCount() > 0) {
      echo json_encode(['mensaje' => 'Cita eliminada correctamente']);
    } else {
      echo json_encode(['error' => 'Error al eliminar la cita']);
    }
    exit;
  }

  echo json_encode(['error' => 'Método no soportado']);
} catch (Throwable $e) {
  error_log('reservas.php error: ' . $e->getMessage());
  echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
