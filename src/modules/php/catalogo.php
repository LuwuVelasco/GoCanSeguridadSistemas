<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener los datos enviados en la solicitud POST
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['nombre'], $data['descripcion'], $data['precio'], $data['categoria'], $data['id_usuario'], $data['imagen'])) {
        echo json_encode(["error" => true, "message" => "Faltan datos requeridos"]);
        exit;
    }

    // Preparar la sentencia SQL con `PDO`
    $stmt = $pdo->prepare("INSERT INTO producto (nombre, descripcion, precio, categoria, id_usuario, imagen) 
                           VALUES (:nombre, :descripcion, :precio, :categoria, :id_usuario, :imagen)");

    // Vincular parámetros de manera segura
    $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);
    $stmt->bindParam(':precio', $data['precio'], PDO::PARAM_STR);
    $stmt->bindParam(':categoria', $data['categoria'], PDO::PARAM_STR);
    $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
    $stmt->bindParam(':imagen', $data['imagen'], PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Producto registrado con éxito."]);
} catch (PDOException $e) {
    echo json_encode(["error" => true, "message" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
