<?php
header('Content-Type: application/json');
session_start();

// Comentar temporalmente la verificación de permisos para pruebas
// if (!isset($_SESSION['usuario_id']) || !$_SESSION['es_admin']) {
//     echo json_encode(["estado" => "error", "mensaje" => "Acceso denegado"]);
//     exit;
// }

// Conectar a la base de datos
include('conexion.php'); // Incluye el archivo de conexión a la base de datos

// Recibir los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$cargo = $_POST['cargo'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';
$estado = 'activo'; // Estado inicial para nuevos veterinarios

// Validación básica
if (empty($nombre) || empty($cargo) || empty($especialidad)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

try {
    $sql = "INSERT INTO doctores (nombre, cargo, id_especialidad, estado) 
            VALUES (:nombre, :cargo, :especialidad, :estado)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':cargo' => $cargo,
        ':especialidad' => $especialidad,
        ':estado' => $estado
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["estado" => "success", "mensaje" => "Veterinario registrado exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar el veterinario"]);
    }
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage(), 0);
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
