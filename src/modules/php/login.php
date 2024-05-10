<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=Jesus.2004");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

// Consulta para buscar al usuario por email, contraseña y obtener el campo cargo
$sql = "SELECT id_usuario, cargo FROM usuario WHERE email = $1 AND password = $2";
$result = pg_prepare($conexion, "login_query", $sql);
$result = pg_execute($conexion, "login_query", array($email, $password));

if ($row = pg_fetch_assoc($result)) {
    // Si la consulta devuelve un resultado, las credenciales son correctas
    echo json_encode(["estado" => "success", "id_usuario" => $row['id_usuario'], "cargo" => $row['cargo']]);
} else {
    // No se encontró un usuario con esas credenciales
    echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
}

pg_close($conexion);
