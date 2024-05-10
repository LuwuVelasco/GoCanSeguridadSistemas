<?php
$servername = "localhost";
$username = "postgres";
$password = "admin";
$dbname = "gocan";

// Intentar crear conexión PDO
try {
    $conn = new PDO("pgsql:host=$servername;dbname=$dbname", $username, $password);
    echo "Conexión establecida";  // Verifica que la conexión es exitosa
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recolectar datos del POST
    $propietario = $_POST['propietario'] ?? 'No especificado';
    $servicio = $_POST['servicio'] ?? 'No especificado';
    $doctor = $_POST['doctor'] ?? 'No especificado';
    $fecha = $_POST['fecha'] ?? 'No especificado';
    $hora = $_POST['hora'] ?? 'No especificado';

    // Informar los datos recolectados
    echo "Datos recibidos: Propietario - $propietario, Servicio - $servicio, Doctor - $doctor, Fecha - $fecha, Hora - $hora";

    // Preparar y ejecutar la inserción
    $stmt = $conn->prepare("INSERT INTO cita (propietario, servicio, doctor, fecha, horario) VALUES (:propietario, :servicio, :doctor, :fecha, :horario)");
    $stmt->bindParam(':propietario', $propietario);
    $stmt->bindParam(':servicio', $servicio);
    $stmt->bindParam(':doctor', $doctor);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':horario', $hora);

    if ($stmt->execute()) {
        echo "Cita registrada correctamente";
    } else {
        echo "Error al registrar la cita";
    }
} catch (PDOException $e) {
    echo "Error de conexión o consulta: " . $e->getMessage();
}

// Cerrar la conexión
$conn = null;
