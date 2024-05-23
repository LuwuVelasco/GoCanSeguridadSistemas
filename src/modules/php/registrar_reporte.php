<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=Jesus.2004");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir los datos del formulario
$propietario = $_POST['propietario'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';

// Validar que los campos no estén vacíos
if (empty($propietario) || empty($descripcion) || empty($fecha) || empty($nombre_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Consulta para insertar un nuevo reporte
$sql = "INSERT INTO public.reporte (propietario, descripcion, fecha, nombre_mascota) VALUES ($1, $2, $3, $4) RETURNING id_reporte";
$result = pg_prepare($conexion, "insert_query", $sql);
$result = pg_execute($conexion, "insert_query", array($propietario, $descripcion, $fecha, $nombre_mascota));

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode(["estado" => "success", "id_reporte" => $row['id_reporte']]);
} else {
    $error = pg_last_error($conexion);
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar el reporte: $error"]);
}

pg_close($conexion);
?>
