<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que los datos fueron enviados
    if (!isset($_POST['id_reporte'], $_POST['propietario'], $_POST['nombre_mascota'], 
              $_POST['sintomas'], $_POST['diagnostico'], $_POST['receta'], $_POST['fecha'])) {
        echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Recuperar los valores de la solicitud POST
    $id_reporte = $_POST['id_reporte'];
    $propietario = $_POST['propietario'];
    $nombre_mascota = $_POST['nombre_mascota'];
    $sintomas = $_POST['sintomas'];
    $diagnostico = $_POST['diagnostico'];
    $receta = $_POST['receta'];
    $fecha = $_POST['fecha'];

    // Preparar la consulta SQL con PDO
    $sql = "UPDATE reporte 
            SET propietario = :propietario, 
                nombre_mascota = :nombre_mascota, 
                sintomas = :sintomas, 
                diagnostico = :diagnostico, 
                receta = :receta, 
                fecha = :fecha 
            WHERE id_reporte = :id_reporte";

    $stmt = $pdo->prepare($sql);
    
    // Vincular parámetros de manera segura
    $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);
    $stmt->bindParam(':sintomas', $sintomas, PDO::PARAM_STR);
    $stmt->bindParam(':diagnostico', $diagnostico, PDO::PARAM_STR);
    $stmt->bindParam(':receta', $receta, PDO::PARAM_STR);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->bindParam(':id_reporte', $id_reporte, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(["estado" => "success", "mensaje" => "Reporte actualizado correctamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "No se pudo actualizar el reporte"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
