<?php
header('Content-Type: application/json');
include 'conexion.php'; // Incluye la conexión a la base de datos

// Obtener datos de la solicitud
$data = json_decode(file_get_contents("php://input"), true);

// Validar datos de entrada
if (!isset($data['id_cita']) || !isset($data['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se proporcionaron los datos necesarios (id_cita o id_usuario)"]);
    exit;
}

$id_cita = $data['id_cita'];
$id_usuario = $data['id_usuario'];

// Verificar que la cita pertenezca al cliente
$queryVerificar = "SELECT id_cita FROM cita WHERE id_cita = $1 AND id_usuario = $2";
$resultVerificar = pg_query_params($conexion, $queryVerificar, array($id_cita, $id_usuario));

if (!$resultVerificar || pg_num_rows($resultVerificar) === 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No tienes permiso para eliminar esta cita o no existe"]);
    pg_close($conexion);
    exit;
}

// Eliminar la cita
$queryEliminar = "DELETE FROM cita WHERE id_cita = $1 AND id_usuario = $2";
$resultEliminar = pg_query_params($conexion, $queryEliminar, array($id_cita, $id_usuario));

if ($resultEliminar) {
    echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
}

pg_close($conexion);
?>
