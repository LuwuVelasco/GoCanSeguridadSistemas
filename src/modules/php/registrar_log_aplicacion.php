<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function registrar_log_aplicacion(
    PDO $pdo,
    $idUsuario,
    ?string $nombreUsuario,
    string $accion,
    string $descripcion,
    string $funcionAfectada,
    string $datoModificado,
    string $valorOriginal
): array {

    if ($accion === '') {
        throw new InvalidArgumentException("La acción no puede estar vacía.");
    }

    if ($datoModificado === '') {
        throw new InvalidArgumentException("El dato modificado no puede estar vacío.");
    }

    $now = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))
        ->format('Y-m-d H:i:s');

    if (!is_numeric($idUsuario)) {
        $idUsuario = null;
    }

    $sql = "INSERT INTO log_aplicacion (
                id_usuario,
                nombre_usuario,
                accion,
                descripcion,
                funcion_afectada,
                dato_modificado,
                valor_original,
                fecha_hora
            )
            VALUES (
                :id_usuario,
                :nombre_usuario,
                :accion,
                :descripcion,
                :funcion_afectada,
                :dato_modificado,
                :valor_original,
                :fecha_hora
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':id_usuario'      => $idUsuario,
        ':nombre_usuario'  => $nombreUsuario,
        ':accion'          => $accion,
        ':descripcion'     => $descripcion,
        ':funcion_afectada'=> $funcionAfectada,
        ':dato_modificado' => $datoModificado,
        ':valor_original'  => $valorOriginal,
        ':fecha_hora'      => $now,
    ]);

    return ["estado" => "success"];
}


/**
 * ENDPOINT HTTP
 * ----------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');
    include 'conexion.php';
    date_default_timezone_set('America/La_Paz');

    try {
        $respuesta = registrar_log_aplicacion(
            $pdo,
            $_POST['id_usuario']       ?? null,
            $_POST['nombre_usuario']   ?? null,
            $_POST['accion']           ?? 'desconocido',
            $_POST['descripcion']      ?? '',
            $_POST['funcion_afectada'] ?? '',
            $_POST['dato_modificado']  ?? '',
            $_POST['valor_original']   ?? ''
        );

        echo json_encode($respuesta);

    } catch (Throwable $e) {
        echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
    }
}
