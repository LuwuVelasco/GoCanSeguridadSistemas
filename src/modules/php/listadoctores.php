<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Consulta para obtener todos los doctores con sus especialidades
$sql = "SELECT d.nombre, d.cargo, d.estado, e.nombre_especialidad AS especialidad 
        FROM doctores d
        LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
        ORDER BY d.nombre, d.cargo, e.nombre_especialidad, d.estado;";

$result = pg_query($conexion, $sql);

$doctores = array();
while ($row = pg_fetch_assoc($result)) {
    $doctores[] = $row;
}

echo json_encode($doctores);

pg_close($conexion);
?>