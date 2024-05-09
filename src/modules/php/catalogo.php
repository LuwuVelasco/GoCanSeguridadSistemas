<?php
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

$nombre = $data->nombre;
$descripcion = $data->descripcion;
$precio = $data->precio;
$categoria = $data->categoria;
$id_usuario = 101;

$conexion = pg_connect("dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error_conexion"]);
    exit();
}

$sql_producto = "INSERT INTO producto (nombre, descripcion, precio, categoria, id_usuario) VALUES ($1, $2, $3, $4,$5) RETURNING id_producto";
$stmt = pg_prepare($conexion, "insert_producto", $sql_producto);

if ($stmt === false) {
    echo json_encode(["estado" => "error_preparar_consulta"]);
    exit();
}

$resultado_producto = pg_execute($conexion, "insert_producto", array($nombre, $descripcion, $precio, $categoria, $id_usuario));
if (!$resultado_producto) {
    echo json_encode(["estado" => "error_insertar_producto"]);
    exit();
}
$id_producto = pg_fetch_result($resultado_producto, 0, 'id_producto');
echo json_encode(["estado" => "producto_registrado", "id_producto" => $id_producto]);

pg_close($conexion);
?>
