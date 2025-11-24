<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function registrar_mascota(
    PDO $pdo,
    string $nombre_mascota,
    string $fecha_nacimiento,
    string $tipo,
    string $raza,
    string $nombre_propietario
): array {

    // Normalizar / limpiar entradas
    $nombre_mascota     = trim($nombre_mascota);
    $fecha_nacimiento   = trim($fecha_nacimiento);
    $tipo               = trim($tipo);
    $raza               = trim($raza);
    $nombre_propietario = trim($nombre_propietario);

    // Validaciones básicas
    if (
        $nombre_mascota === '' ||
        $fecha_nacimiento === '' ||
        $tipo === '' ||
        $raza === '' ||
        $nombre_propietario === ''
    ) {
        throw new InvalidArgumentException('Todos los campos son obligatorios');
    }

    // Validar fecha simple: YYYY-MM-DD
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
        throw new InvalidArgumentException('Formato de fecha inválido (YYYY-MM-DD)');
    }

    // Buscar propietario por NOMBRE (no por email)
    $stmtUser = $pdo->prepare(
        "SELECT id_usuario 
           FROM usuario 
          WHERE nombre = :nombre 
          LIMIT 1"
    );
    $stmtUser->execute([':nombre' => $nombre_propietario]);
    $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$rowUser) {
        throw new RuntimeException('El propietario no existe');
    }
    $id_usuario = (int)$rowUser['id_usuario'];

    // Insertar mascota y devolver id_mascota
    $stmtIns = $pdo->prepare(
        "INSERT INTO mascota (nombre_mascota, fecha_nacimiento, tipo, raza, id_usuario)
         VALUES (:nombre_mascota, :fecha_nacimiento, :tipo, :raza, :id_usuario)
         RETURNING id_mascota"
    );
    $stmtIns->execute([
        ':nombre_mascota'   => $nombre_mascota,
        ':fecha_nacimiento' => $fecha_nacimiento,
        ':tipo'             => $tipo,
        ':raza'             => $raza,
        ':id_usuario'       => $id_usuario,
    ]);

    $newId = $stmtIns->fetchColumn();
    if ($newId === false) {
        throw new RuntimeException('No se pudo registrar la mascota');
    }

    return [
        "estado"    => "success",
        "mensaje"   => "Mascota registrada exitosamente",
        "id_mascota"=> (int)$newId,
    ];
}

/**
 * ENDPOINT HTTP
 * -------------
 * Solo se ejecuta cuando se llama directamente por HTTP,
 * NO cuando se incluye desde PHPUnit (CLI).
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');

    try {
        /** Conexión PDO real */
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // Leer POST
        $nombre_mascota     = $_POST['nombre_mascota']    ?? '';
        $fecha_nacimiento   = $_POST['fecha_nacimiento']  ?? '';
        $tipo               = $_POST['tipo']              ?? '';
        $raza               = $_POST['raza']              ?? '';
        $nombre_propietario = $_POST['nombre_propietario']?? '';

        $resp = registrar_mascota(
            $pdo,
            $nombre_mascota,
            $fecha_nacimiento,
            $tipo,
            $raza,
            $nombre_propietario
        );

        echo json_encode($resp, JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        // Errores de validación / negocio
        echo json_encode([
            "estado"  => "error",
            "mensaje" => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
