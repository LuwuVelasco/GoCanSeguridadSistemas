<?php
header('Content-Type: application/json');
session_start();

// Asegúrate de que el usuario tiene permisos para acceder a esta información
if (!isset($_SESSION['usuario_id']) || !$_SESSION['es_admin']) {
    echo json_encode(["estado" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

// Configuración de la conexión a la base de datos
$host = 'localhost';  // o la IP del servidor de base de datos
$dbname = 'gocan';
$user = 'postgres';
$password = 'admin';

// String de conexión utilizando PDO
$dsn = "pgsql:host=$host;dbname=$dbname";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    // Creación del objeto de conexión
    $conn = new PDO($dsn, $user, $password, $options);

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("SELECT nombre, cargo, especialidad FROM doctores");
    $stmt->execute();

    // Recuperar todos los registros de doctores
    $doctores = $stmt->fetchAll();

    // Verificar si se encontraron registros
    if (!$doctores) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontraron doctores"]);
    } else {
        echo json_encode(["estado" => "success", "doctores" => $doctores]);
    }
} catch (PDOException $e) {
    // Manejo de errores de conexión o de consulta SQL
    echo json_encode(["estado" => "error", "mensaje" => "Error de conexión: " . $e->getMessage()]);
}
?>
