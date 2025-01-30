<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($data['fecha'], $data['horario'])) {
            // Registrar una nueva cita
            $fecha = $data['fecha'];
            $horario = $data['horario'];

            // Validar la fecha y hora
            $fechaHoraIngresada = new DateTime("$fecha $horario");
            $now = new DateTime();

            if ($fechaHoraIngresada <= $now) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'No puedes registrar una cita en una fecha u hora pasada.']);
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
                WHERE id_usuario = :id_usuario 
                AND (fecha > CURRENT_DATE OR (fecha = CURRENT_DATE AND horario > CURRENT_TIME))
            ";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        $query = "DELETE FROM cita WHERE id_cita = :id_cita";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensaje' => 'Cita eliminada correctamente']);
        } else {
            echo json_encode(['error' => 'Error al eliminar la cita']);
        }
    } else {
        echo json_encode(['estado' => 'error', 'mensaje' => 'Método no soportado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
