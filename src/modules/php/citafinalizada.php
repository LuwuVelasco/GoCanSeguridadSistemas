<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Obtener el id_cita de la solicitud
$id_cita = $_POST['id_cita'];

// Eliminar la cita
$query = "DELETE FROM cita WHERE id_cita = $1";
$result = pg_query_params($conexion, $query, array($id_cita));

if (!$result) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
    exit;
}

echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);

pg_close($conexion);
?>
s