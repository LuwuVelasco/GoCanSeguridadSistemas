<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$id_especialidad = $_POST['id_especialidad'] ?? '';

// Imprimir los datos recibidos para depuración
error_log("Nombre: $nombre, Cargo: $cargo, Especialidad: $id_especialidad");

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($cargo) || empty($id_especialidad)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Consulta para insertar un nuevo veterinario
$sql = "INSERT INTO public.doctores (nombre, cargo, id_especialidad) VALUES ($1, $2, $3) RETURNING id_doctores";
$result = pg_prepare($conexion, "insert_vet_query", $sql);
$result = pg_execute($conexion, "insert_vet_query", array($nombre, $cargo, $id_especialidad));

if ($result) {
    $row = pg_fetch_assoc($result);
    echo json_encode(["estado" => "success", "id_doctores" => $row['id_doctores']]);
} else {
    $error = pg_last_error($conexion);
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar el veterinario: $error"]);
}

pg_close($conexion);
?>
