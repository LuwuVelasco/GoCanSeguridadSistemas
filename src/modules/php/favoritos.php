<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usuario'])) {
        // Obtener la cantidad de productos asociados al id_usuario
        $id_usuario = $_GET['id_usuario'];

        if (!is_numeric($id_usuario)) {
            echo json_encode(['error' => 'ID de usuario no válido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS cantidad FROM producto WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver la cantidad de productos en formato JSON
        echo json_encode(["estado" => "success", "cantidad" => $resultado['cantidad']]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener productos favoritos o eliminar un producto
        $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

        if (isset($data['id_usuario'])) {
            // Obtener productos favoritos del usuario
            $id_usuario = $data['id_usuario'];

            if (!is_numeric($id_usuario)) {
                echo json_encode(['error' => 'ID de usuario no válido']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT id_producto, nombre, descripcion, precio, imagen FROM producto WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["estado" => "success", "productos" => $favoritos]);
        } elseif (isset($data['id_producto'])) {
            // Eliminar un producto
            $id_producto = $data['id_producto'];

            if (!is_numeric($id_producto)) {
                echo json_encode(['error' => 'ID de producto no válido']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM producto WHERE id_producto = :id_producto");
            $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(["estado" => "success", "mensaje" => "Producto eliminado con éxito"]);
            } else {
                echo json_encode(["estado" => "error", "mensaje" => "No se encontró el producto"]);
            }
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Datos inválidos"]);
        }
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Método no soportado"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
