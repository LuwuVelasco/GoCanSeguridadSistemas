<?php
header('Content-Type: application/json');
include 'conexion.php';

// Forzar la zona horaria a La Paz, Bolivia.
pg_query($conexion, "SET TIME ZONE 'America/La_Paz'");

$email = $_POST['email'];
$newPassword = $_POST['new_password'];

// Verificar que los datos no estén vacíos
if (empty($email) || empty($newPassword)) {
    echo json_encode(["estado" => "error", "mensaje" => "Email o contraseña no pueden estar vacíos"]);
    exit;
}

// Hashear la nueva contraseña
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
if (!$hashedPassword) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al hashear la contraseña"]);
    exit;
}

// Obtener el id_usuario a partir del email
$sql_usuario = "SELECT id_usuario FROM usuario WHERE email = $1";
$result_usuario = pg_prepare($conexion, "select_usuario", $sql_usuario);
$result_usuario = pg_execute($conexion, "select_usuario", array($email));

if (!$result_usuario || pg_num_rows($result_usuario) === 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
    exit;
}

$user_data = pg_fetch_assoc($result_usuario);
$id_usuario = $user_data['id_usuario'];

// Validar que la nueva contraseña no esté en las últimas N contraseñas del historial
$sql_configuracion = "SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
$result_configuracion = pg_query($conexion, $sql_configuracion);
if (!$result_configuracion) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener la configuración de contraseñas"]);
    exit;
}

$configuracion = pg_fetch_assoc($result_configuracion);
$numero_historico = (int)$configuracion['numero_historico'];

$sql_historial = "
    SELECT password 
    FROM historial_passwords 
    WHERE id_usuario = $1 
    ORDER BY fecha_creacion DESC 
    LIMIT $2
";
$result_historial = pg_prepare($conexion, "select_historial", $sql_historial);
$result_historial = pg_execute($conexion, "select_historial", array($id_usuario, $numero_historico));

if (!$result_historial) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al consultar el historial de contraseñas"]);
    exit;
}

$contraseñas_previas = pg_fetch_all_columns($result_historial, 0);
foreach ($contraseñas_previas as $password_anterior) {
    if (password_verify($newPassword, $password_anterior)) {
        echo json_encode([
            "estado" => "error",
            "mensaje" => "La nueva contraseña no puede ser igual a las últimas $numero_historico contraseñas"
        ]);
        exit;
    }
}

// Actualizar la contraseña anterior a estado `false` en el historial
$sql_update_historial = "
    UPDATE historial_passwords 
    SET estado = false 
    WHERE id_usuario = $1 AND estado = true
";
$result_update_historial = pg_prepare($conexion, "update_historial", $sql_update_historial);
$result_update_historial = pg_execute($conexion, "update_historial", array($id_usuario));

if (!$result_update_historial) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar el estado del historial"]);
    exit;
}

// Guardar la nueva contraseña en el historial (fecha_creacion con NOW(), pero en zona horaria La Paz)
$sql_guardar_historial = "
    INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) 
    VALUES ($1, $2, NOW(), (SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1), true)
";
$result_guardar_historial = pg_prepare($conexion, "guardar_historial", $sql_guardar_historial);
$result_guardar_historial = pg_execute($conexion, "guardar_historial", array($id_usuario, $hashedPassword));

if (!$result_guardar_historial) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al guardar la nueva contraseña en el historial"]);
    exit;
}

// Comprobar si el historial supera el numero_historico y si es así, borrar la contraseña más antigua
$sql_count_history = "SELECT COUNT(*) AS total FROM historial_passwords WHERE id_usuario = $1";
$result_count_history = pg_prepare($conexion, "count_history", $sql_count_history);
$result_count_history = pg_execute($conexion, "count_history", array($id_usuario));
$count_data = pg_fetch_assoc($result_count_history);

if ($count_data['total'] > $numero_historico) {
    // Borramos la contraseña más antigua de este usuario
    $sql_delete_oldest = "
        DELETE FROM historial_passwords
        WHERE id_password IN (
            SELECT id_password
            FROM historial_passwords
            WHERE id_usuario = $1
            ORDER BY fecha_creacion ASC
            LIMIT 1
        )
    ";
    $result_delete_oldest = pg_prepare($conexion, "delete_oldest", $sql_delete_oldest);
    $result_delete_oldest = pg_execute($conexion, "delete_oldest", array($id_usuario));
    if (!$result_delete_oldest) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la contraseña más antigua"]);
        exit;
    }
}

// Actualizar la contraseña del usuario
$sql_update = "UPDATE usuario SET password = $1 WHERE email = $2";
$result_update = pg_prepare($conexion, "update_query", $sql_update);
$result_update = pg_execute($conexion, "update_query", array($hashedPassword, $email));

if ($result_update) {
    echo json_encode(["estado" => "success", "mensaje" => "Contraseña actualizada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la contraseña"]);
}

pg_close($conexion);
?>
