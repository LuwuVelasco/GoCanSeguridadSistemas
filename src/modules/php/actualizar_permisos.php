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

if (!isset($data['permisos']) || !isset($data['id_rol'])) {
    echo json_encode(['error' => true, 'message' => 'Datos incompletos']);
    exit;
}

$idRol = (int)$data['id_rol'];
$permisosInput = $data['permisos'];

try {
    // 1) Obtener lista de columnas válidas para evitar inyección
    $colsStmt = $pdo->prepare("
        SELECT column_name
        FROM information_schema.columns
        WHERE table_name = 'roles_y_permisos'
          AND column_name NOT IN ('id_rol','nombre_rol')
    ");
    $colsStmt->execute();
    $allowedCols = array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'column_name');
    $allowed = array_flip($allowedCols);

    // 2) Estado previo (para el log)
    $prevStmt = $pdo->prepare("SELECT * FROM roles_y_permisos WHERE id_rol = :id_rol");
    $prevStmt->execute([':id_rol' => $idRol]);
    $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);
    if (!$prev) {
        echo json_encode(['error' => true, 'message' => 'Rol no encontrado']);
        exit;
    }

    // 3) Construir SET seguro
    $setParts = [];
    $params   = [':id_rol' => $idRol];
    $touched  = []; // para log

    foreach ($permisosInput as $p) {
        $col = $p['permiso'] ?? ($p['id_permiso'] ?? null);
        if (!$col || !isset($allowed[$col])) continue;

        $param = ':col_' . $col;
        $value = !empty($p['habilitado']) ? 'true' : 'false';
        $setParts[] = "$col = $value";
        $touched[$col] = $value === 'true';
    }

    if (!$setParts) {
        echo json_encode(['success' => true, 'message' => 'Sin cambios válidos.']);
        exit;
    }

    $sql = "UPDATE roles_y_permisos SET " . implode(', ', $setParts) . " WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([':id_rol' => $idRol]);

    if (!$ok) {
        echo json_encode(['error' => true, 'message' => 'No se pudo actualizar los permisos']);
        exit;
    }

    // 4) Log de aplicación: dif de cambios
    $changes = [];
    foreach ($touched as $col => $newBool) {
        $before = ($prev[$col] === true || $prev[$col] === 't' || $prev[$col] === 1) ? 'true' : 'false';
        $after  = $newBool ? 'true' : 'false';
        if ($before !== $after) {
            $changes[$col] = "$before -> $after";
        }
    }

    if ($changes) {
        $datoModificado = implode(', ', array_keys($changes));
        $valorOriginal  = implode('; ', array_map(fn($k, $v) => "$k: $v", array_keys($changes), $changes));

        $idUsuario     = $_SESSION['id_usuario']     ?? null;
        $nombreUsuario = $_SESSION['nombre_usuario'] ?? null;

        $log = $pdo->prepare("
            INSERT INTO log_aplicacion
            (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
            VALUES (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, NOW())
        ");
        $log->execute([
            ':id_usuario'       => is_numeric($idUsuario) ? $idUsuario : null,
            ':nombre_usuario'   => $nombreUsuario,
            ':accion'           => 'actualizar_permisos_rol',
            ':descripcion'      => "Actualización de permisos del rol {$idRol}",
            ':funcion_afectada' => 'roles_y_permisos',
            ':dato_modificado'  => $datoModificado,
            ':valor_original'   => $valorOriginal
        ]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
