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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['especialidad_id'])) {
            $especialidadId = $_GET['especialidad_id'];
            $stmt = $conn->prepare("SELECT nombre FROM doctores WHERE id_especialidad = :especialidad_id");
            $stmt->execute(['especialidad_id' => $especialidadId]);
            $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($doctores);
        } else {
            $stmt = $conn->query("SELECT id_especialidad, nombre_especialidad FROM especialidad");
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($especialidades);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $propietario = $data['propietario'];
        $especialidadId = $data['especialidadId'];
        $especialidadNombre = $data['especialidadNombre'];
        $doctor = $data['doctor'];
        $id_usuario = $data['id_usuario'];
        $fecha = $data['fecha'];
        $hora = $data['horario'];

        $stmt = $conn->prepare("SELECT id_doctores FROM doctores WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $doctor);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_doctor = $row['id_doctores'];

            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cita WHERE id_doctor = :id_doctor AND fecha = :fecha AND horario = :horario");
            $stmt->bindParam(':id_doctor', $id_doctor);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':horario', $hora);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "El doctor ya tiene una cita en ese horario."
                ]);
            } else {
                $stmt = $conn->prepare("INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha,horario) VALUES (:propietario, :servicio, :doctor, :id_usuario, :id_doctor, :fecha, :horario)");
                $stmt->bindParam(':propietario', $propietario);
                $stmt->bindParam(':servicio', $especialidadNombre);
                $stmt->bindParam(':doctor', $doctor);
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':id_doctor', $id_doctor);
                $stmt->bindParam(':fecha', $fecha);
                $stmt->bindParam(':horario', $hora);

                $stmt->execute();

                $id_cita = $conn->lastInsertId();
                echo json_encode([
                    "id_cita" => $id_cita,
                    "mensaje" => "Cita registrada con éxito."
                ]);
            }
        } else {
            echo json_encode([
                "error" => true,
                "mensaje" => "Doctor no encontrado."
            ]);
        }
    } else {
        echo json_encode([
            "error" => true,
            "mensaje" => "Método de solicitud no soportado."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error de conexión: " . $e->getMessage()
    ]);
}
?>