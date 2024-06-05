<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Obtener los datos del reporte a eliminar
$propietario = isset($_POST['propietario']) ? $_POST['propietario'] : '';
$nombre_mascota = isset($_POST['nombre_mascota']) ? $_POST['nombre_mascota'] : '';

if (empty($propietario) || empty($nombre_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "Datos de reporte inválidos: " . json_encode($_POST)]);
    exit;
}

// Consulta para eliminar el reporte
$sql = "DELETE FROM public.reporte WHERE propietario = $1 AND nombre_mascota = $2";
$result = pg_query_params($conexion, $sql, array($propietario, $nombre_mascota));

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Reporte eliminado exitosamente"]);
} else {
    $error = pg_last_error($conexion);
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo eliminar el reporte: $error"]);
}

pg_close($conexion);
?>
