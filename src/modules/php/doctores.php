<?php
header('Content-Type: application/json');

// Iniciar la sesión
session_start();

// Verificar si el ID del doctor está guardado en la sesión
if (!isset($_SESSION['id_doctores'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
    exit;
}

$id_doctor = $_SESSION['id_doctores'];

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Consulta para obtener las citas del doctor
$query = "SELECT propietario, horario, fecha FROM cita WHERE doctor = (SELECT nombre FROM doctores WHERE id_doctores = $1)";
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
