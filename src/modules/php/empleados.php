<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener datos enviados (soporta JSON y x-www-form-urlencoded)
    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

    $nombre = $data['nombre'] ?? null;
    $cargo = $data['cargo'] ?? null;
    $especialidad = $data['especialidad'] ?? null;

    // Validar que todos los datos estén presentes
    if (!$nombre || !$cargo || !$especialidad) {
        echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Preparar la consulta para insertar un nuevo doctor
    $query = "INSERT INTO doctores (nombre, cargo, especialidad) VALUES (:nombre, :cargo, :especialidad)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
    $stmt->bindParam(':especialidad', $especialidad, PDO::PARAM_STR);

    // Ejecutar la consulta y verificar el resultado
    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Veterinario registrado con éxito."]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el veterinario."]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
