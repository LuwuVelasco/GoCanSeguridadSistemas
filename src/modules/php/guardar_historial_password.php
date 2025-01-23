<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['id_usuario'], $data['password'])) {
    $id_usuario = $data['id_usuario'];
    $password = $data['password'];

    // Conexión a la base de datos
    $conexion = pg_connect("dbname=gocan user=postgres password=admin");
    if (!$conexion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error de conexión"]);
        exit();
    }

    // Obtener el id_configuracion más reciente
    $sql_configuracion = "SELECT id_configuracion FROM configuracion_password ORDER BY id_configuracion DESC LIMIT 1";
    $resultado_configuracion = pg_query($conexion, $sql_configuracion);
    if (!$resultado_configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener configuración de contraseña"]);
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
        echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta"]);
        exit();
    }

    $resultado_historial = pg_execute($conexion, "insert_historial", array($id_usuario, $password, $id_configuracion, true));
    if (!$resultado_historial) {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "Error al insertar historial de contraseña: " . $error]);
        exit();
    }

    echo json_encode(["estado" => "success", "mensaje" => "Historial de contraseña guardado correctamente"]);
    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit();
}