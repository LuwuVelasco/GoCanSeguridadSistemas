<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'conexion.php'; // Asegúrate de que $pdo está definido en conexion.php

try {
    // Recibir los datos del formulario
    $propietario = trim($_POST['propietario'] ?? '');
    $sintomas = trim($_POST['sintomas'] ?? '');
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $receta = trim($_POST['receta'] ?? '');
    $fecha = trim($_POST['fecha'] ?? '');
    $nombre_mascota = trim($_POST['nombre_mascota'] ?? '');

    // Validar que los campos no estén vacíos
    if (empty($propietario) || empty($sintomas) || empty($diagnostico) || empty($receta) || empty($fecha) || empty($nombre_mascota)) {
        echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Verificar que el propietario exista
    $query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = :nombre";
    $stmt = $pdo->prepare($query_usuario);
    $stmt->bindParam(':nombre', $propietario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
        exit;
    }

    $id_usuario = $usuario['id_usuario'];

    // Verificar que la mascota exista y pertenezca al propietario
    $query_mascota = "SELECT id_mascota FROM mascota WHERE nombre_mascota = :nombre_mascota AND id_usuario = :id_usuario";
    $stmt = $pdo->prepare($query_mascota);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mascota) {
        echo json_encode(["estado" => "error", "mensaje" => "La mascota no existe"]);
        exit;
    }

    $id_mascota = $mascota['id_mascota'];

    // Insertar el reporte
    $sql = "INSERT INTO reporte (propietario, sintomas, diagnostico, receta, fecha, nombre_mascota) 
            VALUES (:propietario, :sintomas, :diagnostico, :receta, :fecha, :nombre_mascota) RETURNING id_reporte";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
    $stmt->bindParam(':sintomas', $sintomas, PDO::PARAM_STR);
    $stmt->bindParam(':diagnostico', $diagnostico, PDO::PARAM_STR);
    $stmt->bindParam(':receta', $receta, PDO::PARAM_STR);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);
    $stmt->execute();

    // Obtener el ID del reporte insertado
    $id_reporte = $pdo->lastInsertId();

    echo json_encode(["estado" => "success", "id_reporte" => $id_reporte]);

} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
