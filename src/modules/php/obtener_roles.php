<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Obtener todos los roles
    $query = "SELECT id_rol, nombre_rol FROM roles_y_permisos ORDER BY id_rol";
    $stmt = $pdo->query($query);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los datos en formato JSON
    echo json_encode($roles);
} catch (Exception $e) {
    // Manejar errores y devolver una respuesta JSON
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
