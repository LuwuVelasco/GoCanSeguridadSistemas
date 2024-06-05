<?php
header('Content-Type: application/json');

$estrellas = $_POST['estrellas'] ?? null;

if ($estrellas === null || !is_numeric($estrellas) || $estrellas < 1 || $estrellas > 5) {
    echo json_encode(["estado" => "error", "mensaje" => "Calificación inválida"]);
    exit;
}

$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$query = "INSERT INTO calificacion (estrellas) VALUES ($1)";
$result = pg_query_params($conexion, $query, array($estrellas));

if (!$result) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al ejecutar la consulta"]);
    exit;
}

echo json_encode(["estado" => "success", "mensaje" => "Calificación guardada correctamente"]);

pg_close($conexion);
?>
