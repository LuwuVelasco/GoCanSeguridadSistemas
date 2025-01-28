<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['id_usuario'], $data['password'])) {
    $id_usuario = $data['id_usuario'];
    $nueva_password = $data['password'];

    // Conexión a la base de datos
    $conexion = pg_connect("dbname=gocan user=postgres password=admin");
    if (!$conexion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error de conexión a la base de datos"]);
        exit();
    }

    // Obtener la configuración más reciente
    $sql_configuracion = "SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
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

    $numero_historico = (int)$configuracion['numero_historico'];

    // Obtener las últimas contraseñas del usuario
    $sql_historial = "
        SELECT password 
        FROM historial_passwords 
        WHERE id_usuario = $1 
        ORDER BY fecha_creacion DESC 
        LIMIT $2
    ";
    $stmt_historial = pg_prepare($conexion, "select_historial", $sql_historial);
    if ($stmt_historial === false) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta de historial"]);
        exit();
    }

    $resultado_historial = pg_execute($conexion, "select_historial", array($id_usuario, $numero_historico));
    if (!$resultado_historial) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al consultar historial de contraseñas"]);
        exit();
    }

    $contraseñas_previas = pg_fetch_all_columns($resultado_historial, 0);
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
    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit();
}
?>
