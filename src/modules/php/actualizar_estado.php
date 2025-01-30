<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que 'estado' e 'id' están presentes en la solicitud
    if (!isset($_POST['estado'], $_POST['id'])) {
        throw new Exception("Faltan parámetros requeridos.");
    }

    $estado = $_POST['estado'];
    $id = $_POST['id'];

    // Preparar la consulta con PDO
    $sql = "UPDATE doctores SET estado = :estado WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al ejecutar la actualización.");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>