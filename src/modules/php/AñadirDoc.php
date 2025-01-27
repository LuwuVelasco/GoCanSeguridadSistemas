<?php
$servername = "localhost";
$username = "postgres";
$password = "admin";
$dbname = "gocan";

try {
    $conn = new PDO("pgsql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nombre = $_POST['nombre'] ?? 'No proporcionado';
    $especialidad = $_POST['especialidad'] ?? 'No proporcionado';

    $stmt = $conn->prepare("INSERT INTO doctores (nombre, especialidad) VALUES (:nombre, :especialidad)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':especialidad', $especialidad);

    $stmt->execute();
    echo json_encode(["estado" => "success", "mensaje" => "Veterinario registrado con éxito."]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el veterinario: " . $e->getMessage()]);
}

$conn = null;
?>
