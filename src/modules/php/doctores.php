<?php
header('Content-Type: application/json');
session_start();
include 'conexion.php';

if (!isset($_SESSION['id_doctores'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
    exit;
}

$id_doctor = $_SESSION['id_doctores'];

$query = "SELECT id_cita, propietario, horario, fecha FROM cita WHERE id_doctor = $1"; // Incluir ID de la cita
$result = pg_query_params($conexion, $query, array($id_doctor));

if (!$result) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al ejecutar la consulta"]);
    exit;
}

$citas = pg_fetch_all($result);
if (!$citas) {
    $citas = [];
}

echo json_encode(["estado" => "success", "citas" => $citas]);

pg_close($conexion);
?>
