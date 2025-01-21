<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['token'], $data['verified']) && $data['verified'] == true) {
    if (isset($data['email'], $data['nombre'], $data['password'])) {
        $email = $data['email'];
        $nombre = $data['nombre'];
        // Usar password_hash con BCRYPT que es más compatible con PostgreSQL
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $token = $data['token'];
        $cargo = ($data['cargo']);

        $conexion = pg_connect("dbname=gocan user=postgres password=admin");
        if (!$conexion) {
            echo json_encode(["estado" => "error", "mensaje" => "Error de conexión"]);
            exit();
        }

        $sql_usuario = "INSERT INTO usuario (email, nombre, password, token, cargo) VALUES ($1, $2, $3, $4, $5) RETURNING id_usuario";
        $stmt = pg_prepare($conexion, "insert_usuario", $sql_usuario);
        if ($stmt === false) {
            echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta"]);
            exit();
        }

        $resultado_usuario = pg_execute($conexion, "insert_usuario", array($email, $nombre, $hashedPassword, $token, $cargo));
        if (!$resultado_usuario) {
            $error = pg_last_error($conexion);
            echo json_encode(["estado" => "error", "mensaje" => "Error al insertar usuario: " . $error]);
            exit();
        }

        $row = pg_fetch_assoc($resultado_usuario);
        echo json_encode(["estado" => "success", "id_usuario" => $row['id_usuario']]);
        pg_close($conexion);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Token inválido o no verificado"]);
    exit();
}
?>