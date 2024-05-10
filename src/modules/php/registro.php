<?php
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);  // Asegúrate de que estás usando true para obtener un array asociativo

echo "Datos recibidos: ";
print_r($data);

if (isset($data['token'], $data['verified']) && $data['verified'] == true) {
    if (isset($data['email'], $data['nombre'], $data['password'])) {
        $email = $data['email'];
        $nombre = $data['nombre'];
        $password = $data['password'];
        $token = $data['token'];
        // Asegúrate de manejar correctamente el booleano de 'cargo'
        $cargo = isset($data['cargo']) ? filter_var($data['cargo'], FILTER_VALIDATE_BOOLEAN) : false;

        $conexion = pg_connect("dbname=gocan user=postgres password=admin");
        if (!$conexion) {
            echo json_encode(["estado" => "error_conexion"]);
            exit();
        }

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

    } else {
        echo json_encode(["estado" => "error_campos"]);
        exit();
    }
} else {
    echo json_encode(["estado" => "error_token"]);
    exit();
}
?>
