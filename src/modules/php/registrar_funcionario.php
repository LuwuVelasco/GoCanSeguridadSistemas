<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    $nombre = $_POST['nombre'];
    $email = $_POST['correo'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];
    $fechaRegistro = date('Y-m-d H:i:s'); // Fecha y hora actual

    $stmt = $pdo->prepare("INSERT INTO usuario (email, nombre, password, rol_id, fecha_registro) 
                            VALUES (:email, :nombre, :password, :rol, :fecha_registro)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':fecha_registro', $fechaRegistro);
    $stmt->execute();

    echo json_encode(["estado" => "success", "mensaje" => "Funcionario registrado exitosamente."]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
?>
    