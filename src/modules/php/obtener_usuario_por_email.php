<?php
header('Content-Type: application/json');
include 'conexion.php'; // Archivo de conexión a la base de datos

$email = $_POST['email'] ?? null;

if (!$email) {
    echo json_encode(["estado" => "error", "mensaje" => "El email es obligatorio."]);
    exit;
}

try {
    // Consulta para obtener id_usuario y nombre en base al email
    $sql = "SELECT id_usuario, nombre FROM usuario WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        echo json_encode(["estado" => "success", "data" => $usuario]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado."]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la consulta: " . $e->getMessage()]);
}
?>
