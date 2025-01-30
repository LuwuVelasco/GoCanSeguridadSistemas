<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que el ID de la mascota fue enviado
    $id_mascota = $_GET['id_mascota'] ?? '';

    if (empty($id_mascota)) {
        echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no proporcionado"]);
        exit;
    }

    // Consulta para obtener los datos de la mascota con PDO
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
            usuario u ON m.id_usuario = u.id_usuario
        WHERE 
            m.id_mascota = :id_mascota";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_mascota', $id_mascota, PDO::PARAM_INT);
    $stmt->execute();
    $mascota = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mascota) {
        echo json_encode(["estado" => "success", "mascota" => $mascota]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Mascota no encontrada"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
