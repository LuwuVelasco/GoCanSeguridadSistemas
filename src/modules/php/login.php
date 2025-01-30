<?php 
session_start(); // Iniciar la sesión
header('Content-Type: application/json');

// Incluir el archivo de conexión (asegúrate de que devuelve `$pdo`)
$pdo = include 'conexion.php';

// Validar que los datos requeridos estén presentes
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode(["estado" => "error", "mensaje" => "El email y la contraseña son obligatorios"]);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? null;
$secretKey = '6Ldn970qAAAAANB2ogY4Ml1jVCvjt203gjG0jamr';

// Verificar el reCAPTCHA
if (empty($recaptchaResponse)) {
    registrarLog(null, 'captcha_fallido', 'Captcha no enviado');
    echo json_encode(['estado' => 'error', 'mensaje' => 'Captcha no enviado']);
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
$responseData = json_decode($response);

if (!$responseData->success) {
    registrarLog(null, 'captcha_fallido', 'Captcha inválido');
    echo json_encode(['estado' => 'error', 'mensaje' => 'Captcha no válido']);
    exit;
}

try {
    // Consulta para buscar al usuario por email con PDO
    $sql = "
        SELECT 
            u.id_usuario, 
            u.id_doctores, 
            u.password, 
            u.nombre, 
            u.rol_id, 
            r.nombre_rol AS rol
        FROM usuario u
        INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
        WHERE u.email = :email
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Verificar la contraseña ingresada con el hash almacenado
        if (password_verify($password, $row['password'])) {
            // Contraseña válida, iniciar sesión
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['nombre_usuario'] = $row['nombre']; // Guardar el nombre del usuario
            $_SESSION['rol_id'] = $row['rol_id'];         // Guardar el ID del rol
            $_SESSION['rol'] = $row['rol'];               // Guardar el nombre del rol
            if (isset($row['id_doctores'])) {
                $_SESSION['id_doctores'] = $row['id_doctores'];
            }

            // Registrar log de login exitoso
            registrarLog($row['id_usuario'], 'login_exitoso', 'Inicio de sesión exitoso');

            echo json_encode([
                "estado" => "success",
                "id_usuario" => $row['id_usuario'],
                "rol" => $row['rol'], // Retorna el nombre del rol
                "id_doctores" => $row['id_doctores']
            ]);
        } else {
            // Contraseña incorrecta
            registrarLog(null, 'login_fallido', 'Contraseña incorrecta para el correo ' . $email);
            echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
        }
    } else {
        // Usuario no encontrado
        registrarLog(null, 'login_fallido', 'Intento de inicio de sesión con un correo inexistente: ' . $email);
        echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la consulta: " . $e->getMessage()]);
}

//Función para registrar log de usuario
function registrarLog($idUsuario, $accion, $descripcion) {
    $url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_log_usuario.php';

    $data = http_build_query([
        'id_usuario' => $idUsuario,
        'accion' => $accion,
        'descripcion' => $descripcion
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}
?>