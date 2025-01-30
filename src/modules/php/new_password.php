<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`
date_default_timezone_set('America/La_Paz');

try {
    // Obtener los datos enviados (soporta JSON y x-www-form-urlencoded)
    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

    if (!isset($data['email'], $data['new_password'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Email o contraseña no pueden estar vacíos"]);
        exit;
    }

    $email = $data['email'];
    $newPassword = $data['new_password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    if (!$hashedPassword) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al hashear la contraseña"]);
        exit;
    }

    // Obtener el id_usuario a partir del email
    $sql_usuario = "SELECT id_usuario FROM usuario WHERE email = :email";
    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_usuario->execute();
    $user_data = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
        exit;
    }

    $id_usuario = $user_data['id_usuario'];

    // Obtener la configuración de número histórico de contraseñas
    $sql_configuracion = "SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmt_config = $pdo->prepare($sql_configuracion);
    $stmt_config->execute();
    $configuracion = $stmt_config->fetch(PDO::FETCH_ASSOC);

    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener la configuración de contraseñas"]);
        exit;
    }

    $numero_historico = (int) $configuracion['numero_historico'];

    // Validar que la nueva contraseña no coincida con las últimas N contraseñas
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
        if (password_verify($newPassword, $password_anterior)) {
            echo json_encode([
                "estado" => "error",
                "mensaje" => "La nueva contraseña no puede ser igual a las últimas $numero_historico contraseñas"
            ]);
            exit;
        }
    }

    // Desactivar las contraseñas anteriores del historial
    $sql_update_historial = "
        UPDATE historial_passwords 
        SET estado = false 
        WHERE id_usuario = :id_usuario AND estado = true
    ";
    $stmt_update_historial = $pdo->prepare($sql_update_historial);
    $stmt_update_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_update_historial->execute();

    // Guardar la nueva contraseña en el historial
    $sql_guardar_historial = "
        INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) 
        VALUES (:id_usuario, :password, NOW(), (SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1), true)
    ";
    $stmt_guardar_historial = $pdo->prepare($sql_guardar_historial);
    $stmt_guardar_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_guardar_historial->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt_guardar_historial->execute();

    // Eliminar la contraseña más antigua si excede el historial permitido
    $sql_count_history = "SELECT COUNT(*) AS total FROM historial_passwords WHERE id_usuario = :id_usuario";
    $stmt_count_history = $pdo->prepare($sql_count_history);
    $stmt_count_history->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_count_history->execute();
    $count_data = $stmt_count_history->fetch(PDO::FETCH_ASSOC);

    if ($count_data['total'] > $numero_historico) {
        $sql_delete_oldest = "
            DELETE FROM historial_passwords
            WHERE id_password IN (
                SELECT id_password
                FROM historial_passwords
                WHERE id_usuario = :id_usuario
                ORDER BY fecha_creacion ASC
                LIMIT 1
            )
        ";
        $stmt_delete_oldest = $pdo->prepare($sql_delete_oldest);
        $stmt_delete_oldest->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_delete_oldest->execute();
    }

    // Actualizar la contraseña del usuario
    $sql_update = "UPDATE usuario SET password = :password WHERE email = :email";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt_update->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt_update->execute();

    echo json_encode(["estado" => "success", "mensaje" => "Contraseña actualizada correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
