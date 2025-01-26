<?php
header('Content-Type: application/json');
include 'conexion.php';

$id_mascota = $_POST['id_mascota'] ?? 0;

if ($id_mascota == 0) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no válido"]);
    exit;
}

$query = "DELETE FROM mascota WHERE id_mascota = $1";
$result = pg_query_params($conexion, $query, [$id_mascota]);

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Mascota eliminada exitosamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la mascota"]);
}
?>
