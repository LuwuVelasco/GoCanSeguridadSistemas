<?php
declare(strict_types=1);
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

$pdo = require __DIR__ . '/conexion.php'; // Debe devolver un PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

try {
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // ?especialidad_id=#
    if (isset($_GET['especialidad_id']) && is_numeric($_GET['especialidad_id'])) {
      $especialidadId = (int) $_GET['especialidad_id'];

      $stmt = $pdo->prepare(
        'SELECT id_doctores, nombre
           FROM doctores
          WHERE id_especialidad = :id
          ORDER BY nombre'
      );
      $stmt->execute([':id' => $especialidadId]);
      $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

      echo json_encode($doctores ?: []);
      exit;
    }

    // Todas las especialidades
    $stmt = $pdo->query(
      'SELECT id_especialidad, nombre_especialidad
         FROM especialidad
         ORDER BY nombre_especialidad'
    );
    $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($especialidades ?: []);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Acepta JSON o x-www-form-urlencoded
    $data = null;
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
      $raw = file_get_contents('php://input');
      $data = json_decode($raw, true);
    } else {
      // Normalizar a arreglo similar a JSON esperado por el front
      $data = [
        'propietario'        => $_POST['propietario']        ?? null,
        'especialidadNombre' => $_POST['especialidadNombre'] ?? null,
        'doctor'             => $_POST['doctor']             ?? null,
        'id_usuario'         => $_POST['id_usuario']         ?? null,
        'fecha'              => $_POST['fecha']              ?? null,
        'horario'            => $_POST['horario']            ?? null,
      ];
    }

    // Validaciones básicas
    $propietario        = trim((string)($data['propietario'] ?? ''));
    $especialidadNombre = trim((string)($data['especialidadNombre'] ?? ''));
    $doctorNombre       = trim((string)($data['doctor'] ?? ''));
    $idUsuario          = filter_var($data['id_usuario'] ?? null, FILTER_VALIDATE_INT);
    $fecha              = trim((string)($data['fecha'] ?? ''));   // formato YYYY-MM-DD
    $hora               = trim((string)($data['horario'] ?? '')); // ej. 10:15:00-04 o 10:15:00

    if ($propietario === '' || $especialidadNombre === '' || $doctorNombre === '' ||
        !$idUsuario || $fecha === '' || $hora === '') {
      echo json_encode(["error" => true, "mensaje" => "Faltan campos requeridos."]);
      exit;
    }

    // 1) Obtener ID del doctor por nombre
    $stmt = $pdo->prepare('SELECT id_doctores FROM doctores WHERE nombre = :nom LIMIT 1');
    $stmt->execute([':nom' => $doctorNombre]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
      echo json_encode(["error" => true, "mensaje" => "Doctor no encontrado."]);
      exit;
    }
    $idDoctor = (int)$doc['id_doctores'];

    // 2) Verificar choque de horario
    $stmt = $pdo->prepare(
      'SELECT COUNT(*)::int AS count
         FROM cita
        WHERE id_doctor = :id_doctor
          AND fecha = :fecha
          AND horario = :horario'
    );
    $stmt->execute([
      ':id_doctor' => $idDoctor,
      ':fecha'     => $fecha,
      ':horario'   => $hora
    ]);
    $existing = (int)$stmt->fetchColumn();

    if ($existing > 0) {
      echo json_encode([
        "error"   => true,
        "mensaje" => "El doctor ya tiene una cita en ese horario."
      ]);
      exit;
    }

    // 3) Insertar cita
    $stmt = $pdo->prepare(
      'INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario)
       VALUES (:propietario, :servicio, :doctor, :id_usuario, :id_doctor, :fecha, :horario)
       RETURNING id_cita'
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

    $idCita = $stmt->fetchColumn();
    if ($idCita) {
      echo json_encode([
        "id_cita" => (int)$idCita,
        "mensaje" => "Cita registrada con éxito."
      ]);
    } else {
      echo json_encode([
        "error"   => true,
        "mensaje" => "No se pudo registrar la cita."
      ]);
    }
    exit;
  }

  // Método no permitido
  http_response_code(405);
  echo json_encode(["error" => true, "mensaje" => "Método de solicitud no soportado."]);
  exit;

} catch (Throwable $e) {
  error_log('citas.php: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(["error" => true, "mensaje" => "Error del servidor: " . $e->getMessage()]);
  exit;
}
