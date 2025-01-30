<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener todos los usuarios con rol de Cliente (rol_id = 3)
    $query = "SELECT id_usuario, nombre, email FROM usuario WHERE rol_id = 3";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$clientes) {
        echo json_encode(["estado" => "success", "mensaje" => "No hay clientes registrados", "clientes" => []]);
    } else {
        echo json_encode(["estado" => "success", "clientes" => $clientes]);
    }
} catch (Exception $e) {
    echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
}
?>
