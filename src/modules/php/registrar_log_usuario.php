<?php
header('Content-Type: application/json');
include 'conexion.php'; // Archivo de conexión a la base de datos
date_default_timezone_set('America/La_Paz');

try {
    // Obtener valores desde POST
    $id_usuario = $_POST['id_usuario'] ?? null; // Opcional
    $accion = $_POST['accion'] ?? 'desconocido'; // Obligatorio
    $descripcion = $_POST['descripcion'] ?? '';  // Opcional
    $nombre_usuario = null;

    // Si hay un id_usuario, buscar el nombre del usuario en la tabla usuario
    if (!empty($id_usuario) && is_numeric($id_usuario)) {
        $queryUsuario = "SELECT nombre FROM usuario WHERE id_usuario = :id_usuario";
        $stmtUsuario = $pdo->prepare($queryUsuario);
        $stmtUsuario->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtUsuario->execute();
        $resultado = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $nombre_usuario = $resultado['nombre'];
        }
    }

    // Preparar la consulta de inserción en log_usuarios
    $sql = "INSERT INTO log_usuarios (
                id_usuario, 
                nombre_usuario, 
                accion, 
                descripcion, 
                fecha_hora
            ) 
            VALUES (
                :id_usuario, 
                :nombre_usuario, 
                :accion, 
                :descripcion, 
                NOW()
            )";

    $stmt = $pdo->prepare($sql);

    // Asegurar que id_usuario sea un número o null
    if (!is_numeric($id_usuario)) {
        $id_usuario = null;
    }

    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT | PDO::PARAM_NULL);
    $stmt->bindParam(':nombre_usuario', $nombre_usuario);
    $stmt->bindParam(':accion', $accion);
    $stmt->bindParam(':descripcion', $descripcion);

    // Ejecutar la consulta
    $stmt->execute();

    echo json_encode(["estado" => "success", "mensaje" => "Log de usuario registrado correctamente."]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el log: " . $e->getMessage()]);
}
?>
