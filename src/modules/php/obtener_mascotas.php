<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Consulta para obtener todas las mascotas con PDO
    $query = "
        SELECT 
            m.id_mascota,
            m.nombre_mascota,
            m.fecha_nacimiento,
            m.tipo,
            m.raza,
            u.nombre AS nombre_propietario
        FROM 
            mascota m
        JOIN 
            usuario u ON m.id_usuario = u.id_usuario";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["estado" => "success", "mascotas" => $mascotas]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
