<?php
header('Content-Type: application/json');
include 'conexion.php';

if (!isset($_GET['id_rol'])) {
    echo json_encode(['error' => true, 'message' => 'ID de rol no especificado']);
    exit;
}

$idRol = $_GET['id_rol'];

try {
    // Consulta para obtener los permisos del rol
    $query = "SELECT * FROM roles_y_permisos WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
    $stmt->execute();
    $rolData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rolData) {
        echo json_encode(['error' => true, 'message' => 'No se encontraron permisos para este rol']);
        exit;
    }

    // Convertir permisos booleanos a un formato adecuado
    $permisos = [];
    foreach ($rolData as $permiso => $valor) {
        if (!in_array($permiso, ['id_rol', 'nombre_rol'])) {
            $permisos[] = [
                'permiso' => $permiso,
                'habilitado' => ($valor === 't' || $valor === true) ? true : false,
            ];
        }
    }

    echo json_encode($permisos);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
