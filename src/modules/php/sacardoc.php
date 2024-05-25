<?php
header('Content-Type: application/json');

$host = "localhost";
$port = "5432";
$dbname = "gocan";
$username = "postgres";
$password = "admin";
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$username;password=$password";

try {
    $conn = new PDO($dsn);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $especialidad = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';

    if ($especialidad == 'Sin especificar' || $especialidad == '') {
        $stmt = $conn->query("SELECT nombre FROM doctores");
    } else {
        $stmt = $conn->prepare("SELECT nombre FROM doctores WHERE especialidad = :especialidad");
        $stmt->execute(['especialidad' => $especialidad]);
    }

    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($doctores);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}
?>