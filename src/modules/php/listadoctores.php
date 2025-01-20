<?php
header('Content-Type: application/json');

// Incluir el archivo de conexión
include('conexion.php');  // Asegúrate de que la ruta sea correcta

// Consulta para obtener todos los doctores con sus especialidades
$sql = "SELECT d.id_doctores, d.nombre, d.cargo, e.nombre_especialidad AS especialidad 
        FROM doctores d
        LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
        ORDER BY d.id_doctores, d.nombre, d.cargo, e.nombre_especialidad;";

$result = pg_query($conexion, $sql);

$doctores = array();
while ($row = pg_fetch_assoc($result)) {
    $doctores[] = $row;
}

echo json_encode($doctores);

pg_close($conexion);
?>
