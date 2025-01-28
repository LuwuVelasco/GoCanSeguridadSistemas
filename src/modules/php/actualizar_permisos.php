<?php
header('Content-Type: application/json');
include 'conexion.php'; // Conexión a la base de datos

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['permisos']) || !isset($data['id_rol'])) {
    echo json_encode(['error' => true, 'message' => 'Datos incompletos']);
    exit;
}

$idRol = $data['id_rol'];
$permisos = $data['permisos'];

try {
    // Construcción de la consulta SQL dinámicamente
    $setClauses = [];
    foreach ($permisos as $permiso) {
        $setClauses[] = "{$permiso['permiso']} = " . ($permiso['habilitado'] ? 'true' : 'false');
    }
    $setQuery = implode(", ", $setClauses);

    $query = "UPDATE roles_y_permisos SET $setQuery WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => true, 'message' => 'No se pudo actualizar los permisos']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
