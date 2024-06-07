<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos: " . pg_last_error()]);
    exit;
}

// Revisa la consulta para asegurar que las tablas y campos existan en tu base de datos
$query = "
    SELECT 
        m.id_mascota,
        m.nombre_mascota, 
        m.edad_year, 
        m.edad_month, 
        m.edad_day, 
        m.tipo, 
        m.raza, 
        u.nombre as nombre_propietario
    FROM 
        mascota m
    JOIN 
        usuario u ON m.id_usuario = u.id_usuario"; // Elimina cualquier referencia a tablas inexistentes

$result = pg_query($conexion, $query);

if ($result) {
    $mascotas = [];
    while ($row = pg_fetch_assoc($result)) {
        $mascotas[] = $row;
    }
    echo json_encode(["estado" => "success", "mascotas" => $mascotas]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener las mascotas: " . pg_last_error()]);
}
?>
