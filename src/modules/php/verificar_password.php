<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Recibir los datos del cliente
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

    // Obtener la configuración de contraseñas más reciente
    $sql_configuracion = "SELECT * FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
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

    $tiempo_vida_util = $configuracion['tiempo_vida_util'];
    $id_configuracion = $configuracion['id_configuracion'];

    // Obtener la contraseña vigente del usuario
    $sql_historial = "SELECT * FROM historial_passwords 
                      WHERE id_usuario = $1 AND estado = true AND id_configuracion = $2 
                      ORDER BY fecha_creacion DESC LIMIT 1";
    $stmt_historial = pg_prepare($conexion, "select_historial", $sql_historial);
    $resultado_historial = pg_execute($conexion, "select_historial", [$id_usuario, $id_configuracion]);

    if (!$resultado_historial) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener el historial de contraseñas"]);
        exit();
    }

    $historial = pg_fetch_assoc($resultado_historial);
    if (!$historial) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró contraseña vigente para el usuario"]);
        exit();
    }

    $fecha_creacion = new DateTime($historial['fecha_creacion']);
    $fecha_actual = new DateTime();
    $dias_diferencia = $fecha_creacion->diff($fecha_actual)->days;

    if ($dias_diferencia > $tiempo_vida_util) {
        // Contraseña expirada
        echo json_encode([
            "estado" => "expired",
            "mensaje" => "Tu contraseña ha expirado. Por favor, cámbiala para continuar.",
            "id_usuario" => $id_usuario
        ]);
    } else {
        // Contraseña vigente
        echo json_encode(["estado" => "success", "mensaje" => "Contraseña vigente"]);
    }

    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Falta el ID de usuario"]);
    exit();
}
