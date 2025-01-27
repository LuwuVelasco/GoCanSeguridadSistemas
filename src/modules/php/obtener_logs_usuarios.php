<?php
header('Content-Type: application/json');
include 'conexion.php'; // Archivo de conexión a la base de datos

try {
    // Consulta para obtener los logs de usuarios
    $query = "SELECT fecha_hora, nombre_usuario, accion, descripcion FROM log_usuarios ORDER BY fecha_hora DESC";
    $stmt = $pdo->query($query);

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($logs);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener los logs: " . $e->getMessage()]);
}
?>
