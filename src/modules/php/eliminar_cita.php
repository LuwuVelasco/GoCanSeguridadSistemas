<?php
header('Content-Type: application/json');
include 'conexion.php';
// Verificar sesión activa
session_start();
if (!isset($_SESSION['id_doctores'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
    exit;
}

// Obtener datos de la solicitud
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id_cita'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se proporcionó el ID de la cita"]);
    exit;
}

$id_cita = $data['id_cita'];

// Eliminar la cita
$query = "DELETE FROM cita WHERE id_cita = $1";
$result = pg_query_params($conexion, $query, array($id_cita));

if ($result) {
    echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
}

pg_close($conexion);
?>
