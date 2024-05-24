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

    // Asumiendo que los datos del producto son enviados como JSON en el cuerpo de la solicitud POST
    $data = json_decode(file_get_contents("php://input"), true);
    // Extraer datos del JSON
    $propietario = $data['propietario'];
    $servicio = $data['servicio'];
    $doctor = $data['doctor']; // Asumiendo que el nombre del doctor es enviado
    $fecha = $data['fecha'];
    $hora = $data['horario'];
    $id_usuario = $data['id_usuario'];

    // Obtener el id_doctor basado en el nombre del doctor
    $stmt = $conn->prepare("SELECT id_doctores FROM doctores WHERE nombre = :nombre");
    $stmt->bindParam(':nombre', $doctor);
    $stmt->execute();

    // Verificar si se encontró un doctor con el nombre proporcionado
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_doctor = $row['id_doctores'];

        // Preparar y ejecutar la inserción en la tabla cita
        $stmt = $conn->prepare("INSERT INTO cita (propietario, horario,fecha,servicio,doctor,id_usuario,id_doctor) VALUES (:propietario, :horario, :fecha, :servicio, :doctor,:id_usuario,:id_doctor)");
        $stmt->bindParam(':propietario', $propietario);
        $stmt->bindParam(':horario', $hora);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':servicio', $servicio);
        $stmt->bindParam(':doctor', $doctor);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_doctor', $id_doctor);

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver la cantidad de productos en formato JSON
        echo json_encode($resultado);
        echo "Cita registrada con éxito.";
    } else {
        echo "Doctor no encontrado.";
    }
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
?>