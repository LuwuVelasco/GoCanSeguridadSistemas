<?php
$servername = "localhost";
$username = "postgres";
$password = "admin";
$dbname = "gocan";

try {
    $conn = new PDO("pgsql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $nombre = $_POST['nombre'] ?? 'No proporcionado';
    $cargo = $_POST['cargo'] ?? 'No proporcionado';
    $especialidad = $_POST['especialidad'] ?? 'No proporcionado';

    $stmt = $conn->prepare("INSERT INTO doctores (nombre, cargo, especialidad) VALUES (:nombre, :cargo, :especialidad)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':cargo', $cargo);
    $stmt->bindParam(':especialidad', $especialidad);

    if ($stmt->execute()) {
        echo "<p>Veterinario registrado con éxito.</p>";
    } else {
        echo "<p>Error al registrar el veterinario.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error de conexión o consulta: " . $e->getMessage() . "</p>";
}

$conn = null;
