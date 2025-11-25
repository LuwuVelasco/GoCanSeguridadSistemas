<?php
declare(strict_types=1);

// Función pura - fácil de testear
if (!function_exists('obtenerUsuarioPorId')) {
    function obtenerUsuarioPorId(PDO $pdo, int $id): array {
        try {
            $stmt = $pdo->prepare('SELECT nombre FROM usuario WHERE id_usuario = :id LIMIT 1');
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return ['estado' => 'success', 'nombre' => $row['nombre']];
            } else {
                return ['estado' => 'error', 'mensaje' => 'Usuario no encontrado'];
            }
        } catch (Throwable $e) {
            return ['estado' => 'error', 'mensaje' => 'Error del servidor'];
        }
    }
}

// Función para procesar la solicitud
if (!function_exists('procesarSolicitudUsuario')) {
    function procesarSolicitudUsuario(PDO $pdo, array $input): array {
        $id_usuario = $input['id_usuario'] ?? null;

        if ($id_usuario === null || !is_numeric($id_usuario)) {
            return ['estado' => 'error', 'mensaje' => 'No se encontró un id_usuario válido'];
        }

        $id = (int)$id_usuario;
        return obtenerUsuarioPorId($pdo, $id);
    }
}

// --- SCRIPT PRINCIPAL (solo se ejecuta si no estamos en modo test) ---
if (!defined('TESTING_MODE')) {
    header('Content-Type: application/json; charset=UTF-8');

    $allowed_origins = ['http://localhost','http://127.0.0.1','http://localhost:5500','http://127.0.0.1:5500'];
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowed_origins, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') { 
        http_response_code(204); 
        exit; 
    }

    try {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        $id_usuario = $json['id_usuario'] ?? ($_POST['id_usuario'] ?? $_GET['id_usuario'] ?? null);

        $input = ['id_usuario' => $id_usuario];
        $resultado = procesarSolicitudUsuario($pdo, $input);

        echo json_encode($resultado);

    } catch (Throwable $e) {
        error_log('obtener_usuario.php error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['estado' => 'error', 'mensaje' => 'Error del servidor']);
    }
}