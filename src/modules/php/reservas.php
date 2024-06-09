<?php
$host = "localhost";
$port = "5433";
$dbname = "gocan";
$username = "postgres";
$password = "admin";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";

$data = json_decode(file_get_contents("php://input"));

// Intentar crear conexión PDO
try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener reservas
        $id_usuario = $data->id_usuario;

        // Consulta para obtener solo las citas futuras o citas de hoy con hora futura asociadas al id_usuario
        $stmt = $conn->prepare("
            SELECT id_cita, propietario, servicio, fecha, horario 
            FROM cita 
            WHERE id_usuario = :id_usuario AND 
            (fecha > CURRENT_DATE OR 
            (fecha = CURRENT_DATE AND horario > CURRENT_TIME) OR 
            (fecha = CURRENT_DATE AND horario = '00:00:00' OR CURRENT_TIME < '00:00:00'))
        ");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($citas);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Eliminar reserva
        $id_cita = $data->id_cita;

        $stmt = $conn->prepare("DELETE FROM cita WHERE id_cita = :id_cita");
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