<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
session_start();

/* --- CORS opcional para Live Server --- */
$allowed_origins = [
  'http://localhost','http://localhost:5500',
  'http://127.0.0.1','http://127.0.0.1:5500'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
  header("Access-Control-Allow-Origin: $origin");
  header('Vary: Origin');
  header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

/* --- Solo POST --- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'message'=>'Método no permitido']);
  exit;
}

/* --- Verificar que el usuario logueado sea Administrador --- */
$rol    = $_SESSION['rol']    ?? null;   // "Administrador"
$rol_id = $_SESSION['rol_id'] ?? null;   // 1
if (!($rol === 'Administrador' || (int)$rol_id === 1)) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'No autorizado']);
  exit;
}

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Acepta JSON o form
  $raw = file_get_contents('php://input');
  $asJson = json_decode($raw, true);
  if (is_array($asJson)) {
    $idUsuario = (int)($asJson['id_usuario'] ?? 0);
  } else {
    $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
  }

  if ($idUsuario <= 0) {
    echo json_encode(['success'=>false,'message'=>'ID de usuario no proporcionado.']);
    exit;
  }

  // Opcional: impedir que un admin se elimine a sí mismo
  if (!empty($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] === $idUsuario) {
    echo json_encode(['success'=>false,'message'=>'No puedes eliminar tu propio usuario.']);
    exit;
  }

  // Consultar rol del usuario a eliminar
  $stmt = $pdo->prepare('SELECT rol_id FROM usuario WHERE id_usuario = :id');
  $stmt->execute([':id' => $idUsuario]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    echo json_encode(['success'=>false,'message'=>'El usuario no existe.']);
    exit;
  }

  // En tu lógica original: NO eliminar si es Cliente (rol_id = 3)
  if ((int)$user['rol_id'] === 3) {
    echo json_encode(['success'=>false,'message'=>'No puedes eliminar usuarios con el rol de Cliente.']);
    exit;
  }

  // Eliminar dentro de una transacción
  $pdo->beginTransaction();

  $del = $pdo->prepare('DELETE FROM usuario WHERE id_usuario = :id');
  $del->execute([':id' => $idUsuario]);

  if ($del->rowCount() < 1) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'No se encontró el usuario o ya fue eliminado.']);
    exit;
  }

  $pdo->commit();
  echo json_encode(['success'=>true,'message'=>'Funcionario eliminado con éxito.']);

} catch (PDOException $e) {
  // Manejo específico de violaciones de clave foránea (SQLSTATE 23503)
  if ($e->getCode() === '23503') {
    // Tiene registros relacionados (citas, mascotas, productos, etc.)
    http_response_code(409);
    echo json_encode([
      'success' => false,
      'message' => 'No se puede eliminar: el usuario tiene registros asociados (citas/mascotas/productos/logs).'
    ]);
    exit;
  }

  error_log('eliminar_funcionario.php PDO: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Error del servidor.']);
} catch (Throwable $t) {
  error_log('eliminar_funcionario.php: '.$t->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Error del servidor.']);
}