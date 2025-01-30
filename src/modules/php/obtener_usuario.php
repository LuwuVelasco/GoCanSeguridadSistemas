<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

// Validar si se proporcionó el id_usuario
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró el ID del usuario"]);
    exit;
}

$id_usuario = $data['id_usuario'];

try {
    // Obtener el nombre del usuario de la base de datos
    $query = "SELECT nombre FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(["estado" => "success", "nombre" => $usuario['nombre']]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
