<?php
header('Content-Type: application/json');
include 'conexion.php';

$id_mascota = $_POST['id_mascota'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

if (empty($id_mascota) || empty($nombre_mascota) || empty($fecha_nacimiento) || empty($tipo) || empty($raza) || empty($nombre_propietario)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

$query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result_usuario = pg_query_params($conexion, $query_usuario, [$nombre_propietario]);

if ($result_usuario && pg_num_rows($result_usuario) > 0) {
    $row_usuario = pg_fetch_assoc($result_usuario);
    $id_usuario = $row_usuario['id_usuario'];

    $query = "
        UPDATE mascota
        SET 
            nombre_mascota = $1,
            fecha_nacimiento = $2,
            tipo = $3,
            raza = $4,
            id_usuario = $5
        WHERE id_mascota = $6";

    $params = [$nombre_mascota, $fecha_nacimiento, $tipo, $raza, $id_usuario, $id_mascota];
    $result = pg_query_params($conexion, $query, $params);

    if ($result) {
        echo json_encode(["estado" => "success"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}
?>
