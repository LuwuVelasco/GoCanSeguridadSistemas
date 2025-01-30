<?php
include 'conexion.php';
$data = json_decode(file_get_contents("php://input"));

// Intentar crear conexión PDO
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar si es una solicitud para registrar o cargar citas
        if (isset($data->fecha) && isset($data->horario)) {
            // Registrar una nueva cita
            $fecha = $data->fecha;
            $horario = $data->horario;

            // Validar la fecha y hora
            $fechaHoraIngresada = new DateTime("$fecha $horario");
            $now = new DateTime();

            if ($fechaHoraIngresada <= $now) {
                echo json_encode(['error' => 'No puedes registrar una cita en una fecha u hora pasada.']);
                exit;
            }

            // Registrar la cita en la base de datos (puedes añadir más lógica aquí)
            echo json_encode(['mensaje' => 'Cita registrada correctamente']);
        } elseif (isset($data->id_usuario)) {
            // Cargar citas del usuario
            $id_usuario = $data->id_usuario;

            // Consulta para obtener solo las citas futuras o citas de hoy con hora futura asociadas al id_usuario
            $stmt = $conexion->prepare("
                SELECT id_cita, propietario, servicio, fecha, horario 
                FROM cita 
                WHERE id_usuario = :id_usuario AND 
                (fecha > CURRENT_DATE OR 
                (fecha = CURRENT_DATE AND horario > CURRENT_TIME))
            ");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($citas);
        } else {
            echo json_encode(['error' => 'Datos insuficientes proporcionados']);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar reserva
        $id_cita = $data->id_cita;

        $stmt = $conexion->prepare("DELETE FROM cita WHERE id_cita = :id_cita");
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['mensaje' => 'Cita eliminada correctamente']);
    } else {
        echo json_encode(['error' => 'Método no soportado']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
