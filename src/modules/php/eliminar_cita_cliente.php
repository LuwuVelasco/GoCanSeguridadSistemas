<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener datos de la solicitud
    $data = json_decode(file_get_contents("php://input"), true);

    // Validar datos de entrada
    if (!isset($data['id_cita'], $data['id_usuario'])) {
        echo json_encode(["estado" => "error", "mensaje" => "No se proporcionaron los datos necesarios (id_cita o id_usuario)"]);
        exit;
    }

    $id_cita = $data['id_cita'];
    $id_usuario = $data['id_usuario'];

    // Verificar que la cita pertenezca al cliente
    $queryVerificar = "SELECT id_cita FROM cita WHERE id_cita = :id_cita AND id_usuario = :id_usuario";
    $stmt = $pdo->prepare($queryVerificar);
    $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(["estado" => "error", "mensaje" => "No tienes permiso para eliminar esta cita o no existe"]);
        exit;
    }

    // Eliminar la cita
    $queryEliminar = "DELETE FROM cita WHERE id_cita = :id_cita AND id_usuario = :id_usuario";
    $stmt = $pdo->prepare($queryEliminar);
    $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
