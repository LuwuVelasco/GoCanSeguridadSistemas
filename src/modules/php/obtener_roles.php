<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Consulta para obtener todos los roles
    $query = "SELECT id, nombre FROM rol";
    $result = pg_query($conexion, $query);

    if (!$result) {
        throw new Exception("Error al ejecutar la consulta de roles.");
    }

    $roles = [];
    while ($row = pg_fetch_assoc($result)) {
        $roles[] = $row;
    }

    // Devolver los roles en formato JSON
    echo json_encode($roles);
} catch (Exception $e) {
    // Devuelve el error en formato JSON
    echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
}
?>
