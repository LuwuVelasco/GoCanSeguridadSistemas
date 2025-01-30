<?php
header('Content-Type: application/json');
session_start();
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que el ID de la cita fue enviado
    if (!isset($_POST['id_cita'])) {
        echo json_encode(["estado" => "error", "mensaje" => "No se proporcionó el ID de la cita"]);
        exit;
    }

    $id_cita = $_POST['id_cita'];

    // Preparar la consulta para eliminar la cita
    $query = "DELETE FROM cita WHERE id_cita = :id_cita";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);

    // Ejecutar la consulta y verificar el resultado
    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Cita eliminada correctamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la cita"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
