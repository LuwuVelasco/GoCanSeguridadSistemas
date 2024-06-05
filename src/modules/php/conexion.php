<?php
// Parámetros de conexión a la base de datos
$host = "localhost";
$dbname = "gocan";
$user = "postgres";
$password = "admin";
$port = "5432";

// String de conexión para PostgreSQL
$conexion_string = "host=$host port=$port dbname=$dbname user=$user password=$password";


try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos: " . $e->getMessage()]);
    exit;
}
// Conectar a la base de datos
$conexion = pg_connect($conexion_string);
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

return $conexion;
?>
