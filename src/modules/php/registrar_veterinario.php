<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    $nombre = $_POST['nombre'];
    $email = $_POST['correo'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];
    $especialidad = $_POST['especialidad'];
    $fechaRegistro = date('Y-m-d H:i:s'); // Fecha y hora actual

    // Iniciar transacción
    $pdo->beginTransaction();

    // Insertar en la tabla doctores
    $stmtDoctor = $pdo->prepare("INSERT INTO doctores (nombre, id_especialidad) VALUES (:nombre, :especialidad)");
    $stmtDoctor->bindParam(':nombre', $nombre);
    $stmtDoctor->bindParam(':especialidad', $especialidad);
    $stmtDoctor->execute();

    // Obtener el ID del doctor recién insertado
    $idDoctor = $pdo->lastInsertId();

    // Insertar en la tabla usuario
    $stmtUsuario = $pdo->prepare("INSERT INTO usuario (email, nombre, password, rol_id, id_doctores, fecha_registro) 
                                  VALUES (:email, :nombre, :password, :rol, :id_doctores, :fecha_registro)");
    $stmtUsuario->bindParam(':email', $email);
    $stmtUsuario->bindParam(':nombre', $nombre);
    $stmtUsuario->bindParam(':password', $password);
    $stmtUsuario->bindParam(':rol', $rol);
    $stmtUsuario->bindParam(':id_doctores', $idDoctor); // Asociar el usuario con el ID del doctor
    $stmtUsuario->bindParam(':fecha_registro', $fechaRegistro);
    $stmtUsuario->execute();

    // Confirmar la transacción
    $pdo->commit();

    echo json_encode(["estado" => "success", "mensaje" => "Veterinario registrado exitosamente."]);
} catch (PDOException $e) {
    $pdo->rollBack(); // Revertir la transacción en caso de error
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
?>
