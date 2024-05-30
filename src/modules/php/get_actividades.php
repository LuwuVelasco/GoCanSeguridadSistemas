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
$sql = "SELECT * FROM actividades ORDER BY hora_ingreso DESC";
$result = pg_query($conexion, $sql);

$actividades = array();
while ($row = pg_fetch_assoc($result)) {
    $actividades[] = $row;
}

echo json_encode($actividades);

pg_close($conexion);
?>
