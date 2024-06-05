<?php
// Iniciar la sesión
session_start();
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Asegurar que recibimos los parámetros necesarios
if (isset($_POST['id_usuario']) && !empty($_POST['id_usuario']) && isset($_POST['nombre_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $nombre_usuario = $_POST['nombre_usuario'];  // Recuperar el nombre del usuario desde POST
    $hora_ingreso = date('Y-m-d H:i:s'); // Obtener el timestamp actual

    // Preparar y ejecutar la consulta SQL
    $sql = "INSERT INTO actividades (id_usuario, nombre_usuario, hora_ingreso) VALUES ($1, $2, $3)";
    $result = pg_prepare($conexion, "insert_actividad", $sql);
    $result = pg_execute($conexion, "insert_actividad", array($id_usuario, $nombre_usuario, $hora_ingreso));

    if ($result) {
        echo json_encode(["estado" => "success"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar la actividad"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Datos incompletos"]);
}

pg_close($conexion);
?>