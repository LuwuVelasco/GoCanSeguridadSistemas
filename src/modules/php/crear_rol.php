<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
  'http://127.0.0.1:5500',
  'http://localhost:5500'
];
if (in_array($origin, $allowed_origins)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
include 'conexion.php';
session_start();
date_default_timezone_set('America/La_Paz');

$data = json_decode(file_get_contents("php://input"), true);
$nombreRol = $data['nombre_rol'] ?? null;
$permisos  = $data['permisos']    ?? [];

if (!$nombreRol) {
    echo json_encode(['success' => false, 'message' => 'Nombre de rol requerido']);
    exit;
}

try {
    // Whitelist de columnas de permisos
    $colsStmt = $pdo->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_name = 'roles_y_permisos'
          AND column_name NOT IN ('id_rol','nombre_rol')
    ");
    $colsStmt->execute();
    $allowedCols = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'column_name');
    $allowed = array_flip($allowedCols);

    $pdo->beginTransaction();

    // Crear rol
    $stmt = $pdo->prepare("INSERT INTO roles_y_permisos (nombre_rol) VALUES (:nombre_rol) RETURNING id_rol");
    $stmt->execute([':nombre_rol' => $nombreRol]);
    $idRol = (int)$stmt->fetchColumn();

    // Construir SET con permisos
    $setParts = [];
    foreach ($permisos as $p) {
        $col = $p['id_permiso'] ?? ($p['permiso'] ?? null);
        if (!$col || !isset($allowed[$col])) continue;
        $setParts[] = "$col = " . (!empty($p['habilitado']) ? 'true' : 'false');
    }

    if ($setParts) {
        $upd = $pdo->prepare("UPDATE roles_y_permisos SET " . implode(', ', $setParts) . " WHERE id_rol = :id_rol");
        $upd->execute([':id_rol' => $idRol]);
    }

    // Log de aplicación
    $idUsuario     = $_SESSION['id_usuario']     ?? null;
    $nombreUsuario = $_SESSION['nombre_usuario'] ?? null;

    $datoModificado = $setParts ? implode(', ', array_map(
        fn($p) => explode('=', $p)[0],
        $setParts
    )) : '(sin permisos iniciales)';

    $valorOriginal = $setParts ? implode('; ', array_map(function($p){
        $col = trim(explode('=', $p)[0]);
        $after = stripos($p, 'true') !== false ? 'true' : 'false';
        return "$col: false -> $after";
    }, $setParts)) : 'N/A';

    $log = $pdo->prepare("
        INSERT INTO log_aplicacion
        (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
        VALUES (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, NOW())
    ");
    $log->execute([
        ':id_usuario'       => is_numeric($idUsuario) ? $idUsuario : null,
        ':nombre_usuario'   => $nombreUsuario,
        ':accion'           => 'crear_rol',
        ':descripcion'      => "Rol creado: {$nombreRol} (ID {$idRol})",
        ':funcion_afectada' => 'roles_y_permisos',
        ':dato_modificado'  => $datoModificado,
        ':valor_original'   => $valorOriginal
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
