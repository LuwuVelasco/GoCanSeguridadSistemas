<?php
header('Content-Type: application/json');
include 'conexion.php';

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
        usuario u ON m.id_usuario = u.id_usuario";

$result = pg_query($conexion, $query);

if ($result) {
    $mascotas = [];
    while ($row = pg_fetch_assoc($result)) {
        $mascotas[] = $row;
    }
    echo json_encode(["estado" => "success", "mascotas" => $mascotas]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener las mascotas"]);
}
?>
