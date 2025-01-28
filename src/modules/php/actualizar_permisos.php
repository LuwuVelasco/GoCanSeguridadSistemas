<?php
header('Content-Type: application/json');
include 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

try {
    foreach ($data['permisos'] as $permiso) {
        $query = "UPDATE roles_y_permisos SET {$permiso['permiso']} = :habilitado WHERE id_rol = :id_rol";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':habilitado', $permiso['habilitado'], PDO::PARAM_BOOL);
        $stmt->bindParam(':id_rol', $permiso['id_rol'], PDO::PARAM_INT);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
