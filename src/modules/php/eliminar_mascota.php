<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener el ID de la mascota desde la solicitud POST
    $id_mascota = $_POST['id_mascota'] ?? 0;

    // Validar que el ID de la mascota sea válido
    if ($id_mascota == 0) {
        echo json_encode(["estado" => "error", "mensaje" => "ID de mascota no válido"]);
        exit;
    }

    // Preparar la consulta para eliminar la mascota
    $query = "DELETE FROM mascota WHERE id_mascota = :id_mascota";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_mascota', $id_mascota, PDO::PARAM_INT);

    // Ejecutar la consulta y verificar el resultado
    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Mascota eliminada exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al eliminar la mascota"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
