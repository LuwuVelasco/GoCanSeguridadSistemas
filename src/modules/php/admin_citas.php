<?php
header('Content-Type: application/json');
session_start();

$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$query = "SELECT propietario, horario, fecha FROM cita WHERE id_doctor = 1";
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
