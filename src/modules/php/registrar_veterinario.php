<?php
declare(strict_types=1);

/**
 * LÓGICA TESTEABLE
 * ----------------
 */
function registrar_veterinario(
    PDO $pdo,
    string $nombre,
    string $correo,
    string $password,
    int $idEspecialidad,
    int $rol
): array {

    $nombre        = trim($nombre);
    $correo        = trim($correo);
    $password      = trim($password);

    if (
        $nombre === '' ||
        $correo === '' ||
        $password === '' ||
        $idEspecialidad <= 0 ||
        $rol <= 0
    ) {
        throw new InvalidArgumentException('Todos los campos son obligatorios');
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Correo electrónico inválido');
    }

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    $pdo->beginTransaction();

    try {
        $fechaRegistro  = date('Y-m-d H:i:s');
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        if ($driver === 'pgsql') {
            $stmtDoctor = $pdo->prepare("
                INSERT INTO doctores (nombre, id_especialidad)
                VALUES (:nombre, :idEspecialidad)
                RETURNING id_doctores
            ");
            $stmtDoctor->execute([
                ':nombre'        => $nombre,
                ':idEspecialidad'=> $idEspecialidad,
            ]);
            $idDoctor = $stmtDoctor->fetchColumn();
            $stmtDoctor->closeCursor();
        } else {
            $stmtDoctor = $pdo->prepare("
                INSERT INTO doctores (nombre, id_especialidad)
                VALUES (:nombre, :idEspecialidad)
            ");
            $stmtDoctor->execute([
                ':nombre'        => $nombre,
                ':idEspecialidad'=> $idEspecialidad,
            ]);
            $idDoctor = $pdo->lastInsertId();
        }

        if (!$idDoctor) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo obtener el ID del doctor.');
        }

        if ($driver === 'pgsql') {
            $stmtUsuario = $pdo->prepare("
                INSERT INTO usuario (email, nombre, password, rol_id, id_doctores, fecha_registro)
                VALUES (:email, :nombre, :password, :rol, :idDoctor, :fecha_registro)
                RETURNING id_usuario
            ");
            $stmtUsuario->execute([
                ':email'          => $correo,
                ':nombre'         => $nombre,
                ':password'       => $hashedPassword,
                ':rol'            => $rol,
                ':idDoctor'       => $idDoctor,
                ':fecha_registro' => $fechaRegistro,
            ]);
            $idUsuario = $stmtUsuario->fetchColumn();
            $stmtUsuario->closeCursor();
        } else {
            $stmtUsuario = $pdo->prepare("
                INSERT INTO usuario (email, nombre, password, rol_id, id_doctores, fecha_registro)
                VALUES (:email, :nombre, :password, :rol, :idDoctor, :fecha_registro)
            ");
            $stmtUsuario->execute([
                ':email'          => $correo,
                ':nombre'         => $nombre,
                ':password'       => $hashedPassword,
                ':rol'            => $rol,
                ':idDoctor'       => $idDoctor,
                ':fecha_registro' => $fechaRegistro,
            ]);
            $idUsuario = $pdo->lastInsertId();
        }

        if (!$idUsuario) {
            $pdo->rollBack();
            throw new RuntimeException('No se pudo obtener el ID del usuario (veterinario).');
        }


        $stmtConfig = $pdo->query("
            SELECT id_configuracion
            FROM configuracion_passwords
            ORDER BY id_configuracion DESC
            LIMIT 1
        ");
        $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);
        $stmtConfig->closeCursor();

        if (!$config) {
            $pdo->rollBack();
            throw new RuntimeException('No se encontró configuración de contraseña');
        }
        $idConfig = (int)$config['id_configuracion'];

        $stmtHist = $pdo->prepare("
            INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
            VALUES (:id_usuario, :password, :fecha_creacion, :id_configuracion, :estado)
        ");
        $stmtHist->execute([
            ':id_usuario'      => $idUsuario,
            ':password'        => $hashedPassword,
            ':fecha_creacion'  => $fechaRegistro,
            ':id_configuracion'=> $idConfig,
            ':estado'          => true,
        ]);
        $stmtHist->closeCursor();

        $pdo->commit();

        return [
            "estado"          => "success",
            "mensaje"         => "Veterinario registrado exitosamente.",
            "id_usuario"      => (int)$idUsuario,
            "id_configuracion"=> $idConfig,
        ];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}


/**
 * ENDPOINT HTTP
 * -------------
 */
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');
    date_default_timezone_set('America/La_Paz');

    try {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        if (
            !isset($_POST['nombre'], $_POST['correo'], $_POST['password'],
                    $_POST['especialidad'], $_POST['rol'])
        ) {
            echo json_encode([
                "estado"  => "error",
                "mensaje" => "Faltan campos requeridos"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $nombre        = $_POST['nombre']        ?? '';
        $correo        = $_POST['correo']        ?? '';
        $password      = $_POST['password']      ?? '';
        $idEspecialidad= (int)($_POST['especialidad'] ?? 0);
        $rol           = (int)($_POST['rol']           ?? 0);

        $resp = registrar_veterinario(
            $pdo,
            $nombre,
            $correo,
            $password,
            $idEspecialidad,
            $rol
        );

        echo json_encode($resp, JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        echo json_encode([
            "estado"  => "error",
            "mensaje" => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
