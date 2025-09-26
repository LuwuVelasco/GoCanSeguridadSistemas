<?php
session_start();
header('Content-Type: application/json');

// Cabeceras de seguridad
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');

// CORS solo si hace falta (si front y back están en el mismo dominio, puedes eliminar esta línea)
header('Access-Control-Allow-Origin: https://gocanseguridadsistemas.onrender.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Conexión a BD (retorna $pdo)
$pdo = include 'conexion.php';

// 1) Validaciones básicas
if (empty($_POST['email']) || empty($_POST['password'])) {
  echo json_encode(["estado"=>"error","mensaje"=>"El email y la contraseña son obligatorios"]);
  exit;
}

// 2) Verificación reCAPTCHA server-side
$secret = getenv('RECAPTCHA_SECRET');
$captchaResponse = $_POST['g-recaptcha-response'] ?? '';

if (!$secret || !$captchaResponse) {
  registrarLog($pdo, null, null, 'captcha_fallido', 'Captcha no enviado o sin secret');
  echo json_encode(["estado"=>"error","mensaje"=>"captcha_fallido"]);
  exit;
}

$verify = @file_get_contents(
  'https://www.google.com/recaptcha/api/siteverify?secret='
  . urlencode($secret) . '&response=' . urlencode($captchaResponse)
  . '&remoteip=' . urlencode($_SERVER['REMOTE_ADDR'] ?? '')
);
$verifyData = $verify ? json_decode($verify, true) : ['success' => false];

if (empty($verifyData['success'])) {
  registrarLog($pdo, null, null, 'captcha_fallido', 'Captcha inválido');
  echo json_encode(["estado"=>"error","mensaje"=>"captcha_fallido"]);
  exit;
}

// 3) Sanitizar email
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
  echo json_encode(["estado"=>"error","mensaje"=>"Formato de email inválido"]);
  exit;
}
$password = $_POST['password'];

// 4) Ajustes PDO
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
  $sql = "SELECT u.id_usuario, u.id_doctores, u.password, u.nombre, u.rol_id, r.nombre_rol AS rol
          FROM usuario u
          INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
          WHERE u.email = :email";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    // Soportar tanto hash como (temporalmente) texto plano
    $stored = (string)$row['password'];
    $isHashed = password_get_info($stored)['algo'] !== 0; // true si es hash válido
    $ok = $isHashed ? password_verify($password, $stored) : hash_equals($stored, $password);

    if ($ok) {
      // Iniciar sesión
      $_SESSION['id_usuario'] = $row['id_usuario'];
      $_SESSION['nombre_usuario'] = $row['nombre'];
      $_SESSION['rol_id'] = $row['rol_id'];
      $_SESSION['rol'] = $row['rol'];
      if (!empty($row['id_doctores'])) {
        $_SESSION['id_doctores'] = $row['id_doctores'];
      }

      registrarLog($pdo, $row['id_usuario'], $row['nombre'], 'login_exitoso', 'Inicio de sesión exitoso');

      echo json_encode([
        "estado" => "success",
        "id_usuario" => $row['id_usuario'],
        "rol" => $row['rol'],
        "id_doctores" => $row['id_doctores'] ?? null
      ]);
      exit;
    }
  }

  // Credenciales inválidas
  registrarLog($pdo, null, null, 'login_fallido', 'Credenciales inválidas para ' . $email);
  echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
  exit;

} catch (PDOException $e) {
  error_log("Error de base de datos: " . $e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error en la consulta"]);
  exit;
}

/**
 * Registra en la tabla log_usuarios (sin llamadas HTTP a localhost)
 */
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
      ':descripcion'    => $descripcion
    ]);
  } catch (Throwable $e) {
    error_log('No se pudo registrar log: ' . $e->getMessage());
  }
}
