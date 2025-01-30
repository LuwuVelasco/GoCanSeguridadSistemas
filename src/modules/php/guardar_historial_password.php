<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
include 'conexion.php';
date_default_timezone_set('America/La_Paz');
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['id_usuario'], $data['password'])) {
    $id_usuario = $data['id_usuario'];
    $password = $data['password'];

    // Obtener el id_configuracion más reciente de la tabla configuracion_passwords
    $sql_configuracion = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $resultado_configuracion = pg_query($conexion, $sql_configuracion);
    if (!$resultado_configuracion) {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener configuración de contraseña: " . $error]);
        exit();
    }

    $configuracion = pg_fetch_assoc($resultado_configuracion);
    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"]);
        exit();
    }

    $id_configuracion = $configuracion['id_configuracion'];

    // Insertar en la tabla historial_passwords
    $sql_historial = "INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) VALUES ($1, $2, NOW(), $3, $4)";
    $stmt = pg_prepare($conexion, "insert_historial", $sql_historial);
    if ($stmt === false) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta para guardar historial"]);
        exit();
    }

    $resultado_historial = pg_execute($conexion, "insert_historial", array($id_usuario, $password, $id_configuracion, true));
    if (!$resultado_historial) {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "Error al insertar en historial de contraseñas: " . $error]);
        exit();
    }

    echo json_encode(["estado" => "success", "mensaje" => "Historial de contraseña guardado correctamente"]);
    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit();
}
?>