<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function validar_tiempo_vida_password(
    PDO $pdo,
    int $id_usuario,
    ?DateTimeImmutable $ahora = null
): array {

    // Validación básica del parámetro
    if ($id_usuario <= 0) {
        throw new InvalidArgumentException('id_usuario inválido');
    }

    // 1) Obtener configuración más reciente (tiempo_vida_util en días)
    $stmtCfg = $pdo->query("
        SELECT tiempo_vida_util
          FROM configuracion_passwords
         ORDER BY id_configuracion DESC
         LIMIT 1
    ");
    $cfg = $stmtCfg->fetch(PDO::FETCH_ASSOC);

    if (!$cfg) {
        throw new RuntimeException('No se encontró configuración de contraseñas');
    }

    $tiempo_vida_util = (int)$cfg['tiempo_vida_util'];

    // 2) Obtener la contraseña activa más reciente del usuario
    //    IMPORTANTE: estado es BOOLEAN en PostgreSQL → usamos IS TRUE
    $stmtHist = $pdo->prepare(
        "SELECT fecha_creacion
           FROM historial_passwords
          WHERE id_usuario = :id
            AND estado IS TRUE
          ORDER BY fecha_creacion DESC
          LIMIT 1"
    );
    $stmtHist->execute([':id' => $id_usuario]);
    $registro = $stmtHist->fetch(PDO::FETCH_ASSOC);

    if (!$registro || empty($registro['fecha_creacion'])) {
        throw new RuntimeException('No se encontró una contraseña activa para el usuario');
    }

    // Si no se pasó "ahora", usamos la hora actual de La Paz
    if ($ahora === null) {
        $ahora = new DateTimeImmutable('now', new DateTimeZone('America/La_Paz'));
    }

    // Fecha de creación de la contraseña
    $fecha_creacion   = new DateTimeImmutable(
        $registro['fecha_creacion'],
        new DateTimeZone('America/La_Paz')
    );

    // Fecha de expiración = fecha_creacion + tiempo_vida_util días
    $fecha_expiracion = $fecha_creacion->add(new DateInterval("P{$tiempo_vida_util}D"));

    // Comparación para ver si ya expiró
    if ($ahora > $fecha_expiracion) {
        return [
            "estado"  => "error",
            "mensaje" => "La contraseña ha expirado"
        ];
    }

    return [
        "estado"  => "success",
        "mensaje" => "La contraseña sigue siendo válida"
    ];
}

/**
 * ENDPOINT HTTP
 * -------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: POST, OPTIONS');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    date_default_timezone_set('America/La_Paz');

    try {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Acepta JSON o x-www-form-urlencoded
        $raw  = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) { $data = $_POST; }

        if (!isset($data['id_usuario'])) {
            echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id_usuario = (int)$data['id_usuario'];

        $resp = validar_tiempo_vida_password($pdo, $id_usuario);

        echo json_encode($resp, JSON_UNESCAPED_UNICODE);

    } catch (InvalidArgumentException|RuntimeException $e) {
        echo json_encode([
            "estado"  => "error",
            "mensaje" => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        error_log('validar_tiempo_vida_password error: ' . $e->getMessage());
        echo json_encode(["estado" => "error", "mensaje" => "Error del servidor"], JSON_UNESCAPED_UNICODE);
    }
}
