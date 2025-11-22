<?php
declare(strict_types=1);

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

    $now = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))
        ->format('Y-m-d H:i:s');

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
            ':tvu'                => $tiempoVidaUtil,
            ':nh'                 => $numeroHistorico,
            ':fecha_configuracion'=> $now,
        ]);
    } else {
        $sqlInsert = "INSERT INTO configuracion_passwords
                        (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
                      VALUES (1, :tvu, :nh, :fecha_configuracion)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([
            ':tvu'                => $tiempoVidaUtil,
            ':nh'                 => $numeroHistorico,
            ':fecha_configuracion'=> $now,
        ]);
    }

    // Log aplicación
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
