<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function editar_mascota(
    PDO $pdo,
    int $id_mascota,
    string $nombre_mascota,
    string $fecha_nacimiento,
    string $tipo,
    string $raza,
    string $nombre_propietario,
    ?int $idUsuarioActual = null,
    ?string $nombreUsuarioActual = null
): array {
    
    // Validaciones
    if (
        $id_mascota <= 0 ||
        $nombre_mascota === '' ||
        $fecha_nacimiento === '' ||
        $tipo === '' ||
        $raza === '' ||
        $nombre_propietario === ''
    ) {
        throw new InvalidArgumentException("Todos los campos son obligatorios");
    }

    // Validar fecha
    $dt = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    if (!$dt || $dt->format('Y-m-d') !== $fecha_nacimiento) {
        throw new InvalidArgumentException("La fecha de nacimiento no tiene un formato válido (YYYY-MM-DD)");
    }

    // Buscar propietario
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1');
    $stmt->execute([':nombre' => $nombre_propietario]);
    $rowUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rowUsuario) {
        throw new RuntimeException("El propietario no existe");
    }
    $id_usuario_prop = (int)$rowUsuario['id_usuario'];

    // Consultar mascota actual
    $stmt = $pdo->prepare('SELECT * FROM mascota WHERE id_mascota = :id LIMIT 1');
    $stmt->execute([':id' => $id_mascota]);
    $mascota_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mascota_actual) {
        throw new RuntimeException("La mascota no existe");
    }

    // Comparar cambios
    $cambios = [];
    
    if ((string)$mascota_actual['nombre_mascota'] !== $nombre_mascota) {
        $cambios['nombre_mascota'] = [
            'antes' => (string)$mascota_actual['nombre_mascota'],
            'después' => $nombre_mascota
        ];
    }
    if ((string)$mascota_actual['fecha_nacimiento'] !== $fecha_nacimiento) {
        $cambios['fecha_nacimiento'] = [
            'antes' => (string)$mascota_actual['fecha_nacimiento'],
            'después' => $fecha_nacimiento
        ];
    }
    if ((string)$mascota_actual['tipo'] !== $tipo) {
        $cambios['tipo'] = [
            'antes' => (string)$mascota_actual['tipo'],
            'después' => $tipo
        ];
    }
    if ((string)$mascota_actual['raza'] !== $raza) {
        $cambios['raza'] = [
            'antes' => (string)$mascota_actual['raza'],
            'después' => $raza
        ];
    }
    if ((int)$mascota_actual['id_usuario'] !== $id_usuario_prop) {
        $cambios['id_usuario'] = [
            'antes' => (string)$mascota_actual['id_usuario'],
            'después' => (string)$id_usuario_prop
        ];
    }

    if (empty($cambios)) {
        return ["estado" => "success", "mensaje" => "No hubo cambios"];
    }

    // Actualizar mascota
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'UPDATE mascota
                SET nombre_mascota   = :nombre_mascota,
                    fecha_nacimiento = :fecha_nacimiento,
                    tipo             = :tipo,
                    raza             = :raza,
                    id_usuario       = :id_usuario
              WHERE id_mascota       = :id_mascota'
        );

        $stmt->execute([
            ':nombre_mascota'   => $nombre_mascota,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':tipo'             => $tipo,
            ':raza'             => $raza,
            ':id_usuario'       => $id_usuario_prop,
            ':id_mascota'       => $id_mascota,
        ]);

        // Registrar en log
        $camposModificados = implode(', ', array_keys($cambios));
        $detalles = [];
        foreach ($cambios as $campo => $v) {
            $antes = (string)$v['antes'];
            $desp  = (string)$v['después'];
            $detalles[] = "$campo: $antes -> $desp";
        }
        $valorOriginal = implode('; ', $detalles);

        registrarLogAplicacion(
            $pdo,
            $idUsuarioActual,
            $nombreUsuarioActual,
            'editar_mascota',
            "Se editó la mascota con ID $id_mascota",
            'editar_mascota',
            $camposModificados,
            $valorOriginal
        );

        $pdo->commit();

        return ["estado" => "success", "mensaje" => "Mascota actualizada"];

    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function registrarLogAplicacion(
    PDO $pdo,
    $idUsuarioActual,
    $nombreUsuarioActual,
    string $accion,
    string $descripcion,
    string $funcionAfectada,
    string $datoModificado,
    string $valorOriginal
): void {
    // Usar datetime compatible con SQLite
    $now = (new DateTimeImmutable('now', new DateTimeZone('America/La_Paz')))
        ->format('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO log_aplicacion
               (id_usuario, nombre_usuario, accion, descripcion, funcion_afectada, dato_modificado, valor_original, fecha_hora)
             VALUES
               (:id_usuario, :nombre_usuario, :accion, :descripcion, :funcion_afectada, :dato_modificado, :valor_original, :fecha_hora)'
        );
        $stmt->execute([
            ':id_usuario'       => $idUsuarioActual,
            ':nombre_usuario'   => $nombreUsuarioActual,
            ':accion'           => $accion,
            ':descripcion'      => $descripcion,
            ':funcion_afectada' => $funcionAfectada,
            ':dato_modificado'  => $datoModificado,
            ':valor_original'   => $valorOriginal,
            ':fecha_hora'       => $now,
        ]);
    } catch (Throwable $e) {
        error_log('registrarLogAplicacion error: ' . $e->getMessage());
    }
}

/**
 * ENDPOINT HTTP
 * -------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=UTF-8');
    session_start();

    try {
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $id_mascota         = isset($_POST['id_mascota']) ? (int)$_POST['id_mascota'] : 0;
        $nombre_mascota     = trim((string)($_POST['nombre_mascota'] ?? ''));
        $fecha_nacimiento   = trim((string)($_POST['fecha_nacimiento'] ?? ''));
        $tipo               = trim((string)($_POST['tipo'] ?? ''));
        $raza               = trim((string)($_POST['raza'] ?? ''));
        $nombre_propietario = trim((string)($_POST['nombre_propietario'] ?? ''));

        $idUsuarioActual     = $_SESSION['id_usuario']     ?? null;
        $nombreUsuarioActual = $_SESSION['nombre_usuario'] ?? null;

        $respuesta = editar_mascota(
            $pdo,
            $id_mascota,
            $nombre_mascota,
            $fecha_nacimiento,
            $tipo,
            $raza,
            $nombre_propietario,
            $idUsuarioActual,
            $nombreUsuarioActual
        );

        echo json_encode($respuesta);

    } catch (InvalidArgumentException $e) {
        echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
    } catch (RuntimeException $e) {
        echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
    } catch (Throwable $e) {
        error_log('editar_mascota.php error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
    }
}