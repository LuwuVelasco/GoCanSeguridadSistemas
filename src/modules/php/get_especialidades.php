<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["error" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Consulta para obtener todas las actividades
$sql = "SELECT nombre_especialidad FROM especialidad";
$result = pg_query($conexion, $sql);

if (!$result) {
    echo json_encode(["error" => "Error en la consulta SQL"]);
    pg_close($conexion);
    exit;
}

$especialidad = array();
while ($row = pg_fetch_assoc($result)) {
    $especialidad[] = $row;
}

echo json_encode($especialidad);

pg_close($conexion);
?>
