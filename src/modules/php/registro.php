<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

include 'conexion.php'; // Debe devolver la variable $pdo (PDO)

date_default_timezone_set('America/La_Paz');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['verified']) || $data['verified'] !== true) {
        echo json_encode(["estado" => "error", "mensaje" => "Token no verificado"]);
        exit();
    }

    if (!isset($data['email'], $data['nombre'], $data['password'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }

    $email = $data['email'];
    $nombre = $data['nombre'];
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $rol_id = 3; // Rol por defecto
    $fecha_registro = date('Y-m-d H:i:s');

    $pdo->beginTransaction(); // Iniciar transacción

    // 1 Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM usuario WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['total'] > 0) {
        echo json_encode(["estado" => "error", "mensaje" => "El correo ya está registrado"]);
        exit();
    }

    // 2 Insertar el usuario y obtener su ID
    $stmt = $pdo->prepare("
        INSERT INTO usuario (email, nombre, password, fecha_registro, rol_id) 
        VALUES (:email, :nombre, :password, :fecha_registro, :rol_id) 
        RETURNING id_usuario
    ");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':fecha_registro', $fecha_registro);
    $stmt->bindParam(':rol_id', $rol_id);
    $stmt->execute();

    $id_usuario = $stmt->fetchColumn();
    if (!$id_usuario) {
        throw new Exception("No se pudo obtener el ID del usuario registrado.");
    }

    // 3 Obtener la última configuración de contraseñas
    $stmt = $pdo->query("SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        throw new Exception("No se encontró configuración de contraseña.");
    }
    $id_configuracion = $config['id_configuracion'];

    // 4 Insertar en historial_passwords
    $stmt = $pdo->prepare("
        INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado) 
        VALUES (:id_usuario, :password, :fecha_creacion, :id_configuracion, true)
    ");
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':fecha_creacion', $fecha_registro);
    $stmt->bindParam(':id_configuracion', $id_configuracion);
    $stmt->execute();

    $pdo->commit(); // Confirmar la transacción

    echo json_encode(["estado" => "success", "mensaje" => "Usuario y contraseña registrados correctamente", "id_usuario" => $id_usuario]);

} catch (Exception $e) {
    $pdo->rollBack(); // Revertir transacción en caso de error
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
?>
