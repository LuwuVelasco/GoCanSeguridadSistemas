<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['id_usuario'])) {
    $id_usuario = $data['id_usuario'];

    // Conexión a la base de datos
    $conexion = pg_connect("dbname=gocan user=postgres password=admin");
    if (!$conexion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error de conexión a la base de datos"]);
        exit();
    }

    // Obtener la configuración más reciente
    $sql_configuracion = "SELECT tiempo_vida_util FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $resultado_configuracion = pg_query($conexion, $sql_configuracion);
    if (!$resultado_configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener configuración de contraseñas"]);
        exit();
    }

    $configuracion = pg_fetch_assoc($resultado_configuracion);
    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseñas"]);
        exit();
    }

    $tiempo_vida_util = (int)$configuracion['tiempo_vida_util'];

    // Obtener la contraseña más reciente del usuario
    $sql_historial = "
        SELECT fecha_creacion 
        FROM historial_passwords 
        WHERE id_usuario = $1 AND estado = true 
        ORDER BY fecha_creacion DESC LIMIT 1
    ";
    $stmt_historial = pg_prepare($conexion, "select_fecha_creacion", $sql_historial);
    if ($stmt_historial === false) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta de historial"]);
        exit();
    }

    $resultado_historial = pg_execute($conexion, "select_fecha_creacion", array($id_usuario));
    if (!$resultado_historial) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al consultar historial de contraseñas"]);
        exit();
    }

    $registro = pg_fetch_assoc($resultado_historial);
    if (!$registro) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró una contraseña activa para el usuario"]);
        exit();
    }

    $fecha_creacion = new DateTime($registro['fecha_creacion']);
    $fecha_actual = new DateTime();
    $fecha_expiracion = $fecha_creacion->add(new DateInterval("P{$tiempo_vida_util}D"));

    if ($fecha_actual > $fecha_expiracion) {
        echo json_encode(["estado" => "error", "mensaje" => "La contraseña ha expirado"]);
    } else {
        echo json_encode(["estado" => "success", "mensaje" => "La contraseña sigue siendo válida"]);
    }

    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit();
}
?>
