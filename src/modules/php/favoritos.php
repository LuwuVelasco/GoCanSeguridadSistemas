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

    // Asumir que el id_usuario es enviado como parámetro de la URL
    $id_usuario = $_GET['id_usuario'];

    // Preparar la sentencia SQL para contar los productos asociados al id_usuario
    $stmt = $conn->prepare("SELECT COUNT(*) AS cantidad FROM producto WHERE id_usuario = :id_usuario");

    // Vincular el parámetro id_usuario
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

    // Ejecutar la sentencia y obtener el resultado
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver la cantidad de productos en formato JSON
    echo json_encode($resultado);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>