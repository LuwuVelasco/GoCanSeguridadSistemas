<?php
header('Content-Type: application/json');
include 'conexion.php'; // Asegúrate de que este archivo configura correctamente la conexión

// Validar que se haya enviado el ID del usuario
if (!isset($_POST['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "ID del usuario no proporcionado"]);
    exit;
}

$id_usuario = $_POST['id_usuario'];

// Consulta para obtener el rol del usuario
$sql = "
    SELECT 
        u.id_usuario, 
        r.id_rol, 
        r.nombre_rol
    FROM usuario u
    INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
    WHERE u.id_usuario = $1
";

$result = pg_prepare($conexion, "query_rol_usuario", $sql);
$result = pg_execute($conexion, "query_rol_usuario", array($id_usuario));

if ($row = pg_fetch_assoc($result)) {
    // Rol encontrado, devolver información
    echo json_encode([
        "estado" => "success",
        "id_rol" => $row['id_rol'],
        "nombre_rol" => $row['nombre_rol']
    ]);
} else {
    // Rol no encontrado
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Rol no encontrado para el usuario especificado"
    ]);
}

// Cerrar la conexión
pg_close($conexion);
?>
