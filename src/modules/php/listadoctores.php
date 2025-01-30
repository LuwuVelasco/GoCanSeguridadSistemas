<?php
header('Content-Type: application/json');
include 'conexion.php';

try {
    // Consulta para obtener todos los funcionarios excepto clientes
    $sql = "SELECT u.id_usuario, u.nombre, 
            COALESCE(e.nombre_especialidad, '—') AS especialidad, 
            r.nombre_rol 
            FROM usuario u
            LEFT JOIN doctores d ON u.id_doctores = d.id_doctores
            LEFT JOIN especialidad e ON d.id_especialidad = e.id_especialidad
            LEFT JOIN roles_y_permisos r ON u.rol_id = r.id_rol
            WHERE u.rol_id != 3
            ORDER BY u.id_usuario;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($funcionarios);
} catch (Exception $e) {
    echo json_encode(["error" => true, "message" => $e->getMessage()]);
}
?>
