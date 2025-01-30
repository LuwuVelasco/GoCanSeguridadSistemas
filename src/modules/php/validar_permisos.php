<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que el ID del rol fue enviado
    if (!isset($_POST['id_rol'])) {
        echo json_encode(['success' => false, 'message' => 'ID de rol no especificado']);
        exit;
    }

    $idRol = $_POST['id_rol'];

    // Consulta para obtener los permisos del rol con PDO
    $query = "SELECT * FROM roles_y_permisos WHERE id_rol = :id_rol";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_rol', $idRol, PDO::PARAM_INT);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Filtrar solo los permisos (excluir ID y nombre del rol)
        $permisos = [];
        foreach ($row as $clave => $valor) {
            if (!in_array($clave, ['id_rol', 'nombre_rol'])) {
                $permisos[$clave] = ($valor === 't' || $valor === true) ? true : false;
            }
        }

        echo json_encode(['success' => true, 'permisos' => $permisos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron permisos para este rol']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los permisos: ' . $e->getMessage()]);
}
?>
