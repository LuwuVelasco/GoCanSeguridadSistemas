<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function crear_rol(
    PDO $pdo,
    ?string $nombreRol,
    array $permisos = [],
    ?int $idUsuario = null,
    ?string $nombreUsuario = null
): array {

    if (!$nombreRol || trim($nombreRol) === '') {
        throw new InvalidArgumentException('Nombre de rol requerido');
    }

    // Obtener columnas permitidas
    $allowedCols = obtener_columnas_permisos($pdo);
    $allowed = array_flip($allowedCols);

    $pdo->beginTransaction();

    try {
        // Crear rol
        $stmt = $pdo->prepare("INSERT INTO roles_y_permisos (nombre_rol) VALUES (:nombre_rol)");
        $stmt->execute([':nombre_rol' => $nombreRol]);
        $idRol = (int)$pdo->lastInsertId();

        // Construir SET con permisos
        $setParts = [];
        $params = [':id_rol' => $idRol];
        
        foreach ($permisos as $p) {
            $col = $p['id_permiso'] ?? ($p['permiso'] ?? null);
            if (!$col || !isset($allowed[$col])) continue;
            
            $paramName = ":$col";
            $setParts[] = "$col = $paramName";
            $params[$paramName] = !empty($p['habilitado']) ? 1 : 0;
        }

        if ($setParts) {
            $sql = "UPDATE roles_y_permisos SET " . implode(', ', $setParts) . " WHERE id_rol = :id_rol";
            $upd = $pdo->prepare($sql);
            $upd->execute($params);
        }

        // Log de aplicación
        $datoModificado = $setParts ? implode(', ', array_map(
            fn($p) => explode('=', trim($p))[0],
            $setParts
        )) : '(sin permisos iniciales)';

        $valorOriginal = $setParts ? implode('; ', array_map(function($p) use ($params) {
            $parts = explode('=', $p);
            $col = trim($parts[0]);
            $paramName = trim($parts[1]);
            $after = $params[$paramName] ? 'true' : 'false';
            return "$col: false -> $after";
        }, $setParts)) : 'N/A';

        registrar_log_crear_rol(
            $pdo,
            $idUsuario,
            $nombreUsuario,
            $nombreRol,
            $idRol,
            $datoModificado,
            $valorOriginal
        );

        $pdo->commit();
        
        return [
            'success' => true,
            'id_rol' => $idRol
        ];

    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Obtiene las columnas de permisos de la tabla roles_y_permisos
 */
function obtener_columnas_permisos(PDO $pdo): array {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        // Para SQLite, usar PRAGMA
        $stmt = $pdo->query("PRAGMA table_info(roles_y_permisos)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $allowedCols = [];
        foreach ($columns as $col) {
            if (!in_array($col['name'], ['id_rol', 'nombre_rol'])) {
                $allowedCols[] = $col['name'];
            }
        }
        return $allowedCols;
    } else {
        // Para MySQL/PostgreSQL, usar information_schema
        $colsStmt = $pdo->prepare("
            SELECT column_name
            FROM information_schema.columns
            WHERE table_name = 'roles_y_permisos'
              AND column_name NOT IN ('id_rol','nombre_rol')
        ");
        $colsStmt->execute();
        return array_column($colsStmt->fetchAll(PDO::FETCH_ASSOC), 'column_name');
    }
}

/**
 * Registra el log de creación de rol
 */
function registrar_log_crear_rol(
    PDO $pdo,
    ?int $idUsuario,
    ?string $nombreUsuario,
    string $nombreRol,
    int $idRol,
    string $datoModificado,
    string $valorOriginal
): void {
    $now = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))
        ->format('Y-m-d H:i:s');

    $log = $pdo->prepare("
        INSERT INTO log_aplicacion
        (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
        VALUES (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, :fecha_hora)
    ");
    $log->execute([
        ':id_usuario'       => is_numeric($idUsuario) ? $idUsuario : null,
        ':nombre_usuario'   => $nombreUsuario,
        ':accion'           => 'crear_rol',
        ':descripcion'      => "Rol creado: {$nombreRol} (ID {$idRol})",
        ':funcion_afectada' => 'roles_y_permisos',
        ':dato_modificado'  => $datoModificado,
        ':valor_original'   => $valorOriginal,
        ':fecha_hora'       => $now,
    ]);
}

/**
 * ENDPOINT HTTP
 * -------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
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
    header('Content-Type: application/json; charset=UTF-8');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    require_once __DIR__ . '/conexion.php';
    session_start();
    date_default_timezone_set('America/La_Paz');

    try {
        $data = json_decode(file_get_contents("php://input"), true);
        $nombreRol = $data['nombre_rol'] ?? null;
        $permisos  = $data['permisos']    ?? [];

        $idUsuario     = $_SESSION['id_usuario']     ?? null;
        $nombreUsuario = $_SESSION['nombre_usuario'] ?? null;

        $resultado = crear_rol($pdo, $nombreRol, $permisos, $idUsuario, $nombreUsuario);
        
        echo json_encode($resultado);

    } catch (InvalidArgumentException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}