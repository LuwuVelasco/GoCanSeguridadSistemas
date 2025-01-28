<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('America/La_Paz');

include 'conexion.php';
try {
    if (!isset($_POST['nombre'], $_POST['correo'], $_POST['password'], $_POST['rol'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }
    $nombre = $_POST['nombre'];
    $email = $_POST['correo'];
    $rol = $_POST['rol'];
    $fechaRegistro = date('Y-m-d H:i:s');
    $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $sqlUsuario = "
        INSERT INTO usuario (email, nombre, password, rol_id, fecha_registro)
        VALUES (:email, :nombre, :password, :rol, :fecha_registro)
        RETURNING id_usuario
    ";
    $stmtUsuario = $pdo->prepare($sqlUsuario);
    $stmtUsuario->bindParam(':email', $email);
    $stmtUsuario->bindParam(':nombre', $nombre);
    $stmtUsuario->bindParam(':password', $hashedPassword);
    $stmtUsuario->bindParam(':rol', $rol);
    $stmtUsuario->bindParam(':fecha_registro', $fechaRegistro);

    $stmtUsuario->execute();
    // Obtener el ID retornado
    $idUsuario = $stmtUsuario->fetchColumn();
    if (!$idUsuario) {
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo obtener el ID del usuario registrado."]);
        exit();
    }

    // 2) Obtener la configuración de contraseñas más reciente
    $sqlConfig = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmtConfig = $pdo->query($sqlConfig);
    $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);
    if (!$config) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"]);
        exit();
    }
    $idConfiguracion = $config['id_configuracion'];

    // 3) Insertar en historial_passwords usando la contraseña hasheada
    $sqlHistorial = "
        INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
        VALUES (:id_usuario, :password, :fecha_creacion, :id_configuracion, :estado)
    ";
    $stmtHistorial = $pdo->prepare($sqlHistorial);
    $stmtHistorial->bindParam(':id_usuario', $idUsuario);
    $stmtHistorial->bindParam(':password', $hashedPassword);    // Guardar la misma contraseña hasheada
    $stmtHistorial->bindParam(':fecha_creacion', $fechaRegistro);
    $stmtHistorial->bindParam(':id_configuracion', $idConfiguracion);
    $stmtHistorial->bindValue(':estado', true, PDO::PARAM_BOOL); // Asignar estado = true
    $stmtHistorial->execute();

    // Si todo va bien, responde con éxito
    echo json_encode([
        "estado" => "success",
        "mensaje" => "Funcionario registrado exitosamente.",
        "id_usuario" => $idUsuario,
        "id_configuracion" => $idConfiguracion
    ]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
?>
