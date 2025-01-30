<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
session_start(); // Para obtener $_SESSION del usuario logueado
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

    //obtener datos 
    $idUsuarioActual    = $_SESSION['id_usuario'] ?? null;
    $nombreUsuarioActual= $_SESSION['nombre_usuario'] ?? null;

    $sqlRolNombre = "SELECT nombre_rol FROM roles_y_permisos WHERE id_rol = :rol";
    $stmtRol = $pdo->prepare($sqlRolNombre);
    $stmtRol->bindParam(':rol', $rol);
    $stmtRol->execute();
    $rowRol = $stmtRol->fetch(PDO::FETCH_ASSOC);

    $nombreRolNuevoUsuario = $rowRol ? $rowRol['nombre_rol'] : 'Rol desconocido';

    registrarLogAplicacion(
        $idUsuarioActual, // QUIÉN lo hizo (la persona logueada)
        $nombreUsuarioActual, // Nombre de quien lo hizo
        'registro_usuario',   // Acción
        "Se registró un nuevo usuario con ID $idUsuario y rol $nombreRolNuevoUsuario", // Descripción
        'registrar_funcionario',  // Función afectada
        'usuario',                // Dato modificado
        "Registro de $nombreRolNuevoUsuario" // Valor original (texto descriptivo)
    );
    // Si todo va bien, responde con éxito
    echo json_encode([
        "estado" => "success",
        "mensaje" => "Funcionario registrado exitosamente.",
        "id_usuario"       => $idUsuario, // Usamos $idUsuario aquí
        "id_configuracion" => $idConfiguracion
    ]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error: " . $e->getMessage()]);
}
function registrarLogAplicacion(
    $idUsuarioActual,
    $nombreUsuarioActual,
    $accion,
    $descripcion,
    $funcionAfectada,
    $datoModificado,
    $valorOriginal
) {
    $url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_log_aplicacion.php';

    // Construir datos a enviar
    $data = http_build_query([
        'id_usuario'       => $idUsuarioActual,
        'nombre_usuario'   => $nombreUsuarioActual,  // Este es el nombre de la persona logueada
        'accion'           => $accion,
        'descripcion'      => $descripcion,
        'funcion_afectada' => $funcionAfectada,
        'dato_modificado'  => $datoModificado,
        'valor_original'   => $valorOriginal
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);

    // Llamada al archivo que hace la inserción real en log_aplicacion
    file_get_contents($url, false, $context);
}
?>
