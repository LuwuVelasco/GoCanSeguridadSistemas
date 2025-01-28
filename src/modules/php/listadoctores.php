<?php
header('Content-Type: application/json');

// Incluir el archivo de conexión
include('conexion.php'); // Asegúrate de que la ruta sea correcta

// Consulta para obtener todos los doctores con sus especialidades
$sql = "SELECT d.id_doctores, d.nombre, 
        COALESCE(e.nombre_especialidad, 'Sin especialidad') AS especialidad
        FROM doctores d
        LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
        ORDER BY d.id_doctores;";

$result = pg_query($conexion, $sql);

// Verificar si la consulta fue exitosa
if (!$result) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Error al obtener los doctores: " . pg_last_error($conexion)
    ]);
    pg_close($conexion);
    exit;
}

$doctores = [];
while ($row = pg_fetch_assoc($result)) {
    $doctores[] = $row;
}

// Enviar el resultado como JSON
echo json_encode($doctores);

// Cerrar la conexión
pg_close($conexion);
?>
