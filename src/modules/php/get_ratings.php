<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Ejecutar la consulta
$sql = "SELECT estrellas, COUNT(*) as count FROM calificacion GROUP BY estrellas";
$result = pg_query($conexion, $sql);

if ($result) {
    $ratings = [];
    while ($row = pg_fetch_assoc($result)) {
        $ratings[] = ["stars" => (int)$row["estrellas"], "count" => (int)$row["count"]];
    }
    echo json_encode(["estado" => "success", "ratings" => $ratings]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "No se encontraron calificaciones"]);
}

// Cerrar la conexión
pg_close($conexion);
?>
