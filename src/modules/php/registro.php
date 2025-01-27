<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);
date_default_timezone_set('America/La_Paz');
if (isset($data['verified']) && $data['verified'] == true) {
    if (isset($data['email'], $data['nombre'], $data['password'])) {
        $email = $data['email'];
        $nombre = $data['nombre'];
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $rol_id = 3; // Asignar un rol por defecto para los nuevos usuarios
        $fecha_registro = date('Y-m-d H:i:s'); // Obtener la fecha actual
        $conexion = pg_connect("dbname=gocan user=postgres password=admin");
        if (!$conexion) {
            echo json_encode(["estado" => "error", "mensaje" => "Error de conexión a la base de datos"]);
            exit();
        }
        // Verificar si el correo ya está registrado
        $sql_verificar = "SELECT COUNT(*) AS total FROM usuario WHERE email = $1";
        $resultado_verificar = pg_query_params($conexion, $sql_verificar, array($email));
        if ($resultado_verificar) {
            $row_verificar = pg_fetch_assoc($resultado_verificar);
            if ($row_verificar['total'] > 0) {
                echo json_encode(["estado" => "error", "mensaje" => "El correo ya está registrado"]);
                exit();
            }
        }
        // Insertar en la tabla usuario
        $sql_usuario = "INSERT INTO usuario (email, nombre, password, fecha_registro, rol_id) VALUES ($1, $2, $3, $4, $5) RETURNING id_usuario";
        $stmt = pg_prepare($conexion, "insert_usuario", $sql_usuario);
        if ($stmt === false) {
            echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta"]);
            exit();
        }

        $resultado_usuario = pg_execute($conexion, "insert_usuario", array($email, $nombre, $hashedPassword, $fecha_registro, $rol_id));
        if (!$resultado_usuario) {
            $error = pg_last_error($conexion);
            echo json_encode(["estado" => "error", "mensaje" => "Error al insertar usuario: " . $error]);
            exit();
        }

        $row = pg_fetch_assoc($resultado_usuario);
        $id_usuario = $row['id_usuario'];

        // Obtener id_configuracion más reciente para historial_passwords
        $sql_configuracion = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
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

        // Insertar en historial_passwords
        $sql_historial = "INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) VALUES ($1, $2, NOW(), $3, $4)";
        $stmt_historial = pg_prepare($conexion, "insert_historial", $sql_historial);
        if ($stmt_historial === false) {
            echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta de historial"]);
            exit();
        }

        $resultado_historial = pg_execute($conexion, "insert_historial", array($id_usuario, $hashedPassword, $id_configuracion, true));
        if (!$resultado_historial) {
            $error = pg_last_error($conexion);
            echo json_encode(["estado" => "error", "mensaje" => "Error al insertar historial de contraseña: " . $error]);
            exit();
        }

        echo json_encode(["estado" => "success", "mensaje" => "Usuario y contraseña registrados correctamente", "id_usuario" => $id_usuario]);
        pg_close($conexion);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Token no verificado"]);
    exit();
}
?>