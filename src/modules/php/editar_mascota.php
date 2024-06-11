<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/Users/micro/Downloads/php-error.log');

$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

$id_mascota = $_POST['id_mascota'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$edad = intval($_POST['edad'] ?? 0);
$period = $_POST['period'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

if (empty($id_mascota) || empty($nombre_mascota) || empty($tipo) || empty($raza) || empty($nombre_propietario) || !is_numeric($edad) || $edad == 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios y la edad debe ser un número válido mayor a 0"]);
    exit;
}

// Verificar si el nombre del propietario existe
$query = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result = pg_query_params($conexion, $query, [$nombre_propietario]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $id_usuario = $row['id_usuario'];

    $query = "
        UPDATE mascota
        SET 
            nombre_mascota = $1, 
            edad_year = CASE WHEN $2 = 'ano' THEN $3::int ELSE 0 END,
            edad_month = CASE WHEN $2 = 'mes' THEN $3::int ELSE 0 END,
            edad_day = CASE WHEN $2 = 'dia' THEN $3::int ELSE 0 END,
            tipo = $4,
            raza = $5,
            id_usuario = $6
        WHERE id_mascota = $7";

    $params = array($nombre_mascota, $period, $edad, $tipo, $raza, $id_usuario, $id_mascota);
    $result = pg_query_params($conexion, $query, $params);

    if ($result) {
        echo json_encode(["estado" => "success"]);
    } else {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota: " . $error]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}
?>
