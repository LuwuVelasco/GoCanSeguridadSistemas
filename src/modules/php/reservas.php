<?php
header('Content-Type: application/json');
include 'conexion.php'; // Debe devolver la variable $pdo (PDO)
date_default_timezone_set('America/La_Paz');

try {
    $data = json_decode(file_get_contents("php://input"));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($data->fecha, $data->horario, $data->id_usuario, $data->propietario, $data->servicio)) {
            // Registrar una nueva cita
            $fecha = $data->fecha;
            $horario = $data->horario;
            $id_usuario = $data->id_usuario;
            $propietario = $data->propietario;
            $servicio = $data->servicio;

            // Validar la fecha y hora
            $fechaHoraIngresada = new DateTime("$fecha $horario");
            $now = new DateTime();

            if ($fechaHoraIngresada <= $now) {
                echo json_encode(['estado' => 'error', 'mensaje' => 'No puedes registrar una cita en una fecha u hora pasada.']);
                exit;
            }

            // Insertar la cita en la base de datos
            $stmt = $pdo->prepare("
                INSERT INTO cita (id_usuario, propietario, servicio, fecha, horario)
                VALUES (:id_usuario, :propietario, :servicio, :fecha, :horario)
            ");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
            $stmt->bindParam(':servicio', $servicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':horario', $horario, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode(['estado' => 'success', 'mensaje' => 'Cita registrada correctamente']);
        
        } elseif (isset($data->id_usuario)) {
            // Cargar citas del usuario
            $id_usuario = $data->id_usuario;

            // Consulta para obtener solo las citas futuras
            $stmt = $pdo->prepare("
                SELECT id_cita, propietario, servicio, fecha, horario 
                FROM cita 
                WHERE id_usuario = :id_usuario 
                AND (fecha > CURRENT_DATE OR (fecha = CURRENT_DATE AND horario > CURRENT_TIME))
                ORDER BY fecha, horario
            ");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['estado' => 'success', 'citas' => $citas]);

        } else {
            echo json_encode(['estado' => 'error', 'mensaje' => 'Datos insuficientes proporcionados']);
        }
    
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar reserva
        if (!isset($data->id_cita)) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'ID de cita no proporcionado']);
            exit();
        }

        $id_cita = $data->id_cita;

        $stmt = $pdo->prepare("DELETE FROM cita WHERE id_cita = :id_cita");
        $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(['estado' => 'success', 'mensaje' => 'Cita eliminada correctamente']);

    } else {
        echo json_encode(['estado' => 'error', 'mensaje' => 'Método no soportado']);
    }

} catch (PDOException $e) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
