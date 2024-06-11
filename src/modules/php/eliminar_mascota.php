<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir el ID de la mascota
$id_mascota = $_POST['id_mascota'] ?? 0;

if ($id_mascota == 0) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no válido"]);
    exit;
}

// Eliminar la mascota
$query = "DELETE FROM mascota WHERE id_mascota = $1";
$result = pg_query_params($conexion, $query, [$id_mascota]);

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Mascota eliminada exitosamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la mascota"]);
}
?>