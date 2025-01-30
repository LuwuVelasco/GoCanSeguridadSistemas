<?php
header('Content-Type: application/json');
include 'conexion.php';

// Intenta conectar y ejecutar la actualización
try {
    // Asegúrate de que 'estado' y 'id' están definidos
    $estado = $_POST['estado'];
    $id = $_POST['id'];

    // Preparar y ejecutar la consulta
    $result = pg_query_params($conexion, "UPDATE docotores SET estado = $1 WHERE id = $2", array($estado, $id));
    if ($result === false) {
        throw new Exception("Error al ejecutar la actualización.");
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($conexion)) {
        pg_close($conexion);
    }
}
?>