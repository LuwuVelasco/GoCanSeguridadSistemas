<?php
session_start(); // Iniciar la sesión
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Validar que los datos requeridos estén presentes
if (empty($_POST['email']) || empty($_POST['password'])) {
    echo json_encode(["estado" => "error", "mensaje" => "El email y la contraseña son obligatorios"]);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
//Prueba capcha
$recaptchaResponse = $_POST['g-recaptcha-response'];
$secretKey = '6Ldn970qAAAAANB2ogY4Ml1jVCvjt203gjG0jamr';

if (empty($recaptchaResponse)) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Captcha no enviado']);
    exit;
}

$verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
$response = file_get_contents($verifyUrl . '?secret=' . $secretKey . '&response=' . $recaptchaResponse);
$responseData = json_decode($response);

if (!$responseData->success) {
    error_log('Captcha error codes: ' . implode(', ', $responseData->{'error-codes'} ?? []));
    echo json_encode(['estado' => 'error', 'mensaje' => 'Captcha no válido']);
    exit;
}
// Consulta para buscar al usuario por email, incluyendo el rol
$sql = "
    SELECT 
        u.id_usuario, 
        u.id_doctores, 
        u.password, 
        r.nombre AS rol
    FROM usuario u
    INNER JOIN rol r ON u.rol_id = r.id
    WHERE u.email = $1
";
$result = pg_prepare($conexion, "login_query", $sql);
$result = pg_execute($conexion, "login_query", array($email));

if ($row = pg_fetch_assoc($result)) {
    // Verificar la contraseña ingresada con el hash almacenado
    if (password_verify($password, $row['password'])) {
        // Contraseña válida, iniciar sesión
        if (isset($row['id_doctores'])) {
            $_SESSION['id_doctores'] = $row['id_doctores'];
        }

        echo json_encode([
            "estado" => "success",
            "id_usuario" => $row['id_usuario'],
            "rol" => $row['rol'], // Retorna el nombre del rol
            "id_doctores" => $row['id_doctores']
        ]);
    } else {
        // Contraseña incorrecta
        echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
    }
} else {
    // No se encontró un usuario con ese email
    echo json_encode(["estado" => "error", "mensaje" => "El email o la contraseña son incorrectos"]);
}

// Cerrar la conexión
pg_close($conexion);

