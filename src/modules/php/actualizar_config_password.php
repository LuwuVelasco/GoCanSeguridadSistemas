<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function actualizar_config_password(
    PDO $pdo,
    ?int $tiempoVidaUtil,
    ?int $numeroHistorico,
    ?int $idUsuario = null,
    ?string $nombreUsuario = null
): array {

    if ($tiempoVidaUtil === null || $numeroHistorico === null) {
        throw new InvalidArgumentException(
            'Faltan datos obligatorios: tiempo de vida útil y número histórico.'
        );
    }

    if ($tiempoVidaUtil <= 0 || $numeroHistorico <= 0) {
        throw new InvalidArgumentException('Los valores deben ser mayores a 0.');
    }

    // Fecha/hora compatible con MySQL y SQLite (se pasa por parámetro)
    $now = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))
        ->format('Y-m-d H:i:s');

    // Leer valores previos
    $prev = $pdo->query("
        SELECT tiempo_vida_util, numero_historico
        FROM configuracion_passwords
        WHERE id_configuracion = 1
    ")->fetch(PDO::FETCH_ASSOC);

    if ($prev) {
        $sqlUpdate = "UPDATE configuracion_passwords
                      SET tiempo_vida_util = :tvu,
                          numero_historico = :nh,
                          fecha_configuracion = :fecha_configuracion
                      WHERE id_configuracion = 1";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([
            ':tvu'                 => $tiempoVidaUtil,
            ':nh'                  => $numeroHistorico,
            ':fecha_configuracion' => $now,
        ]);
    } else {
        $sqlInsert = "INSERT INTO configuracion_passwords
                        (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
                      VALUES (1, :tvu, :nh, :fecha_configuracion)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([
            ':tvu'                 => $tiempoVidaUtil,
            ':nh'                  => $numeroHistorico,
            ':fecha_configuracion' => $now,
        ]);
    }

    // Log de aplicación
    $datoModificado = 'tiempo_vida_util, numero_historico';
    $valorOriginal  = sprintf(
        'tiempo_vida_util: %s -> %s; numero_historico: %s -> %s',
        $prev['tiempo_vida_util']  ?? 'NULL', $tiempoVidaUtil,
        $prev['numero_historico']  ?? 'NULL', $numeroHistorico
    );

    $log = $pdo->prepare("
        INSERT INTO log_aplicacion
            (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
        VALUES
            (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, :fecha_hora)
    ");
    $log->execute([
        ':id_usuario'       => is_numeric($idUsuario) ? $idUsuario : null,
        ':nombre_usuario'   => $nombreUsuario,
        ':accion'           => 'configuracion_cambio',
        ':descripcion'      => 'Cambio en la configuración de contraseñas',
        ':funcion_afectada' => 'configuracion_passwords',
        ':dato_modificado'  => $datoModificado,
        ':valor_original'   => $valorOriginal,
        ':fecha_hora'       => $now,
    ]);

    return [
        "estado"  => "success",
        "mensaje" => "Configuración de contraseñas actualizada exitosamente."
    ];
}

/**
 * ENDPOINT HTTP
 * -------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed_origins = [
        'http://127.0.0.1:5500',
        'http://localhost:5500',
        'http://localhost', // XAMPP en http://localhost/GoCanSeguridadSistemas/...
    ];

    if (in_array($origin, $allowed_origins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }

    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: POST, OPTIONS');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    // conexión real (misma carpeta)
    require_once __DIR__ . '/conexion.php';

    session_start();
    date_default_timezone_set('America/La_Paz');

    try {
        // OJO: el frontend debe mandar tiempo_vida_util y numero_historico
        $tiempoVidaUtil  = isset($_POST['tiempo_vida_util']) ? (int)$_POST['tiempo_vida_util'] : null;
        $numeroHistorico = isset($_POST['numero_historico']) ? (int)$_POST['numero_historico'] : null;

        $idUsuario     = $_SESSION['id_usuario']     ?? null;
        $nombreUsuario = $_SESSION['nombre_usuario'] ?? null;

        $respuesta = actualizar_config_password(
            $pdo,
            $tiempoVidaUtil,
            $numeroHistorico,
            $idUsuario,
            $nombreUsuario
        );

        echo json_encode($respuesta);
    } catch (Throwable $e) {
        echo json_encode([
            "estado"  => "error",
            "mensaje" => "Error al actualizar la configuración: " . $e->getMessage()
        ]);
    }
}
