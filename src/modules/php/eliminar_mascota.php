<?php
declare(strict_types=1);

// --- FUNCIONES REUTILIZABLES PARA TESTING ---

/**
 * Elimina una mascota por su ID.
 *
 * @param PDO $pdo
 * @param int $id_mascota
 * @return array Resultado de la operación
 */
if (!function_exists('eliminarMascotaPorId')) {
    function eliminarMascotaPorId(PDO $pdo, int $id_mascota): array {
        try {
            $stmt = $pdo->prepare('DELETE FROM mascota WHERE id_mascota = :id');
            $stmt->execute([':id' => $id_mascota]);

            if ($stmt->rowCount() > 0) {
                return ['estado' => 'success', 'mensaje' => 'Mascota eliminada exitosamente'];
            } else {
                return ['estado' => 'error', 'mensaje' => 'No se encontró la mascota (o ya fue eliminada)'];
            }

        } catch (PDOException $e) {
            // Error de la base de datos
            return ['estado' => 'error', 'mensaje' => 'Error al eliminar la mascota'];
        } catch (Throwable $t) {
            // Error genérico
            return ['estado' => 'error', 'mensaje' => 'Error del servidor'];
        }
    }
}

/**
 * Procesa la solicitud de eliminar mascota a partir de input (POST/JSON)
 *
 * @param PDO $pdo
 * @param array $input
 * @return array Resultado de la operación
 */
if (!function_exists('procesarSolicitudEliminarMascota')) {
    function procesarSolicitudEliminarMascota(PDO $pdo, array $input): array {
        $id_mascota = $input['id_mascota'] ?? null;

        if ($id_mascota === null || !is_numeric($id_mascota) || (int)$id_mascota <= 0) {
            return ['estado' => 'error', 'mensaje' => 'ID de mascota no válido'];
        }

        return eliminarMascotaPorId($pdo, (int)$id_mascota);
    }
}

// --- SCRIPT PRINCIPAL (solo se ejecuta si no estamos en modo TESTING) ---
if (!defined('TESTING_MODE')) {
    header('Content-Type: application/json; charset=UTF-8');

    try {
        /** @var PDO $pdo */
        $pdo = require __DIR__ . '/conexion.php';
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);
        $id_mascota = $json['id_mascota'] ?? ($_POST['id_mascota'] ?? null);

        $input = ['id_mascota' => $id_mascota];
        $resultado = procesarSolicitudEliminarMascota($pdo, $input);

        echo json_encode($resultado);

    } catch (Throwable $t) {
        error_log('eliminar_mascota.php error: ' . $t->getMessage());
        http_response_code(500);
        echo json_encode(['estado' => 'error', 'mensaje' => 'Error del servidor']);
    }
}
