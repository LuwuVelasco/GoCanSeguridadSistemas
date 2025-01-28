<?php
header('Content-Type: application/json');
include 'conexion.php'; // Incluye el archivo de conexión

// Validar si se proporcionó el id_usuario
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró el ID del usuario"]);
    exit;
}

$id_usuario = $data['id_usuario'];

// Obtener el nombre del usuario de la base de datos
$query = "SELECT nombre FROM usuario WHERE id_usuario = $1";
$result = pg_query_params($conexion, $query, array($id_usuario));

if ($row = pg_fetch_assoc($result)) {
    echo json_encode(["estado" => "success", "nombre" => $row['nombre']]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
}

pg_close($conexion);
?>
