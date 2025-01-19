<?php
session_start(); // Iniciar la sesión
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Validar que los datos requeridos estén presentes
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode(["estado" => "error", "mensaje" => "El email y la contraseña son obligatorios"]);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

// Consulta para buscar al usuario por email
$sql = "SELECT id_usuario, cargo, id_doctores, password FROM usuario WHERE email = $1";
$result = pg_prepare($conexion, "login_query", $sql);
$result = pg_execute($conexion, "login_query", array($email));

if ($row = pg_fetch_assoc($result)) {
    // Verificar la contraseña ingresada con el hash almacenado
    if (password_verify($password, $row['password'])) {
        // Contraseña válida, iniciar sesión
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
        // Contraseña incorrecta
        echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
    }
} else {
    // No se encontró un usuario con ese email
    echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
}

// Cerrar la conexión
pg_close($conexion);
