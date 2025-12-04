<?php
declare(strict_types=1);

require_once __DIR__ . '/registrar_log_aplicacion.php';

/**
 * LÓGICA TESTEABLE
 * ----------------
 *
 * Esta función:
 * - Valida campos de entrada.
 * - Inserta en:
 *      1) usuario
 *      2) historial_passwords (usando la última configuración)
 * - Registra log de aplicación.
 */
function registrar_funcionario(
    PDO $pdo,
    string $nombre,
    string $email,
    string $password,
    int $rol,
    ?int $idUsuarioActual = null,
    ?string $nombreUsuarioActual = null
): array {

    // Normalización
    $nombre   = trim($nombre);
    $email    = trim($email);
    $password = trim($password);

    // Validación básica
    if ($nombre === '' || $email === '' || $password === '' || $rol <= 0) {
        throw new InvalidArgumentException('Faltan campos requeridos');
    }

    // Validación de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Correo electrónico inválido');
    }

    $yaEnTransaccion = $pdo->inTransaction();
    if (!$yaEnTransaccion) {
        $pdo->beginTransaction();
    }

    try {
        $fechaRegistro = date('Y-m-d H:i:s');
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // 1) Insertar en usuario
        // Detectar driver para RETURNING vs lastInsertId
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'pgsql') {
            $sqlUsuario = "
                INSERT INTO usuario (email, nombre, password, rol_id, fecha_registro)
                VALUES (:email, :nombre, :password, :rol, :fecha_registro)
                RETURNING id_usuario
            ";
            $stmtUsuario = $pdo->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':email'          => $email,
                ':nombre'         => $nombre,
                ':password'       => $hashedPassword,
                ':rol'            => $rol,
                ':fecha_registro' => $fechaRegistro
            ]);
            $idUsuario = $stmtUsuario->fetchColumn();
        } else {
            $sqlUsuario = "
                INSERT INTO usuario (email, nombre, password, rol_id, fecha_registro)
                VALUES (:email, :nombre, :password, :rol, :fecha_registro)
            ";
            $stmtUsuario = $pdo->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':email'          => $email,
                ':nombre'         => $nombre,
                ':password'       => $hashedPassword,
                ':rol'            => $rol,
                ':fecha_registro' => $fechaRegistro
            ]);
            $idUsuario = $pdo->lastInsertId();
        }

        if (!$idUsuario) {
            throw new RuntimeException("No se pudo obtener el ID del usuario registrado.");
        }

        // 2) Obtener la configuración de contraseñas más reciente
        $sqlConfig = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
        $stmtConfig = $pdo->query($sqlConfig);
        $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            throw new RuntimeException("No se encontró configuración de contraseña");
        }
        $idConfiguracion = (int)$config['id_configuracion'];

        // 3) Insertar en historial_passwords
        $sqlHistorial = "
            INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
            VALUES (:id_usuario, :password, :fecha_creacion, :id_configuracion, :estado)
        ";
        $stmtHistorial = $pdo->prepare($sqlHistorial);
        $stmtHistorial->execute([
            ':id_usuario'       => $idUsuario,
            ':password'         => $hashedPassword,
            ':fecha_creacion'   => $fechaRegistro,
            ':id_configuracion' => $idConfiguracion,
            ':estado'           => true
        ]);

        // 4) Obtener nombre del rol para el log
        $sqlRolNombre = "SELECT nombre_rol FROM roles_y_permisos WHERE id_rol = :rol";
        $stmtRol = $pdo->prepare($sqlRolNombre);
        $stmtRol->execute([':rol' => $rol]);
        $rowRol = $stmtRol->fetch(PDO::FETCH_ASSOC);
        $nombreRolNuevoUsuario = $rowRol ? $rowRol['nombre_rol'] : 'Rol desconocido';

        // 5) Registrar Log (llamada directa)
        registrar_log_aplicacion(
            $pdo,
            $idUsuarioActual,
            $nombreUsuarioActual,
            'registro_usuario',
            "Se registró un nuevo usuario con ID $idUsuario y rol $nombreRolNuevoUsuario",
            'registrar_funcionario',
            'usuario',
            "Registro de $nombreRolNuevoUsuario"
        );

        if (!$yaEnTransaccion && $pdo->inTransaction()) {
            $pdo->commit();
        }

        return [
            "estado"           => "success",
            "mensaje"          => "Funcionario registrado exitosamente.",
            "id_usuario"       => (int)$idUsuario,
            "id_configuracion" => $idConfiguracion
        ];

    } catch (Throwable $e) {
        if (!$yaEnTransaccion && $pdo->inTransaction()) {
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
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type');
    
    session_start();
    date_default_timezone_set('America/La_Paz');
    
    try {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!isset($_POST['nombre'], $_POST['correo'], $_POST['password'], $_POST['rol'])) {
            echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
            exit();
        }

        $idUsuarioActual     = $_SESSION['id_usuario'] ?? null;
        $nombreUsuarioActual = $_SESSION['nombre_usuario'] ?? null;

        // Convertir a int si es necesario, aunque el POST suele ser string
        $rol = (int)$_POST['rol'];

        $resp = registrar_funcionario(
            $pdo,
            $_POST['nombre'],
            $_POST['correo'],
            $_POST['password'],
            $rol,
            is_numeric($idUsuarioActual) ? (int)$idUsuarioActual : null,
            $nombreUsuarioActual
        );

        echo json_encode($resp);

    } catch (Throwable $e) {
        echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
    }
}
