<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Verificar que el ID del usuario fue enviado
    if (!isset($_POST['id_usuario'])) {
        throw new Exception('ID de usuario no proporcionado.');
    }

    $idUsuario = $_POST['id_usuario'];

    // Consultar el rol del usuario
    $queryCheckRole = "SELECT rol_id FROM usuario WHERE id_usuario = $1";
    $resultCheckRole = pg_query_params($conexion, $queryCheckRole, [$idUsuario]);

    if (!$resultCheckRole || pg_num_rows($resultCheckRole) === 0) {
        throw new Exception('El usuario no existe.');
    }

    $roleData = pg_fetch_assoc($resultCheckRole);
    if ($roleData['rol_id'] == 3) { // 3 es el rol de Cliente
        throw new Exception('No puedes eliminar usuarios con el rol de Cliente.');
    }

    // Eliminar el usuario
    $queryDelete = "DELETE FROM usuario WHERE id_usuario = $1";
    $resultDelete = pg_query_params($conexion, $queryDelete, [$idUsuario]);

    if (!$resultDelete) {
        throw new Exception('Error al ejecutar la consulta: ' . pg_last_error($conexion));
    }

    echo json_encode(['success' => true, 'message' => 'Funcionario eliminado con éxito.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
