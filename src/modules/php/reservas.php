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

    // Consulta para obtener solo las citas futuras comparando fecha y hora con NOW()
    $stmt = $conn->prepare("SELECT propietario, servicio, fecha, horario FROM cita WHERE (fecha || ' ' || horario)::timestamp > NOW()");
    $stmt->execute();

    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($citas);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>