<?php
header('Content-Type: application/json');
session_start();
include 'conexion.php';

$query = "SELECT id_cita, propietario, horario, fecha FROM cita WHERE id_doctor = 1";
$result = pg_query($conexion, $query);

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
