<?php
header('Content-Type: application/json');
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($data['fecha']) && isset($data['horario'])) {
            // Registrar una nueva cita
            $fecha = $data['fecha'];
            $horario = $data['horario'];

            // Validar la fecha y hora
            $fechaHoraIngresada = new DateTime("$fecha $horario");
            $now = new DateTime();

            if ($fechaHoraIngresada <= $now) {
                echo json_encode(['error' => 'No puedes registrar una cita en una fecha u hora pasada.']);
                exit;
            }

            echo json_encode(['mensaje' => 'Cita registrada correctamente']);
            exit;

        } elseif (isset($data['id_usuario'])) {
            // Cargar citas del usuario
            $id_usuario = $data['id_usuario'];

            // Consulta para obtener citas futuras
            $query = "
                SELECT id_cita, propietario, servicio, fecha, horario 
                FROM cita 
                WHERE id_usuario = $1 AND 
                (fecha > CURRENT_DATE OR 
                (fecha = CURRENT_DATE AND horario > CURRENT_TIME))
            ";
            $result = pg_prepare($conexion, "query_citas_usuario", $query);
            $result = pg_execute($conexion, "query_citas_usuario", [$id_usuario]);

            $citas = [];
            while ($row = pg_fetch_assoc($result)) {
                $citas[] = $row;
            }

            echo json_encode($citas);
            exit;
        } else {
            echo json_encode(['error' => 'Datos insuficientes proporcionados']);
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar una cita
        if (!isset($data['id_cita']) || !is_numeric($data['id_cita'])) {
            echo json_encode(['error' => 'ID de cita no válido']);
            exit;
        }

        $id_cita = $data['id_cita'];

        $query = "DELETE FROM cita WHERE id_cita = $1";
        $result = pg_prepare($conexion, "query_eliminar_cita", $query);
        $result = pg_execute($conexion, "query_eliminar_cita", [$id_cita]);

        if ($result) {
            echo json_encode(['mensaje' => 'Cita eliminada correctamente']);
        } else {
            echo json_encode(['error' => 'Error al eliminar la cita']);
        }
    } else {
        echo json_encode(['error' => 'Método no soportado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
