<?php
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
$estado = $_POST['estado'] ?? '';
$id_especialidad = $_POST['especialidad'] ?? '';

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($cargo) || empty($id_especialidad) || empty($estado)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Preparar y ejecutar la consulta SQL para insertar los datos
$sql = "INSERT INTO doctores (nombre, cargo, estado, id_especialidad) VALUES ($1, $2, $3, $4) RETURNING id_doctores";
$result = pg_query_params($conexion, $sql, array($nombre, $cargo, $estado, $id_especialidad));
if ($stmt = pg_prepare($conexion, "insert_doctor", $sql)) {
    $result = pg_execute($conexion, "insert_doctor", array($nombre, $cargo, $estado, $id_especialidad));
    if ($result) {
        $row = pg_fetch_assoc($result);
        echo json_encode(["estado" => "success", "id_doctores" => $row['id_doctores']]);
    } else {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar el veterinario: $error"]);
    }
} else {
    $error = pg_last_error($conexion);
    echo json_encode(["estado" => "error", "mensaje" => "Error en la preparación de la consulta: $error"]);
}

// Cerrar la conexión
pg_close($conexion);
?>