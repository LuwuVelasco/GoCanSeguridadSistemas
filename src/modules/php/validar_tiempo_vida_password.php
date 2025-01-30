<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['id_usuario'])) {
    $id_usuario = $data['id_usuario'];

    try {
        // Obtener la configuración más reciente
        $sql_configuracion = "SELECT tiempo_vida_util FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
        $stmt_config = $pdo->query($sql_configuracion);
        $configuracion = $stmt_config->fetch(PDO::FETCH_ASSOC);

        if (!$configuracion) {
            echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseñas"]);
            exit();
        }

        $tiempo_vida_util = (int) $configuracion['tiempo_vida_util'];

        // Obtener la contraseña más reciente del usuario
        $sql_historial = "
            SELECT fecha_creacion 
            FROM historial_passwords 
            WHERE id_usuario = :id_usuario AND estado = true 
            ORDER BY fecha_creacion DESC LIMIT 1
        ";
        $stmt_historial = $pdo->prepare($sql_historial);
        $stmt_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_historial->execute();
        $registro = $stmt_historial->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            echo json_encode(["estado" => "error", "mensaje" => "No se encontró una contraseña activa para el usuario"]);
            exit();
        }

        // Comparar fechas
        $fecha_creacion = new DateTime($registro['fecha_creacion']);
        $fecha_actual = new DateTime();
        $fecha_expiracion = $fecha_creacion->add(new DateInterval("P{$tiempo_vida_util}D"));

        if ($fecha_actual > $fecha_expiracion) {
            echo json_encode(["estado" => "error", "mensaje" => "La contraseña ha expirado"]);
        } else {
            echo json_encode(["estado" => "success", "mensaje" => "La contraseña sigue siendo válida"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
}
?>
