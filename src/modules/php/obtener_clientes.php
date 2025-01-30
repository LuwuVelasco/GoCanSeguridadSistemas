<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Obtener todos los usuarios con rol de Cliente (rol_id = 3 en tu BD)
    $query = "SELECT id_usuario, nombre, email FROM usuario WHERE rol_id = 3";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($clientes);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
