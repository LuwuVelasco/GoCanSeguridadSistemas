<?php
include 'conexion.php';  // Asegúrate de tener un archivo de conexión

$id_reporte = $_POST['id_reporte'];  // Recuperar ID del reporte
$propietario = $_POST['propietario'];
$nombre_mascota = $_POST['nombre_mascota'];
$sintomas = $_POST['sintomas'];
$diagnostico = $_POST['diagnostico'];
$receta = $_POST['receta'];
$fecha = $_POST['fecha'];

// Actualizar usando el ID del reporte
$sql = "UPDATE reporte SET propietario=?, nombre_mascota=?, sintomas=?, diagnostico=?, receta=?, fecha=? WHERE id_reporte=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param('ssssssi', $propietario, $nombre_mascota, $sintomas, $diagnostico, $receta, $fecha, $id_reporte);

if ($stmt->execute()) {
    echo json_encode(array("estado" => "success"));
} else {
    echo json_encode(array("estado" => "error", "mensaje" => "No se pudo actualizar el reporte"));
}

$stmt->close();
$conexion->close();
