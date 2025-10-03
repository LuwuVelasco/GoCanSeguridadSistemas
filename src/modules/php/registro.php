<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
// CORS básico para desarrollo local (opcional)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Zona horaria app
date_default_timezone_set('America/La_Paz');

try {
  /** @var PDO $pdo */
  $pdo = require __DIR__ . '/conexion.php';
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

  // Zona horaria en la DB (por si usas NOW())
  try { $pdo->exec("SET TIME ZONE 'America/La_Paz'"); } catch (Throwable $e) {}

  // Soporta JSON (fetch con body JSON)
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (!is_array($data)) {
    echo json_encode(["estado" => "error", "mensaje" => "Formato de datos inválido"]);
    exit;
  }

  // Verificación previa (e.g., token captcha/email ya validado en front)
  if (empty($data['verified'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Token no verificado"]);
    exit;
  }

  // Campos requeridos
  $email   = trim((string)($data['email']   ?? ''));
  $nombre  = trim((string)($data['nombre']  ?? ''));
  $passRaw = (string)($data['password'] ?? '');

  if ($email === '' || $nombre === '' || $passRaw === '') {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit;
  }

  // Validar email
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["estado" => "error", "mensaje" => "Email inválido"]);
    exit;
  }

  // Hash de contraseña
  $hashedPassword = password_hash($passRaw, PASSWORD_BCRYPT);
  if ($hashedPassword === false) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo generar el hash de la contraseña"]);
    exit;
  }

  // Rol por defecto (Cliente)
  $rol_id = 3;

  // Comenzar transacción
  $pdo->beginTransaction();

  // (1) Verificar si el correo ya existe (case-insensitive)
  $stmt = $pdo->prepare("SELECT 1 FROM usuario WHERE LOWER(email) = LOWER(:email) LIMIT 1");
  $stmt->execute([':email' => $email]);
  if ($stmt->fetchColumn()) {
    $pdo->rollBack();
    echo json_encode(["estado" => "error", "mensaje" => "El correo ya está registrado"]);
    exit;
  }

  // (2) Insertar usuario
  $stmt = $pdo->prepare("
    INSERT INTO usuario (email, nombre, password, fecha_registro, rol_id)
    VALUES (:email, :nombre, :password, NOW(), :rol_id)
    RETURNING id_usuario
  ");
  $stmt->execute([
    ':email'    => $email,
    ':nombre'   => $nombre,
    ':password' => $hashedPassword,
    ':rol_id'   => $rol_id,
  ]);
  $id_usuario = $stmt->fetchColumn();
  if (!$id_usuario) {
    throw new RuntimeException('No se pudo crear el usuario.');
  }

  // (3) Obtener id_configuracion más reciente
  $stmt = $pdo->query("SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
  $config = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$config || empty($config['id_configuracion'])) {
    throw new RuntimeException('No se encontró configuración de contraseña.');
  }
  $id_configuracion = (int)$config['id_configuracion'];

  // (4) Insertar en historial_passwords
  $stmt = $pdo->prepare("
    INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
    VALUES (:id_usuario, :password, NOW(), :id_configuracion, TRUE)
  ");
  $stmt->execute([
    ':id_usuario'       => $id_usuario,
    ':password'         => $hashedPassword,
    ':id_configuracion' => $id_configuracion,
  ]);

  // Confirmar transacción
  $pdo->commit();

  echo json_encode([
    "estado"      => "success",
    "mensaje"     => "Usuario y contraseña registrados correctamente",
    "id_usuario"  => (int)$id_usuario
  ]);

} catch (Throwable $e) {
  // Si hay transacción abierta, revertir
  if (isset($pdo) && $pdo->inTransaction()) {
    $pdo->rollBack();
  }
  error_log('registro.php error: '.$e->getMessage());
  echo json_encode(["estado" => "error", "mensaje" => "Error del servidor al registrar"]);
}
