<?php
header('Content-Type: application/json');
include 'conexion.php';

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
        echo json_encode(["estado" => "success", "mensaje" => "Doctor eliminado con éxito."]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró el doctor."]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar el doctor: " . $e->getMessage()]);
}
?>
