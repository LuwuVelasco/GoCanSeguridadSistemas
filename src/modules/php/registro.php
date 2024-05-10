<?php
header('Content-Type: application/json');

// Verificar que se recibió un cuerpo JSON válido
$json = file_get_contents('php://input');
$data = json_decode($json);

// Verificar si el campo "token" está presente y no es nulo
if (isset($data->token) && !empty($data->token)) {
    $token = $data->token;
} else {
    echo json_encode(["estado" => "error_token"]);
    exit();
}

// Verificar que se recibieron los demás campos necesarios y que el token fue verificado
if (isset($data->email) && isset($data->nombre) && isset($data->password) && isset($data->cargo) && isset($data->verified) && $data->verified === true) {
    $email = $data->email;
    $nombre = $data->nombre;
    $password = $data->password;
    $cargo = $data->cargo;
} else {
    echo json_encode(["estado" => "error_campos"]);
    exit();
}


// Conexión a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error_conexion"]);
    exit();
}

// Preparar y ejecutar la consulta de inserción
$sql_usuario = "INSERT INTO usuario (email, nombre, password, token, cargo) VALUES ($1, $2, $3, $4, $5) RETURNING id_usuario";
$stmt = pg_prepare($conexion, "insert_usuario", $sql_usuario);
if ($stmt === false) {
    echo json_encode(["estado" => "error_preparar_consulta"]);
    exit();
}

$resultado_usuario = pg_execute($conexion, "insert_usuario", array($email, $nombre, $password, $token, $cargo));
if (!$resultado_usuario) {
    echo json_encode(["estado" => "error_insertar_usuario"]);
    exit();
}

// Si todo es correcto, retornar el ID del usuario registrado
$id_usuario = pg_fetch_result($resultado_usuario, 0, 'id_usuario');
echo json_encode(["estado" => "usuario_registrado", "id_usuario" => $id_usuario]);

// Cerrar la conexión a la base de datos
pg_close($conexion);
?>
