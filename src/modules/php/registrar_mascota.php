<?php
header('Content-Type: application/json');
include 'conexion.php'; // Asegúrate de que $pdo está definido en conexion.php

try {
    // Obtener los valores de la solicitud
    $nombre_mascota = $_POST['nombre_mascota'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $raza = $_POST['raza'] ?? '';
    $nombre_propietario = $_POST['nombre_propietario'] ?? '';

    // Validar que todos los campos estén presentes
    if (empty($nombre_mascota) || empty($fecha_nacimiento) || empty($tipo) || empty($raza) || empty($nombre_propietario)) {
        echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Buscar el ID del propietario en la base de datos
    $query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = :nombre";
    $stmt = $pdo->prepare($query_usuario);
    $stmt->bindParam(':nombre', $nombre_propietario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
        exit;
    }

    $id_usuario = $usuario['id_usuario'];

    // Insertar la nueva mascota
    $query = "INSERT INTO mascota (nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario) 
              VALUES (:nombre_mascota, :fecha_nacimiento, :tipo, :raza, :id_usuario)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':raza', $raza, PDO::PARAM_STR);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Mascota registrada exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al registrar la mascota"]);
    }

} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
