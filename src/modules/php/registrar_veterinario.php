<?php
header('Content-Type: application/json');
include 'conexion.php'; 
date_default_timezone_set('America/La_Paz');

try {
    // Verificar que lleguen los campos requeridos
    if (!isset($_POST['nombre'], $_POST['correo'], $_POST['password'], $_POST['especialidad'], $_POST['rol'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
        exit();
    }

    $pdo->beginTransaction();

    $nombre        = $_POST['nombre'];
    $email         = $_POST['correo'];
    $rol           = $_POST['rol'];
    $idEspecialidad= $_POST['especialidad'];
    $fechaRegistro = date('Y-m-d H:i:s');
    $hashedPassword= password_hash($_POST['password'], PASSWORD_BCRYPT);

    // 1) Insertar en la tabla doctores
    $stmtDoctor = $pdo->prepare("
        INSERT INTO doctores (nombre, id_especialidad) 
        VALUES (:nombre, :idEspecialidad)
        RETURNING id_doctores
    ");
    $stmtDoctor->bindParam(':nombre', $nombre);
    $stmtDoctor->bindParam(':idEspecialidad', $idEspecialidad);
    $stmtDoctor->execute();

    // Obtener el ID del doctor
    $idDoctor = $stmtDoctor->fetchColumn();
    if (!$idDoctor) {
        $pdo->rollBack();
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo obtener el ID del doctor."]);
        exit();
    }

    // 2) Insertar en tabla usuario
    $stmtUsuario = $pdo->prepare("
        INSERT INTO usuario (email, nombre, password, rol_id, id_doctores, fecha_registro)
        VALUES (:email, :nombre, :password, :rol, :idDoctor, :fecha_registro)
        RETURNING id_usuario
    ");
    $stmtUsuario->bindParam(':email', $email);
    $stmtUsuario->bindParam(':nombre', $nombre);
    $stmtUsuario->bindParam(':password', $hashedPassword);
    $stmtUsuario->bindParam(':rol', $rol);
    $stmtUsuario->bindParam(':idDoctor', $idDoctor);
    $stmtUsuario->bindParam(':fecha_registro', $fechaRegistro);
    $stmtUsuario->execute();

    $idUsuario = $stmtUsuario->fetchColumn();
    if (!$idUsuario) {
        $pdo->rollBack();
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo obtener el ID del usuario (veterinario)."]);
        exit();
    }

    // 3) Obtener config para historial
    $sqlConfig = "SELECT id_configuracion FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmtConfig = $pdo->query($sqlConfig);
    $config = $stmtConfig->fetch(PDO::FETCH_ASSOC);
    if (!$config) {
        $pdo->rollBack();
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"]);
        exit();
    }
    $idConfig = $config['id_configuracion'];

    // 4) Insertar en historial_passwords
    $sqlHist = "
        INSERT INTO historial_passwords (id_usuario, password, fecha_creacion, id_configuracion, estado)
        VALUES (:id_usuario, :password, :fecha_creacion, :id_configuracion, :estado)
    ";
    $stmtHist = $pdo->prepare($sqlHist);
    $stmtHist->bindParam(':id_usuario', $idUsuario);
    $stmtHist->bindParam(':password', $hashedPassword);
    $stmtHist->bindParam(':fecha_creacion', $fechaRegistro);
    $stmtHist->bindParam(':id_configuracion', $idConfig);
    $stmtHist->bindValue(':estado', true, PDO::PARAM_BOOL);
    $stmtHist->execute();

    $pdo->commit();

    // Respuesta exitosa
    echo json_encode([
        "estado" => "success",
        "mensaje" => "Veterinario registrado exitosamente.",
        "id_usuario" => $idUsuario,
        "id_configuracion" => $idConfig
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
?>
