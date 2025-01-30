<?php
header('Content-Type: application/json');
session_start();

// Incluir la conexión con PDO
$pdo = include 'conexion.php';

try {
    // Preparar y ejecutar la consulta con PDO
    $query = "SELECT id_cita, propietario, horario, fecha FROM cita WHERE id_doctor = :id_doctor";
    $stmt = $pdo->prepare($query);
    $id_doctor = 1; // Ajustar si el ID del doctor es dinámico
    $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener los resultados
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["estado" => "success", "citas" => $citas]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al ejecutar la consulta: " . $e->getMessage()]);
}
?>
