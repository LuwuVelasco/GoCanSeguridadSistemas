<?php
// C:\xampp\htdocs\GoCanSeguridadSistemas\src\modules\php\obtener_rol_usuario.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Conexión (retorna $pdo)
$pdo = require __DIR__ . '/conexion.php';

try {
    // Aceptar x-www-form-urlencoded o JSON
    $data = $_POST;
    if (!$data) {
        $raw = file_get_contents('php://input');
        if ($raw) $data = json_decode($raw, true) ?: [];
    }

    $idUsuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : 0;
    if ($idUsuario <= 0) {
        echo json_encode(['estado' => 'error', 'mensaje' => 'Falta el ID de usuario']);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT u.rol_id, r.nombre_rol
         FROM usuario u
         JOIN roles_y_permisos r ON r.id_rol = u.rol_id
         WHERE u.id_usuario = :id
         LIMIT 1"
    );
    $stmt->execute([':id' => $idUsuario]);
    $row = $stmt->fetch();

    if ($row) {
        echo json_encode([
            'estado'      => 'success',
            'id_rol'      => (int)$row['rol_id'],
            'nombre_rol'  => (string)$row['nombre_rol'],
        ]);
    } else {
        echo json_encode(['estado' => 'error', 'mensaje' => 'Usuario no encontrado']);
    }
} catch (Throwable $e) {
    error_log('obtener_rol_usuario error: '.$e->getMessage());
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en la consulta']);
}
