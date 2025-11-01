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
session_start();

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  if (!isset($_SESSION['id_doctores']) || !is_numeric($_SESSION['id_doctores'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
    exit;
  }

  $idDoctor = (int) $_SESSION['id_doctores'];

  $stmt = $pdo->prepare(
    'SELECT id_cita, propietario, horario, fecha
       FROM cita
      WHERE id_doctor = :id_doctor
      ORDER BY fecha ASC, horario ASC'
  );
  $stmt->execute([':id_doctor' => $idDoctor]);
  $citas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

  echo json_encode(["estado" => "success", "citas" => $citas]);
  exit;

} catch (Throwable $e) {
  error_log('doctores.php error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(["estado" => "error", "mensaje" => "Error al obtener citas"]);
  exit;
}