<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'conexion.php';

// Recibir los datos del formulario
$propietario = trim($_POST['propietario'] ?? '');
$sintomas = trim($_POST['sintomas'] ?? '');
$diagnostico = trim($_POST['diagnostico'] ?? '');
$receta = trim($_POST['receta'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$nombre_mascota = trim($_POST['nombre_mascota'] ?? '');

// Validar que los campos no estén vacíos
if (empty($propietario) || empty($sintomas) || empty($diagnostico) || empty($receta) || empty($fecha) || empty($nombre_mascota)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Verificar que el propietario y la mascota existan
$query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result_usuario = pg_query_params($conexion, $query_usuario, [$propietario]);

if ($result_usuario && pg_num_rows($result_usuario) > 0) {
    $usuario = pg_fetch_assoc($result_usuario);
    $id_usuario = $usuario['id_usuario'];

    $query_mascota = "SELECT id_mascota FROM mascota WHERE nombre_mascota = $1 AND id_usuario = $2";
    $result_mascota = pg_query_params($conexion, $query_mascota, [$nombre_mascota, $id_usuario]);

    if ($result_mascota && pg_num_rows($result_mascota) > 0) {
        $mascota = pg_fetch_assoc($result_mascota);
        $id_mascota = $mascota['id_mascota'];

        // Insertar el reporte
        $sql = "INSERT INTO reporte (propietario, sintomas, diagnostico, receta, fecha, nombre_mascota) 
                VALUES ($1, $2, $3, $4, $5, $6) RETURNING id_reporte";
        $params = [$propietario, $sintomas, $diagnostico, $receta, $fecha, $nombre_mascota];
        $result = pg_query_params($conexion, $sql, $params);

        if ($result) { // Aquí usamos $result
            $reporte = pg_fetch_assoc($result);
            echo json_encode(["estado" => "success", "id_reporte" => $reporte['id_reporte']]);
            exit;
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el reporte"]);
        }
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "La mascota no existe"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}

pg_close($conexion);
?>
