<?php
header('Content-Type: application/json');
session_start();
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar sesión activa del doctor
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

    // Eliminar la cita usando una consulta preparada con PDO
    $query = "DELETE FROM cita WHERE id_cita = :id_cita";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
