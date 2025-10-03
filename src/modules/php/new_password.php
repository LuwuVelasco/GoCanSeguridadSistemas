<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

/* ---- CORS (útil en desarrollo con Live Server) ---- */
$allowed_origins = [
  'http://localhost', 'http://127.0.0.1',
  'http://localhost:5500', 'http://127.0.0.1:5500'
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['estado'=>'error','mensaje'=>'Método no permitido']); exit; }

/* ---- Zona horaria PHP ---- */
date_default_timezone_set('America/La_Paz');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Alinear la sesión de BD a La Paz (opcional)
  $pdo->exec("SET TIME ZONE 'America/La_Paz'");

  // Aceptar JSON o x-www-form-urlencoded
  $raw  = file_get_contents('php://input');
  $json = json_decode($raw, true);
  $email = (string)($json['email'] ?? ($_POST['email'] ?? ''));
  $newPassword = (string)($json['new_password'] ?? ($_POST['new_password'] ?? ''));

  if ($email === '' || $newPassword === '') {
    echo json_encode(['estado'=>'error','mensaje'=>'Email o contraseña no pueden estar vacíos']); exit;
  }
  $emailValidated = filter_var($email, FILTER_VALIDATE_EMAIL);
  if (!$emailValidated) { echo json_encode(['estado'=>'error','mensaje'=>'Formato de email inválido']); exit; }

  // Hash de la nueva contraseña
  $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
  if ($hashedPassword === false) {
    echo json_encode(['estado'=>'error','mensaje'=>'Error al hashear la contraseña']); exit;
  }

  $pdo->beginTransaction();

  // Obtener usuario
  $stmtUser = $pdo->prepare("SELECT id_usuario FROM usuario WHERE LOWER(email)=LOWER(:email) LIMIT 1");
  $stmtUser->execute([':email' => $emailValidated]);
  $id_usuario = (int)($stmtUser->fetchColumn() ?: 0);
  if ($id_usuario <= 0) {
    $pdo->rollBack();
    echo json_encode(['estado'=>'error','mensaje'=>'Usuario no encontrado']); exit;
  }

  // Obtener configuración de historial (N últimas)
  $numero_historico = 0;
  $stmtCfg = $pdo->query("SELECT numero_historico FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
  $rowCfg = $stmtCfg->fetch(PDO::FETCH_ASSOC);
  if ($rowCfg) $numero_historico = max(0, (int)$rowCfg['numero_historico']);

  // Validar que la nueva contraseña NO esté en las últimas N
  if ($numero_historico > 0) {
    $stmtHist = $pdo->prepare(
      "SELECT password
         FROM historial_passwords
        WHERE id_usuario = :id
        ORDER BY fecha_creacion DESC
        LIMIT :lim"
    );
    $stmtHist->bindValue(':id', $id_usuario, PDO::PARAM_INT);
    $stmtHist->bindValue(':lim', $numero_historico, PDO::PARAM_INT);
    $stmtHist->execute();
    $prevs = $stmtHist->fetchAll(PDO::FETCH_COLUMN, 0);

    foreach ($prevs as $prevPass) {
      $prevPass = (string)$prevPass;
      $isHash = password_get_info($prevPass)['algo'] !== 0;
      $match  = $isHash ? password_verify($newPassword, $prevPass)
                        : hash_equals($prevPass, $newPassword);
      if ($match) {
        $pdo->rollBack();
        echo json_encode([
          'estado'  => 'error',
          'mensaje' => "La nueva contraseña no puede ser igual a las últimas $numero_historico contraseñas"
        ]);
        exit;
      }
    }
  }

  // Desactivar historial vigente (estado=true) del usuario
  $stmtOff = $pdo->prepare("UPDATE historial_passwords SET estado = false WHERE id_usuario = :id AND estado = true");
  $stmtOff->execute([':id' => $id_usuario]);

  // Obtener id_configuracion actual para guardar en historial
  $stmtCfgId = $pdo->query("SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
  $id_configuracion = (int)($stmtCfgId->fetchColumn() ?: 0);
  if ($id_configuracion <= 0) {
    $pdo->rollBack();
    echo json_encode(['estado'=>'error','mensaje'=>'No se encontró configuración de contraseña']); exit;
  }

  // Insertar nueva contraseña en historial (guardamos el HASH)
  $stmtIns = $pdo->prepare(
    "INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
     VALUES (:id, :pwd, NOW(), :cfg, true)"
  );
  $stmtIns->execute([
    ':id'  => $id_usuario,
    ':pwd' => $hashedPassword,
    ':cfg' => $id_configuracion
  ]);

  // Mantener solo las últimas N en historial (si N > 0)
  if ($numero_historico > 0) {
    $stmtDel = $pdo->prepare(
      "DELETE FROM historial_passwords
        WHERE id_usuario = :id
          AND id_password IN (
            SELECT id_password
              FROM historial_passwords
             WHERE id_usuario = :id
             ORDER BY fecha_creacion DESC
             OFFSET :keep
          )"
    );
    $stmtDel->bindValue(':id', $id_usuario, PDO::PARAM_INT);
    $stmtDel->bindValue(':keep', $numero_historico, PDO::PARAM_INT);
    $stmtDel->execute();
  }

  // Actualizar la contraseña del usuario
  $stmtUpd = $pdo->prepare("UPDATE usuario SET password = :pwd WHERE id_usuario = :id");
  $stmtUpd->execute([':pwd' => $hashedPassword, ':id' => $id_usuario]);

  $pdo->commit();

  echo json_encode(['estado'=>'success','mensaje'=>'Contraseña actualizada correctamente']);

} catch (Throwable $e) {
  if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
  error_log('new_password.php error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['estado'=>'error','mensaje'=>'Error al actualizar la contraseña']);
}
