<?php
header('Content-Type: application/json');
session_start();

/* Comprobar permisos del usuario
if (!isset($_SESSION['usuario_id']) || !$_SESSION['es_admin']) {
    echo json_encode(["estado" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}*/

include 'conexion.php'; // Asegúrate de que este archivo contiene los detalles de conexión

$id = $_POST['id'];

if (!is_numeric($id)) {
    echo json_encode(["estado" => "error", "mensaje" => "ID no válido"]);
    exit;
}

try {
    $query = $pdo->prepare("DELETE FROM doctores WHERE id_doctores = :id");
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() > 0) {
        echo json_encode(["estado" => "success"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró el doctor"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar el doctor: " . $e->getMessage()]);
}
?>
