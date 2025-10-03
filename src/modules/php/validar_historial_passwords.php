<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Soporta preflight (por si lo llamas con fetch y OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Lee body JSON; si viene vacío, cae a POST tradicional
  $raw  = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) { $data = $_POST; }

  if (!isset($data['id_usuario'], $data['password'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit;
  }

  $id_usuario     = (int)$data['id_usuario'];
  $nueva_password = (string)$data['password'];

  // 1) Configuración más reciente (numero_historico)
  $stmt = $pdo->query("SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
  $row  = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseñas"]);
    exit;
  }

  $numero_historico = (int)$row['numero_historico'];

  // 2) Últimas N contraseñas del usuario
  $sqlH = "
    SELECT password
    FROM historial_passwords
    WHERE id_usuario = :id_usuario
    ORDER BY fecha_creacion DESC
    LIMIT :lim
  ";
  $stmtH = $pdo->prepare($sqlH);
  $stmtH->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
  $stmtH->bindValue(':lim', $numero_historico, PDO::PARAM_INT);
  $stmtH->execute();

  $hashes = $stmtH->fetchAll(PDO::FETCH_COLUMN, 0);

  foreach ($hashes as $hashAnterior) {
    if (is_string($hashAnterior) && password_verify($nueva_password, $hashAnterior)) {
      echo json_encode([
        "estado"  => "error",
        "mensaje" => "La nueva contraseña no puede ser igual a las últimas $numero_historico contraseñas"
      ]);
      exit;
    }
  }

  echo json_encode(["estado" => "success", "mensaje" => "La nueva contraseña cumple con los criterios de historial"]);
} catch (Throwable $e) {
  error_log('validar-historial-passwords error: ' . $e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error al obtener configuración de contraseñas"]);
}
