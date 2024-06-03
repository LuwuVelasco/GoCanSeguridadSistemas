<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Consulta para obtener todos los doctores
$sql = "SELECT id_doctores, nombre, cargo, especialidad, estado FROM doctores";
$result = pg_query($conexion, $sql);

$doctores = array();
while ($row = pg_fetch_assoc($result)) {
    $doctores[] = $row;
}

echo json_encode($doctores);

pg_close($conexion);
?>
