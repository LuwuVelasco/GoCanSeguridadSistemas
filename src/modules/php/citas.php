<?php
declare(strict_types=1);

/**
 * ============================================
 *     LÓGICA TESTEABLE
 * ============================================
 */

/**
 * Obtener doctores por especialidad 
 */
function obtener_doctores_por_especialidad(PDO $pdo, int $idEspecialidad): array
{
    $stmt = $pdo->prepare(
        "SELECT id_doctores, nombre
         FROM doctores
         WHERE id_especialidad = :id
         ORDER BY nombre"
    );
    $stmt->execute([':id' => $idEspecialidad]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Registrar cita 
 */
function registrar_cita(PDO $pdo, array $data): array
{
    $propietario        = trim((string)($data['propietario'] ?? ''));
    $especialidadNombre = trim((string)($data['especialidadNombre'] ?? ''));
    $doctorNombre       = trim((string)($data['doctor'] ?? ''));
    $idUsuario          = filter_var($data['id_usuario'] ?? null, FILTER_VALIDATE_INT);
    $fecha              = trim((string)($data['fecha'] ?? ''));
    $hora               = trim((string)($data['horario'] ?? ''));

    if (
        $propietario === '' ||
        $especialidadNombre === '' ||
        $doctorNombre === '' ||
        !$idUsuario ||
        $fecha === '' ||
        $hora === ''
    ) {
        throw new InvalidArgumentException("Faltan campos requeridos.");
    }

    // 1) Obtener ID del doctor
    $stmt = $pdo->prepare("SELECT id_doctores FROM doctores WHERE nombre = :nom LIMIT 1");
    $stmt->execute([':nom' => $doctorNombre]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        throw new RuntimeException("Doctor no encontrado.");
    }
    $idDoctor = (int)$doc['id_doctores'];

    // 2) Verificar choque horario
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) AS count
         FROM cita
         WHERE id_doctor = :id_doctor
           AND fecha = :fecha
           AND horario = :horario"
    );
    $stmt->execute([
        ':id_doctor' => $idDoctor,
        ':fecha'     => $fecha,
        ':horario'   => $hora
    ]);

    if ((int)$stmt->fetchColumn() > 0) {
        throw new RuntimeException("El doctor ya tiene una cita en ese horario.");
    }

    // 3) Insertar cita
    $stmt = $pdo->prepare(
        "INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario)
         VALUES (:propietario, :servicio, :doctor, :id_usuario, :id_doctor, :fecha, :horario)"
    );
    $stmt->execute([
        ':propietario' => $propietario,
        ':servicio'    => $especialidadNombre,
        ':doctor'      => $doctorNombre,
        ':id_usuario'  => $idUsuario,
        ':id_doctor'   => $idDoctor,
        ':fecha'       => $fecha,
        ':horario'     => $hora
    ]);

    return [
        "estado"  => "success",
        "mensaje" => "Cita registrada con éxito.",
        "id_cita" => (int)$pdo->lastInsertId()
    ];
}



/**
 * =====================================================
 *                 ENDPOINT HTTP 
 * =====================================================
 */

if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {

    header('Content-Type: application/json; charset=UTF-8');

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
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $pdo = require __DIR__ . '/conexion.php';
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    try {
        /**
         * ====================================
         *                GET
         * ====================================
         */
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {

            if (isset($_GET['especialidad_id']) && is_numeric($_GET['especialidad_id'])) {
                $id = (int)$_GET['especialidad_id'];
                echo json_encode(obtener_doctores_por_especialidad($pdo, $id));
                exit;
            }

            // Todas las especialidades
            $stmt = $pdo->query(
                "SELECT id_especialidad, nombre_especialidad
                 FROM especialidad
                 ORDER BY nombre_especialidad"
            );
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
            exit;
        }

        /**
         * ====================================
         *                POST
         * ====================================
         */

        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($raw, true);
        } else {
            $data = [
                'propietario'        => $_POST['propietario']        ?? null,
                'especialidadNombre' => $_POST['especialidadNombre'] ?? null,
                'doctor'             => $_POST['doctor']             ?? null,
                'id_usuario'         => $_POST['id_usuario']         ?? null,
                'fecha'              => $_POST['fecha']              ?? null,
                'horario'            => $_POST['horario']            ?? null
            ];
        }

        $resp = registrar_cita($pdo, $data);
        echo json_encode($resp);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "error" => true,
            "mensaje" => $e->getMessage()
        ]);
        exit;
    }
}
