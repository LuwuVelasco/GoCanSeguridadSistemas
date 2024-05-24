<?php
$host = "localhost";
$port = "5432";
$dbname = "gocan";
$username = "postgres";
$password = "admin";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";

$data = json_decode(file_get_contents("php://input"));
$id_usuario = $data->id_usuario;

// Intentar crear conexión PDO
try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para obtener solo las citas futuras o citas de hoy con hora futura asociadas al id_usuario
    $stmt = $conn->prepare("
        SELECT propietario, servicio, fecha, horario 
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
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>