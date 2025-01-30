<?php
header('Content-Type: application/json');
session_start();
include 'conexion.php';

if (!isset($_POST['id_cita'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se proporcionó el ID de la cita"]);
    exit;
}

$id_cita = $_POST['id_cita'];

$query = "DELETE FROM cita WHERE id_cita = $1";
$result = pg_query_params($conexion, $query, array($id_cita));

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
}

pg_close($conexion);
?>
