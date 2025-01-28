<?php
header('Content-Type: application/json');
include 'conexion.php';

$idRol = $_GET['id_rol'];

try {
    $query = "SELECT * FROM roles_y_permisos WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
    $stmt->execute();
    $permisos = $stmt->fetch(PDO::FETCH_ASSOC);

    // Convertir permisos booleanos en un formato adecuado
    $result = [];
    foreach ($permisos as $permiso => $valor) {
        if (!in_array($permiso, ['id_rol', 'nombre_rol'])) {
            $result[] = [
                'permiso' => $permiso,
                'habilitado' => $valor === 't', // PostgreSQL usa 't' para true
            ];
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
