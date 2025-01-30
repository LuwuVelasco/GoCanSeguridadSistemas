<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

// Verificar conexión
if (!$pdo) {
    echo json_encode(["estado" => "error", "mensaje" => "Error de conexión a la base de datos"]);
    exit;
}

try {
    // Consulta para obtener todos los reportes
    $sql = "SELECT propietario, sintomas, diagnostico, receta, fecha, nombre_mascota FROM reporte";
    $stmt = $pdo->query($sql);
    $reportes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; // Asegurar que no sea null

    echo json_encode([
        "estado" => "success",
        "mensaje" => empty($reportes) ? "No hay reportes disponibles" : "Reportes obtenidos correctamente",
        "reportes" => $reportes
    ]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
