<?php
header('Content-Type: application/json');

// Desactivar errores HTML
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/Users/micro/Downloads/php-error.log'); // Reemplaza con la ruta real al archivo de log

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$id_mascota = $_POST['id_mascota'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$edad = $_POST['edad'] ?? 0;
$period = $_POST['period'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

// Validar campos
if (empty($id_mascota) || empty($nombre_mascota) || empty($tipo) || empty($raza) || empty($nombre_propietario) || !is_numeric($edad)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios y la edad debe ser un número válido"]);
    exit;
}

$query = "
    UPDATE mascota
    SET 
        nombre_mascota = $1, 
        edad_year = CASE WHEN $2 = 'ano' THEN $3 ELSE NULL END,
        edad_month = CASE WHEN $2 = 'mes' THEN $3 ELSE NULL END,
        edad_day = CASE WHEN $2 = 'dia' THEN $3 ELSE NULL END,
        tipo = $4,
        raza = $5
    WHERE id_mascota = $6";

$params = array($nombre_mascota, $period, $edad, $tipo, $raza, $id_mascota);

$result = pg_query_params($conexion, $query, $params);

if ($result) {
    echo json_encode(["estado" => "success"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
}
?>
