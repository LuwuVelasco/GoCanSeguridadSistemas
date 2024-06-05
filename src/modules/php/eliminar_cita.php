<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['id_doctores'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
    exit;
}

$id_doctor = $_SESSION['id_doctores'];

if (!isset($_POST['id_cita'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se proporcionó el ID de la cita"]);
    exit;
}

$id_cita = $_POST['id_cita'];

$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$query = "DELETE FROM cita WHERE id_cita = $1";
$result = pg_query_params($conexion, $query, array($id_cita));

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
}

pg_close($conexion);
?>
