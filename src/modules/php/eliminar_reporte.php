<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener los datos del reporte a eliminar (soporte para JSON y x-www-form-urlencoded)
    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

    $propietario = $data['propietario'] ?? null;
    $nombre_mascota = $data['nombre_mascota'] ?? null;

    // Validar que los datos sean proporcionados
    if (!$propietario || !$nombre_mascota) {
        echo json_encode(["estado" => "error", "mensaje" => "Datos de reporte inválidos"]);
        exit;
    }

    // Consulta para eliminar el reporte
    $sql = "DELETE FROM public.reporte WHERE propietario = :propietario AND nombre_mascota = :nombre_mascota";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Reporte eliminado exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo eliminar el reporte"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
