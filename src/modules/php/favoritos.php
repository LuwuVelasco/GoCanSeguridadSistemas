<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usuario'])) {
        // Obtener la cantidad de productos asociados al id_usuario
        $id_usuario = $_GET['id_usuario'];
        $stmt = $conexion->prepare("SELECT COUNT(*) AS cantidad FROM producto WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver la cantidad de productos en formato JSON
        echo json_encode($resultado);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener productos favoritos o eliminar un producto
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['id_usuario'])) {
            // Obtener productos favoritos del usuario
            $id_usuario = $data['id_usuario'];

            $stmt = $conexion->prepare("SELECT id_producto, nombre, descripcion, precio, imagen FROM producto WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($favoritos);
        } elseif (isset($data['id_producto'])) {
            // Eliminar un producto
            $id = $data['id_producto'];

            $stmt = $conexion->prepare("DELETE FROM producto WHERE id_producto = :id_producto");
            $stmt->bindParam(':id_producto', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Datos inválidos']);
        }
    } else {
        echo json_encode(['error' => 'Método no soportado']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>