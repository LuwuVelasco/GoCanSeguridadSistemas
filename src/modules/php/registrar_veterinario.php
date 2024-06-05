<?php
// Permitir solicitudes desde cualquier origen
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

// Verificar si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// Recoger los datos enviados en POST
$nombre = $_POST['nombre'] ?? null;
$cargo = $_POST['cargo'] ?? null;
$especialidad = $_POST['especialidad'] ?? null;
$estado = $_POST['estado'] ?? null;

// Validar que todos los campos requeridos están presentes
if (!$nombre || !$cargo || !$especialidad || !$estado) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Configuración de la conexión a la base de datos
$dsn = 'pgsql:host=localhost;dbname=GoCan';
$user = 'postgres'; // Asegúrate de ajustar estos detalles según tu configuración
$password = 'admin'; // Asegúrate de ajustar estos detalles según tu configuración

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Preparar la sentencia SQL para inserción de datos
    $sql = "INSERT INTO doctores (nombre, cargo, especialidad, estado) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$nombre, $cargo, $especialidad, $estado]);

    // Verificar si la inserción fue exitosa
    if ($result) {
        echo json_encode(["estado" => "success", "mensaje" => "Doctor registrado exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al insertar el doctor"]);
    }
    catch (PDOException $e) {
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Error de conexión a la base de datos: " . $e->getMessage(),
            "code" => $e->getCode(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
    
?>
