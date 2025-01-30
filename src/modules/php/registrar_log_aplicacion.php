<?php
header('Content-Type: application/json');
include 'conexion.php'; // 
date_default_timezone_set('America/La_Paz');

try {
    $id_usuario       = $_POST['id_usuario']       ?? null;
    $nombre_usuario   = $_POST['nombre_usuario']   ?? null; // <-- ahora lo recibimos directamente
    $accion           = $_POST['accion']           ?? 'desconocido';
    $descripcion      = $_POST['descripcion']      ?? '';
    $funcion_afectada = $_POST['funcion_afectada'] ?? '';
    $dato_modificado  = $_POST['dato_modificado']  ?? '';
    $valor_original   = $_POST['valor_original']   ?? '';

    // Opción A: Ya tenemos $nombre_usuario por POST, no hace falta buscar en la BD
    // Opción B (mixta): si $nombre_usuario está vacío, buscarlo usando $id_usuario, etc.

    // Preparar la consulta
    $sql = "INSERT INTO log_aplicacion (
                id_usuario,
                nombre_usuario,
                accion,
                descripcion,
                funcion_afectada,
                dato_modificado,
                valor_original,
                fecha_hora
            )
            VALUES (
                :id_usuario,
                :nombre_usuario,
                :accion,
                :descripcion,
                :funcion_afectada,
                :dato_modificado,
                :valor_original,
                NOW()
            )";

    $stmt = $pdo->prepare($sql);

    // Convertir a null si no es numérico
    if (!is_numeric($id_usuario)) {
        $id_usuario = null;
    }

    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':nombre_usuario', $nombre_usuario);
    $stmt->bindParam(':accion', $accion);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':funcion_afectada', $funcion_afectada);
    $stmt->bindParam(':dato_modificado', $dato_modificado);
    $stmt->bindParam(':valor_original', $valor_original);

    $stmt->execute();

    echo json_encode(["estado" => "success", "mensaje" => "Log de aplicación registrado correctamente."]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el log: " . $e->getMessage()]);
}
