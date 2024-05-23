<?php
$host = "localhost";
$port = "5432";
$dbname = "gocan";
$username = "postgres";
$password = "admin";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";

// Intentar crear conexión PDO
try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("
        SELECT propietario, servicio, fecha, horario 
        FROM cita 
        WHERE fecha > CURRENT_DATE 
        OR (fecha = CURRENT_DATE AND horario > CURRENT_TIME)
        OR (fecha = CURRENT_DATE AND horario = '00:00:00' AND CURRENT_TIME < '00:00:00')
    ");
    $stmt->execute();

    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($citas);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>