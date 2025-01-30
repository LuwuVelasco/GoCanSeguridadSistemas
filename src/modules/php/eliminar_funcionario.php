<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Verificar que el ID del usuario fue enviado
    if (!isset($_POST['id_usuario'])) {
        throw new Exception('ID de usuario no proporcionado.');
    }

    $idUsuario = $_POST['id_usuario'];

    // Consultar el rol del usuario
    $queryCheckRole = "SELECT rol_id FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($queryCheckRole);
    $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    $roleData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$roleData) {
        throw new Exception('El usuario no existe.');
    }

    if ($roleData['rol_id'] == 3) { // 3 es el rol de Cliente
        throw new Exception('No puedes eliminar usuarios con el rol de Cliente.');
    }

    // Eliminar el usuario
    $queryDelete = "DELETE FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($queryDelete);
    $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Funcionario eliminado con éxito.']);
    } else {
        throw new Exception('Error al ejecutar la eliminación del usuario.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
