<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir los datos del formulario
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$edad_year = $_POST['edad_year'] ?? 0;
$edad_month = $_POST['edad_month'] ?? 0;
$edad_day = $_POST['edad_day'] ?? 0;
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

// Validar que los campos no estén vacíos
if (empty($nombre_mascota) || empty($nombre_propietario) || (!is_numeric($edad_year) && !is_numeric($edad_month) && !is_numeric($edad_day))) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios y la edad debe ser un número válido"]);
    exit;
}

// Verificar si el nombre del propietario existe
$query = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result = pg_query_params($conexion, $query, [$nombre_propietario]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $id_usuario = $row['id_usuario'];

    // Insertar los datos en la base de datos
    $insert_query = "INSERT INTO mascota (nombre_mascota, edad_year, edad_month, edad_day, id_usuario) 
                     VALUES ($1, $2, $3, $4, $5)";
    $insert_result = pg_query_params($conexion, $insert_query, [$nombre_mascota, $edad_year, $edad_month, $edad_day, $id_usuario]);

    if ($insert_result) {
        echo json_encode(["estado" => "success", "mensaje" => "Mascota registrada exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al registrar la mascota"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}
?>
