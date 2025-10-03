<?php
// C:\xampp\htdocs\GoCanSeguridadSistemas\src\modules\php\verificar_email.php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

$allowed = [
  'http://localhost', 'http://localhost:5500',
  'http://127.0.0.1', 'http://127.0.0.1:5500'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['estado' => 'error', 'mensaje' => 'Método no permitido']);
  exit;
}

// Conexión (retorna $pdo)
$pdo = require __DIR__ . '/conexion.php';

// Acepta x-www-form-urlencoded o JSON
$input = $_POST;
if (!$input) {
  $raw = file_get_contents('php://input');
  if ($raw) $input = json_decode($raw, true) ?: [];
}

$emailRaw = $input['email'] ?? '';
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
if (!$email) {
  echo json_encode(['estado' => 'error', 'mensaje' => 'Email inválido']);
  exit;
}

try {
  $stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total
     FROM usuario
     WHERE LOWER(email) = LOWER(:email)'
  );
  $stmt->execute([':email' => $email]);
  $total = (int)$stmt->fetchColumn();

  if ($total > 0) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'El correo ya está registrado']);
  } else {
    echo json_encode(['estado' => 'success', 'mensaje' => 'El correo está disponible']);
  }
} catch (Throwable $e) {
  error_log('verificar_email error: '.$e->getMessage());
  echo json_encode(['estado' => 'error', 'mensaje' => 'Error en la consulta']);
}
