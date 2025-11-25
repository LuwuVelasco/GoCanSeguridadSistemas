<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start();
date_default_timezone_set('America/La_Paz');

if (!isset($_GET['id_rol'])) {
    echo json_encode(['error' => true, 'message' => 'ID de rol no proporcionado']);
    exit;
}

$idRol = (int)$_GET['id_rol'];

try {
    // Verificar si hay usuarios asignados
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) AS count FROM usuario WHERE rol_id = :id_rol");
    $stmtCheck->execute([':id_rol' => $idRol]);
    $userCount = (int)$stmtCheck->fetch(PDO::FETCH_ASSOC)['count'];

    if ($userCount > 0) {
        echo json_encode(['error' => true, 'message' => 'No se puede eliminar el rol porque hay usuarios asignados a él']);
        exit;
    }

    // Snapshot previo para log
    $prev = $pdo->prepare("SELECT * FROM roles_y_permisos WHERE id_rol = :id_rol");
    $prev->execute([':id_rol' => $idRol]);
    $row = $prev->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['error' => true, 'message' => 'Rol no encontrado']);
        exit;
    }

    $pdo->beginTransaction();

    // Eliminar
    $del = $pdo->prepare("DELETE FROM roles_y_permisos WHERE id_rol = :id_rol");
    $ok = $del->execute([':id_rol' => $idRol]);

    if (!$ok) {
        $pdo->rollBack();
        echo json_encode(['error' => true, 'message' => 'No se pudo eliminar el rol']);
        exit;
    }

    // Log aplicación
    $idUsuario     = $_SESSION['id_usuario']     ?? null;
    $nombreUsuario = $_SESSION['nombre_usuario'] ?? null;

    $nombreRol = $row['nombre_rol'] ?? '(sin nombre)';
    $perms = $row; unset($perms['id_rol'], $perms['nombre_rol']);

    $datoModificado = 'todos los permisos del rol';
    $valorOriginal  = 'nombre_rol: ' . $nombreRol;

    $log = $pdo->prepare("
        INSERT INTO log_aplicacion
        (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
        VALUES (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, NOW())
    ");
    $log->execute([
        ':id_usuario'       => is_numeric($idUsuario) ? $idUsuario : null,
        ':nombre_usuario'   => $nombreUsuario,
        ':accion'           => 'eliminar_rol',
        ':descripcion'      => "Rol eliminado: {$nombreRol} (ID {$idRol})",
        ':funcion_afectada' => 'roles_y_permisos',
        ':dato_modificado'  => $datoModificado,
        ':valor_original'   => $valorOriginal
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
