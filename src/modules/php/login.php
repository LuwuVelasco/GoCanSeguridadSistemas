<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=UTF-8');

/* ===== Seguridad + CORS (solo local) ===== */
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

$allowed_origins = [
  'http://127.0.0.1:5500', // Live Server
  'http://localhost:5500', // Live Server variante
  'http://localhost',      // Apache local
  'http://127.0.0.1',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
  header('Access-Control-Allow-Credentials: true'); // cookie de sesión
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['estado'=>'error','mensaje'=>'Método no permitido']); exit; }

/* ===== Conexión (PDO) ===== */
$pdo = require __DIR__ . '/conexion.php';
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ===== Input ===== */
$emailRaw = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$captcha  = $_POST['g-recaptcha-response'] ?? '';

putenv("RECAPTCHA_BYPASS_LOCAL=1");

if ($emailRaw === '' || $password === '') {
  echo json_encode(['estado'=>'error','mensaje'=>'El email y la contraseña son obligatorios']); exit;
}
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
if (!$email) { echo json_encode(['estado'=>'error','mensaje'=>'Formato de email inválido']); exit; }

/* ===== reCAPTCHA ===== */
// Para desarrollo, puedes saltarlo exportando RECAPTCHA_BYPASS_LOCAL=1
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true);
$bypassLocal = getenv('RECAPTCHA_BYPASS_LOCAL') === '1';

if (!($isLocal && $bypassLocal)) {
  $secret = getenv('RECAPTCHA_SECRET') ?: '6Ldn970qAAAAANB2ogY4Ml1jVCvjt203gjG0jamr'; // tu secret local
  if (!$captcha || !$secret) {
    registrarLog($pdo, null, null, 'captcha_fallido', 'Captcha no enviado o sin secret');
    echo json_encode(['estado'=>'error','mensaje'=>'captcha_fallido']); exit;
  }
  $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['secret'=>$secret,'response'=>$captcha,'remoteip'=>$_SERVER['REMOTE_ADDR'] ?? '']),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
  ]);
  $verify = curl_exec($ch);
  $err = curl_error($ch);
  curl_close($ch);
  $data = $verify ? json_decode($verify, true) : ['success'=>false];
  if (empty($data['success'])) {
    registrarLog($pdo, null, null, 'captcha_fallido', 'Captcha inválido' . ($err ? " ($err)" : ''));
    echo json_encode(['estado'=>'error','mensaje'=>'captcha_fallido']); exit;
  }
}

/* ===== Autenticación ===== */
try {
  $stmt = $pdo->prepare(
    "SELECT u.id_usuario, u.id_doctores, u.password, u.nombre, u.rol_id, r.nombre_rol AS rol
     FROM usuario u
     INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
     WHERE LOWER(u.email) = LOWER(:email)
     LIMIT 1"
  );
  $stmt->execute([':email' => $email]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  $ok = false;
  if ($row) {
    $stored = (string)($row['password'] ?? '');
    if ($stored !== '') {
      $isHash = password_get_info($stored)['algo'] !== 0;
      $ok = $isHash ? password_verify($password, $stored) : hash_equals($stored, $password);
    }
  }

  if ($ok) {
    $_SESSION['id_usuario']     = (int)$row['id_usuario'];
    $_SESSION['nombre_usuario'] = (string)$row['nombre'];
    $_SESSION['rol_id']         = (int)$row['rol_id'];
    $_SESSION['rol']            = (string)$row['rol'];
    if (!empty($row['id_doctores'])) $_SESSION['id_doctores'] = (int)$row['id_doctores'];

    registrarLog($pdo, (int)$row['id_usuario'], (string)$row['nombre'], 'login_exitoso', 'Inicio de sesión exitoso');

    echo json_encode([
      'estado'      => 'success',
      'id_usuario'  => (int)$row['id_usuario'],
      'rol'         => (string)$row['rol'],
      'id_doctores' => $row['id_doctores'] ?? null,
    ]);
    exit;
  }

  registrarLog($pdo, null, null, 'login_fallido', 'Credenciales inválidas para ' . $email);
  echo json_encode(['estado'=>'error','mensaje'=>'El email o la contraseña son incorrectos']);
  exit;

} catch (Throwable $e) {
  error_log('Login error: '.$e->getMessage());
  echo json_encode(['estado'=>'error','mensaje'=>'Error en la consulta']); exit;
}

/* ===== Log en BD ===== */
function registrarLog(PDO $pdo, $idUsuario, $nombreUsuario, string $accion, string $descripcion): void {
  try {
    $stmt = $pdo->prepare(
      "INSERT INTO log_usuarios (id_usuario, nombre_usuario, accion, descripcion, fecha_hora)
       VALUES (:id_usuario, :nombre_usuario, :accion, :descripcion, NOW())"
    );
    $stmt->execute([
      ':id_usuario'     => $idUsuario,
      ':nombre_usuario' => $nombreUsuario,
      ':accion'         => $accion,
      ':descripcion'    => $descripcion,
    ]);
  } catch (Throwable $e) {
    error_log('No se pudo registrar log: '.$e->getMessage());
  }
}
