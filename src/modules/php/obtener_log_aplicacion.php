<?php
header('Content-Type: application/json');
include 'conexion.php'; // Archivo de conexión a la base de datos

try {
    // Consulta para obtener los logs de la aplicación
    $query = "
        SELECT 
            fecha_hora, 
            nombre_usuario, 
            accion, 
            funcion_afectada, 
            dato_modificado, 
            descripcion,
            valor_original
        FROM log_aplicacion
        ORDER BY fecha_hora DESC";
    $stmt = $pdo->query($query);

    // Obtener todos los registros
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los logs como JSON
    echo json_encode($logs);
} catch (PDOException $e) {
    // Manejo de errores
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener los logs: " . $e->getMessage()]);
}
?>
