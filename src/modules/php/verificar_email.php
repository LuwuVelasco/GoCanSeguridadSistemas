<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['email'])) {
    $email = $data['email'];

    // Conexión a la base de datos
    $conexion = pg_connect("dbname=gocan user=postgres password=admin");
    if (!$conexion) {
        echo json_encode(["estado" => "error", "mensaje" => "Error de conexión a la base de datos"]);
        exit();
    }

    // Consulta para verificar si el correo ya existe
    $sql = "SELECT COUNT(*) AS total FROM usuario WHERE email = $1";
    $stmt = pg_prepare($conexion, "verificar_email", $sql);
    if ($stmt === false) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al preparar la consulta"]);
        exit();
    }

    $resultado = pg_execute($conexion, "verificar_email", array($email));
    if (!$resultado) {
        $error = pg_last_error($conexion);
        echo json_encode(["estado" => "error", "mensaje" => "Error al ejecutar la consulta: " . $error]);
        exit();
    }

    $row = pg_fetch_assoc($resultado);
    $total = $row['total'];

    // Verificar si el correo ya está registrado
    if ($total > 0) {
        echo json_encode(["estado" => "error", "mensaje" => "El correo ya está registrado"]);
    } else {
        echo json_encode(["estado" => "success", "mensaje" => "El correo está disponible"]);
    }

    pg_close($conexion);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
    exit();
}
?>
