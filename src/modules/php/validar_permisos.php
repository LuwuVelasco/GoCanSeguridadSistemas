<?php
header('Content-Type: application/json');
include 'conexion.php'; // Asegúrate de que la conexión esté configurada correctamente

if (!isset($_POST['id_rol'])) {
    echo json_encode(['success' => false, 'message' => 'ID de rol no especificado']);
    exit;
}

$idRol = $_POST['id_rol'];

try {
    // Consulta para obtener los permisos del rol
    $query = "SELECT * FROM roles_y_permisos WHERE id_rol = $1";
    $result = pg_prepare($conexion, "query_permisos", $query);
    $result = pg_execute($conexion, "query_permisos", [$idRol]);

    if ($row = pg_fetch_assoc($result)) {
        // Filtrar solo los permisos (excluir ID y nombre del rol)
        $permisos = [];
        foreach ($row as $clave => $valor) {
            if (!in_array($clave, ['id_rol', 'nombre_rol'])) {
                $permisos[$clave] = ($valor === 't' || $valor === true) ? true : false;
            }
        }

        echo json_encode(['success' => true, 'permisos' => $permisos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron permisos para este rol']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los permisos: ' . $e->getMessage()]);
}
