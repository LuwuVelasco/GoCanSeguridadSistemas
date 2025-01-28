<?php
header('Content-Type: application/json');
include 'conexion.php';

$id_mascota = $_GET['id_mascota'] ?? '';
if (empty($id_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no proporcionado"]);
    exit;
}

$query = "
    SELECT 
        m.id_mascota,
        m.nombre_mascota,
        m.fecha_nacimiento,
        m.tipo,
        m.raza,
        u.nombre AS nombre_propietario
    FROM 
        mascota m
    JOIN 
        usuario u ON m.id_usuario = u.id_usuario
    WHERE 
        m.id_mascota = $1";

$result = pg_query_params($conexion, $query, [$id_mascota]);

if ($result) {
    if ($row = pg_fetch_assoc($result)) {
        echo json_encode(["estado" => "success", "mascota" => $row]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Mascota no encontrada"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener la mascota"]);
}
?>
