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

    // 1. Verificar el rol del usuario
    $sqlRol = "
        SELECT u.rol_id, r.nombre_rol 
        FROM usuario u
        INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
        WHERE u.id_usuario = $1
    ";
    $prepRol = pg_prepare($conexion, "rol_query", $sqlRol);
    $execRol = pg_execute($conexion, "rol_query", [$id_usuario]);
    if (!$execRol) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al obtener el rol del usuario"]);
        exit();
    }

    $rowRol = pg_fetch_assoc($execRol);
    if (!$rowRol) {
        echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
        exit();
    }

    $nombreRol = $rowRol['nombre_rol'];

    // 2. Obtener la configuración de contraseñas más reciente
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

    // 3. Obtener la contraseña vigente del usuario
    $sql_historial = "
        SELECT * 
        FROM historial_passwords 
        WHERE id_usuario = $1 
        AND estado = true 
        AND id_configuracion = $2 
        ORDER BY fecha_creacion DESC 
        LIMIT 1
    ";
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

    // 4. Revisar si la contraseña está expirada
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
        pg_close($conexion);
        exit();
    }

    // 5. Verificar si el usuario no es 'Cliente' y tiene sólo 1 contraseña en historial

    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM historial_passwords
        WHERE id_usuario = $1
    ";
    $prepTotal = pg_prepare($conexion, "total_pw", $sqlTotal);
    $execTotal = pg_execute($conexion, "total_pw", [$id_usuario]);

    if (!$execTotal) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al contar contraseñas en historial"]);
        exit();
    }

    $rowTotal = pg_fetch_assoc($execTotal);
    $totalPasswords = (int)$rowTotal['total'];

    // Si rol no es 'Cliente' y total de contraseñas = 1 => forzar cambio
    if ($nombreRol !== 'Cliente' && $totalPasswords === 1) {
        echo json_encode([
            "estado" => "change_required",
            "mensaje" => "Debes cambiar tu contraseña generada automáticamente.",
            "id_usuario" => $id_usuario
        ]);
        pg_close($conexion);
        exit();
    }

    echo json_encode(["estado" => "success", "mensaje" => "Contraseña vigente"]);
    pg_close($conexion);
    exit();
    
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Falta el ID de usuario"]);
    exit();
}
