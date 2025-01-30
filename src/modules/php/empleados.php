<?php
include 'conexion.php';

try {
    $nombre = $_POST['nombre'] ?? 'No proporcionado';
    $cargo = $_POST['cargo'] ?? 'No proporcionado';
    $especialidad = $_POST['especialidad'] ?? 'No proporcionado';

    $stmt = $conexion->prepare("INSERT INTO doctores (nombre, cargo, especialidad) VALUES (:nombre, :cargo, :especialidad)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':cargo', $cargo);
    $stmt->bindParam(':especialidad', $especialidad);

    if ($stmt->execute()) {
        echo "Veterinario registrado con éxito.";
    } else {
        echo "Error al registrar el veterinario.";
    }
} catch (PDOException $e) {
    echo "Error de conexión o consulta: " . $e->getMessage();
}

$conexion = null;
