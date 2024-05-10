<?php
$host = "localhost";
$port = "5432";
$dbname = "gocan";
$username = "postgres";
$password = "admin";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Asumiendo que los datos del producto son enviados como JSON en el cuerpo de la solicitud POST
    $data = json_decode(file_get_contents("php://input"), true);

    // Preparar la sentencia SQL
    $stmt = $conn->prepare("INSERT INTO producto (nombre, descripcion, precio, categoria, id_usuario) VALUES (:nombre, :descripcion, :precio, :categoria, 1)");

    // Vincular parámetros
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':descripcion', $data['descripcion']);
    $stmt->bindParam(':precio', $data['precio']);
    $stmt->bindParam(':categoria', $data['categoria']);

    // Ejecutar la sentencia
    $stmt->execute();

    echo "Producto registrado con éxito.";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>