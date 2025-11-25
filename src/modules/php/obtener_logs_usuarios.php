<?php
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
    // Consulta para obtener los logs de usuarios
    $query = "SELECT fecha_hora, nombre_usuario, accion, descripcion FROM log_usuarios ORDER BY fecha_hora DESC";
    $stmt = $pdo->query($query);

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($logs);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al obtener los logs: " . $e->getMessage()]);
}
?>
