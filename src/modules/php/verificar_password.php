<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "Falta el ID de usuario"]);
    exit();
}

$id_usuario = $data['id_usuario'];

try {
    // 1. Verificar el rol del usuario
    $sqlRol = "
        SELECT u.rol_id, r.nombre_rol 
        FROM usuario u
        INNER JOIN roles_y_permisos r ON u.rol_id = r.id_rol
        WHERE u.id_usuario = :id_usuario
    ";
    $stmtRol = $pdo->prepare($sqlRol);
    $stmtRol->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtRol->execute();
    $rowRol = $stmtRol->fetch(PDO::FETCH_ASSOC);

    if (!$rowRol) {
        echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
        exit();
    }

    $nombreRol = $rowRol['nombre_rol'];

    // 2. Obtener la configuración de contraseñas más reciente
    $sql_configuracion = "SELECT * FROM configuracion_passwords ORDER BY id_configuracion DESC LIMIT 1";
    $stmtConfig = $pdo->query($sql_configuracion);
    $configuracion = $stmtConfig->fetch(PDO::FETCH_ASSOC);

    if (!$configuracion) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró configuración de contraseña"]);
        exit();
    }

    $tiempo_vida_util = $configuracion['tiempo_vida_util'];
    $id_configuracion = $configuracion['id_configuracion'];

    // 3. Obtener la contraseña vigente del usuario
    $sql_historial = "
        SELECT * 
        FROM historial_passwords 
        WHERE id_usuario = :id_usuario 
        AND estado = true 
        AND id_configuracion = :id_configuracion 
        ORDER BY fecha_creacion DESC 
        LIMIT 1
    ";
    $stmtHistorial = $pdo->prepare($sql_historial);
    $stmtHistorial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtHistorial->bindParam(':id_configuracion', $id_configuracion, PDO::PARAM_INT);
    $stmtHistorial->execute();
    $historial = $stmtHistorial->fetch(PDO::FETCH_ASSOC);

    if (!$historial) {
        echo json_encode(["estado" => "error", "mensaje" => "No se encontró contraseña vigente para el usuario"]);
        exit();
    }

    // 4. Revisar si la contraseña está expirada
    $fecha_creacion = new DateTime($historial['fecha_creacion']);
    $fecha_actual = new DateTime();
    $dias_diferencia = $fecha_creacion->diff($fecha_actual)->days;

    if ($dias_diferencia > $tiempo_vida_util) {
        echo json_encode([
            "estado" => "expired",
            "mensaje" => "Tu contraseña ha expirado. Por favor, cámbiala para continuar.",
            "id_usuario" => $id_usuario
        ]);
        exit();
    }

    // 5. Verificar si el usuario no es 'Cliente' y tiene sólo 1 contraseña en historial
    $sqlTotal = "
        SELECT COUNT(*) AS total
        FROM historial_passwords
        WHERE id_usuario = :id_usuario
    ";
    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmtTotal->execute();
    $rowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalPasswords = (int)$rowTotal['total'];

    // Si el rol no es 'Cliente' y tiene solo 1 contraseña en historial, forzar cambio
    if ($nombreRol !== 'Cliente' && $totalPasswords === 1) {
        echo json_encode([
            "estado" => "change_required",
            "mensaje" => "Debes cambiar tu contraseña generada automáticamente.",
            "id_usuario" => $id_usuario
        ]);
        exit();
    }

    echo json_encode(["estado" => "success", "mensaje" => "Contraseña vigente"]);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}
?>
