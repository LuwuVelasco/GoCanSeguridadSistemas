<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Consulta para obtener todos los reportes
$sql = "SELECT propietario, sintomas, diagnostico, receta, fecha, nombre_mascota FROM public.reporte";
$result = pg_query($conexion, $sql);

if ($result) {
    $reportes = pg_fetch_all($result);
    echo json_encode(["estado" => "success", "reportes" => $reportes]);
} else {
    $error = pg_last_error($conexion);
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo obtener los reportes: $error"]);
}

pg_close($conexion);
?>
