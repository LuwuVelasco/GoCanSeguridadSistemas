<?php
header('Content-Type: application/json');
include 'conexion.php';

$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

if (empty($nombre_mascota) || empty($fecha_nacimiento) || empty($tipo) || empty($raza) || empty($nombre_propietario)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

$query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result_usuario = pg_query_params($conexion, $query_usuario, [$nombre_propietario]);

if ($result_usuario && pg_num_rows($result_usuario) > 0) {
    $row_usuario = pg_fetch_assoc($result_usuario);
    $id_usuario = $row_usuario['id_usuario'];

    $query = "
        INSERT INTO mascota (nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario) 
        VALUES ($1, $2, $3, $4, $5)";
    $params = [$nombre_mascota, $fecha_nacimiento, $tipo, $raza, $id_usuario];
    $result = pg_query_params($conexion, $query, $params);

    if ($result) {
        echo json_encode(["estado" => "success", "mensaje" => "Mascota registrada exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al registrar la mascota"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}
?>
