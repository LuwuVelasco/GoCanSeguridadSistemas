<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
include 'conexion.php'; // Asegúrate de que este archivo devuelve `$pdo`

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id_usuario'], $data['password'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }

    $id_usuario = $data['id_usuario'];
    $nueva_password = $data['password'];

    // Obtener la configuración más reciente de contraseñas
    $sql_configuracion = "SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmt_config = $pdo->query($sql_configuracion);
    $configuracion = $stmt_config->fetch(PDO::FETCH_ASSOC);

    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseñas"]);
        exit();
    }

    $numero_historico = (int)$configuracion['numero_historico'];

    // Obtener las últimas contraseñas del usuario
    $sql_historial = "
        SELECT password 
        FROM historial_passwords 
        WHERE id_usuario = :id_usuario 
        ORDER BY fecha_creacion DESC 
        LIMIT :numero_historico
    ";
    $stmt_historial = $pdo->prepare($sql_historial);
    $stmt_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_historial->bindParam(':numero_historico', $numero_historico, PDO::PARAM_INT);
    $stmt_historial->execute();

    $contraseñas_previas = $stmt_historial->fetchAll(PDO::FETCH_COLUMN);

    foreach ($contraseñas_previas as $password_anterior) {
        if (password_verify($nueva_password, $password_anterior)) {
            echo json_encode([
                "estado" => "error",
                "mensaje" => "La nueva contraseña no puede ser igual a las últimas $numero_historico contraseñas"
            ]);
            exit();
        }
    }

    echo json_encode(["estado" => "success", "mensaje" => "La nueva contraseña cumple con los criterios de historial"]);

} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
