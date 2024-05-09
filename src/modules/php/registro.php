<?php
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

$email = $data->email;
$nombre = $data->nombre;
$password = $data->password;
$token = null; // Definido como null porque es gestionado internamente
$cargo = true;

$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error_conexion"]);
    exit();
}

// Asegúrate de que los identificadores sean únicos o simplemente no los uses.
$sql_usuario = "INSERT INTO usuario (email, nombre, password, token, cargo) VALUES ($1, $2, $3, $4, $5) RETURNING id_usuario";
$stmt = pg_prepare($conexion, "insert_usuario", $sql_usuario);

if ($stmt === false) {
    echo json_encode(["estado" => "error_preparar_consulta"]);
    exit();
}

$resultado_usuario = pg_execute($conexion, "insert_usuario", array($email, $nombre, $password, $token, $cargo));
if (!$resultado_usuario) {
    echo json_encode(["estado" => "error_insertar_usuario"]);
    exit();
}

$id_usuario = pg_fetch_result($resultado_usuario, 0, 'id_usuario');
echo json_encode(["estado" => "usuario_registrado", "id_usuario" => $id_usuario]);

pg_close($conexion);
?>
