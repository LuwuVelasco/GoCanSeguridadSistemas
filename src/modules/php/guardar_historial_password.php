<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`
date_default_timezone_set('America/La_Paz');

try {
    // Obtener los datos enviados (soporta JSON y x-www-form-urlencoded)
    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id_usuario'], $data['password'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }

    $id_usuario = $data['id_usuario'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash de la contraseña

    // Obtener el id_configuracion más reciente de la tabla configuracion_passwords
    $sql_configuracion = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmt_config = $pdo->prepare($sql_configuracion);
    $stmt_config->execute();
    $configuracion = $stmt_config->fetch(PDO::FETCH_ASSOC);

    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"]);
        exit();
    }

    $id_configuracion = $configuracion['id_configuracion'];

    // Insertar en la tabla historial_passwords
    $sql_historial = "INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) VALUES (:id_usuario, :password, NOW(), :id_configuracion, :estado)";
    $stmt = $pdo->prepare($sql_historial);
    $estado = true;
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':id_configuracion', $id_configuracion, PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);

    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Historial de contraseña guardado correctamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al insertar en historial de contraseñas"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
