<?php
header('Content-Type: application/json');
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$nombreRol = $data['nombre_rol'];
$permisos = $data['permisos'];

try {
    $pdo->beginTransaction();

    // Insertar el nuevo rol
    $query = "INSERT INTO roles_y_permisos (nombre_rol) VALUES (:nombre_rol) RETURNING id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre_rol', $nombreRol, PDO::PARAM_STR);
    $stmt->execute();
    $idRol = $stmt->fetchColumn();

    // Insertar los permisos
    foreach ($permisos as $permiso) {
        $query = "UPDATE roles_y_permisos SET {$permiso['id_permiso']} = :habilitado WHERE id_rol = :id_rol";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':habilitado', $permiso['habilitado'] ? 'true' : 'false', PDO::PARAM_BOOL);
        $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
        $stmt->execute();
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
