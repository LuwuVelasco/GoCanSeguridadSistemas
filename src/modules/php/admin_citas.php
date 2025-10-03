<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=UTF-8');

$pdo = require __DIR__ . '/conexion.php';

// Puedes filtrar por doctor. Toma id_doctor de GET/POST o, si no viene, usa el de la sesión.
// Si no hay ninguno, devuelve todas (útil para Administrador).
$idDoctor = null;
if (isset($_GET['id_doctor'])) {
  $idDoctor = (int) $_GET['id_doctor'];
} elseif (isset($_POST['id_doctor'])) {
  $idDoctor = (int) $_POST['id_doctor'];
} elseif (!empty($_SESSION['id_doctores'])) {
  $idDoctor = (int) $_SESSION['id_doctores'];
}

try {
  if ($idDoctor) {
    $stmt = $pdo->prepare(
      "SELECT id_cita,
              propietario,
              horario::text AS horario,   -- para JSON limpio
              fecha::date  AS fecha
       FROM cita
       WHERE id_doctor = :id_doctor
       ORDER BY fecha, horario"
    );
    $stmt->execute([':id_doctor' => $idDoctor]);
  } else {
    // Sin filtro: todas las citas (vista admin)
    $stmt = $pdo->query(
      "SELECT id_cita,
              propietario,
              horario::text AS horario,
              fecha::date  AS fecha,
              id_doctor
       FROM cita
       ORDER BY fecha, horario"
    );
  }

  $citas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  echo json_encode(['estado' => 'success', 'citas' => $citas]);

} catch (Throwable $e) {
  error_log('admin_citas.php: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error al ejecutar la consulta']);
}
