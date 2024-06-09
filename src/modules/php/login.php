<?php
session_start();
header('Content-Type: application/json');

// Función para manejar errores
function handle_error($mensaje) {
    echo json_encode(["estado" => "error", "mensaje" => $mensaje]);
    exit;
}

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin port='5433'");
if (!$conexion) {
    handle_error("No se pudo conectar a la base de datos");
}

$email = $_POST['email'];
$password = $_POST['password'];

// Verificar si las variables están definidas
if (!isset($email) || !isset($password)) {
    handle_error("Email y contraseña son requeridos");
}

// Consulta para buscar al usuario por email, contraseña y obtener el campo cargo y id_doctores
$sql = "SELECT id_usuario, cargo, id_doctores FROM usuario WHERE email = $1 AND password = $2";
$result = pg_prepare($conexion, "login_query", $sql);
if (!$result) {
    handle_error("Error al preparar la consulta");
}

$result = pg_execute($conexion, "login_query", array($email, $password));
if (!$result) {
    handle_error("Error al ejecutar la consulta");
}

if ($row = pg_fetch_assoc($result)) {
    if (isset($row['id_doctores'])) {
        $_SESSION['id_doctores'] = $row['id_doctores'];
    }

    echo json_encode([
        "estado" => "success",
        "id_usuario" => $row['id_usuario'],
        "cargo" => $row['cargo'],
        "id_doctores" => $row['id_doctores']
    ]);
} else {
    handle_error("El email o la contraseña son incorrectos");
}
?>