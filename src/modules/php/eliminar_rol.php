<?php
header('Content-Type: application/json');
include 'conexion.php'; // Conexión a la base de datos

if (!isset($_GET['id_rol'])) {
    echo json_encode(['error' => true, 'message' => 'ID de rol no proporcionado']);
    exit;
}

$idRol = $_GET['id_rol'];

try {
    // Verificar si hay usuarios asignados a este rol
    $checkUsersQuery = "SELECT COUNT(*) as count FROM usuario WHERE rol_id = :id_rol";
    $stmtCheck = $pdo->prepare($checkUsersQuery);
    $stmtCheck->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
    $stmtCheck->execute();
    $userCount = $stmtCheck->fetch(PDO::FETCH_ASSOC)['count'];

    if ($userCount > 0) {
        echo json_encode(['error' => true, 'message' => 'No se puede eliminar el rol porque hay usuarios asignados a él']);
        exit;
    }

    // Proceder a eliminar el rol
    $query = "DELETE FROM roles_y_permisos WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => true, 'message' => 'No se pudo eliminar el rol']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
