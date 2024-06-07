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

$id_mascota = $_GET['id_mascota'] ?? '';

if (empty($id_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no proporcionado"]);
    exit;
}

$query = "
    SELECT 
        m.id_mascota,
        m.nombre_mascota, 
        m.edad_year AS edad,
        CASE
            WHEN m.edad_day IS NOT NULL THEN 'dia'
            WHEN m.edad_month IS NOT NULL THEN 'mes'
            WHEN m.edad_year IS NOT NULL THEN 'ano'
            ELSE NULL
        END AS period,  
        m.tipo, 
        m.raza, 
        u.nombre as nombre_propietario
    FROM 
        mascota m
    JOIN 
        usuario u ON m.id_usuario = u.id_usuario
    WHERE 
        m.id_mascota = $1";

$result = pg_query_params($conexion, $query, array($id_mascota));

if ($result) {
    if ($row = pg_fetch_assoc($result)) {
        echo json_encode(["estado" => "success", "mascota" => $row]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Mascota no encontrada"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener la mascota"]);
}
?>
