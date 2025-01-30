<?php
header('Content-Type: application/json');
session_start();
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar si la sesión del doctor está establecida
    if (!isset($_SESSION['id_doctores'])) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró la sesión del doctor"]);
        exit;
    }

    $id_doctor = $_SESSION['id_doctores'];

    // Preparar la consulta con PDO
    $query = "SELECT id_cita, propietario, horario, fecha FROM cita WHERE id_doctor = :id_doctor";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener las citas
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["estado" => "success", "citas" => $citas ?: []]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al ejecutar la consulta: " . $e->getMessage()]);
}
?>
