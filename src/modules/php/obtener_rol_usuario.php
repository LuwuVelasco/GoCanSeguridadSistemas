<?php
header('Content-Type: application/json');

// Incluir la conexión con PDO
$pdo = include 'conexion.php';

// Validar que se haya enviado el ID del usuario
if (!isset($_POST['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "ID del usuario no proporcionado"]);
    exit;
}

$id_usuario = $_POST['id_usuario'];

try {
    // Consulta para obtener el rol del usuario usando PDO
    $sql = "
        SELECT 
            u.id_usuario, 
            r.id_rol, 
            r.nombre_rol
        FROM usuario u
        INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
        WHERE u.id_usuario = :id_usuario
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Rol encontrado, devolver información
        echo json_encode([
            "estado" => "success",
            "id_rol" => $row['id_rol'],
            "nombre_rol" => $row['nombre_rol']
        ]);
    } else {
        // Rol no encontrado
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Rol no encontrado para el usuario especificado"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la consulta: " . $e->getMessage()]);
}
?>
