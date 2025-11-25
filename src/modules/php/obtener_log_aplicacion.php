<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
  'http://127.0.0.1:5500',
  'http://localhost:5500'
];
if (in_array($origin, $allowed_origins)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
