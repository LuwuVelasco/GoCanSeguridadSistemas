<?php
session_start();
header('Content-Type: application/json');

// Función para manejar errores
function handle_error($mensaje) {
    echo json_encode(["estado" => "error", "mensaje" => $mensaje]);
    exit;
}

// Conectar a la base de datos
$conexion = pg_connect("dbname=gocan user=postgres password=admin port='5433'");
if (!$conexion) {
    handle_error("No se pudo conectar a la base de datos");
}

$email = $_POST['email'];
$password = $_POST['password'];

// Consulta para buscar al usuario por email, contraseña y obtener el campo cargo, id_doctores, y nombre
$sql = "SELECT id_usuario, cargo, id_doctores, nombre FROM usuario WHERE email = $1 AND password = $2";
$result = pg_prepare($conexion, "login_query", $sql);
if (!$result) {
    handle_error("Error al preparar la consulta");
}

$result = pg_execute($conexion, "login_query", array($email, $password));
if (!$result) {
    handle_error("Error al ejecutar la consulta");
}

if ($row = pg_fetch_assoc($result)) {
    if (isset($row['id_doctores'])) {
        $_SESSION['id_doctores'] = $row['id_doctores'];
    }

    // Respuesta de éxito antes de registrar la actividad
    echo json_encode([
        "estado" => "success",
        "id_usuario" => $row['id_usuario'],
        "cargo" => $row['cargo'],
        "id_doctores" => $row['id_doctores']
    ]);

    // Usar cURL para enviar una petición POST al archivo PHP que registra la actividad
    $ch = curl_init('http://localhost/GoCan/src/modules/php/registrar_actividad.php');
    $data = http_build_query([
        'id_usuario' => $row['id_usuario'],
        'nombre_usuario' => $row['nombre']  // Asegúrate de incluir el nombre del usuario aquí
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Decodificar la respuesta y manejar cualquier error
    $responseDecoded = json_decode($response, true);
    if ($responseDecoded['estado'] !== 'success') {
        // Manejar el error de la inserción de actividad aquí si es necesario
    }

} else {
    handle_error("El email o la contraseña son incorrectos");
}
<<<<<<< HEAD

pg_close($conexion);
=======
>>>>>>> origin/MatyLastOne
?>