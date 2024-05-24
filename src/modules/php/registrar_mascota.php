<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=Jesus.2004");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir los datos del formulario
$propietario = $_POST['propietario'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';

// Validar que los campos no estén vacíos
if (empty($propietario) || empty($descripcion) || empty($fecha) || empty($nombre_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Verificar si se recibieron los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_mascota = $_POST['nombre_mascota'] ?? null;
    $edad = $_POST['edad'] ?? null;
    $raza = $_POST['raza'] ?? null;
    $tipo = $_POST['tipo'] ?? null;
    $id_usuario = $_POST['id_usuario'] ?? null;

    // Validar los datos recibidos
    if ($nombre_mascota && $edad && $raza && $tipo && $id_usuario) {
        // Insertar los datos en la base de datos
        try {
            $sql = "INSERT INTO mascotas (nombre_mascota, edad, raza, tipo, id_usuario) VALUES (:nombre_mascota, :edad, :raza, :tipo, :id_usuario)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre_mascota' => $nombre_mascota,
                ':edad' => $edad,
                ':raza' => $raza,
                ':tipo' => $tipo,
                ':id_usuario' => $id_usuario
            ]);

            echo json_encode(['estado' => 'success', 'mensaje' => 'Mascota registrada exitosamente']);
        } catch (PDOException $e) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Error al registrar la mascota: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['estado' => 'error', 'mensaje' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Método no permitido']);
}
?>