<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Obtén los nombres de las columnas excepto `id_rol` y `nombre_rol`
    $query = "
        SELECT column_name
        FROM information_schema.columns
        WHERE table_name = 'roles_y_permisos'
        AND column_name NOT IN ('id_rol', 'nombre_rol')
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Devuelve la lista de permisos
    $result = array_map(function ($column) {
        return [
            'permiso' => $column,
        ];
    }, $columns);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>
