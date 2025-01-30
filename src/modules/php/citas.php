<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['especialidad_id']) && is_numeric($_GET['especialidad_id'])) {
            // Obtener doctores según la especialidad
            $especialidadId = $_GET['especialidad_id'];
            $query = "SELECT nombre FROM doctores WHERE id_especialidad = :especialidad_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':especialidad_id', $especialidadId, PDO::PARAM_INT);
            $stmt->execute();
            $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($doctores ?: []);
            exit;
        } else {
            // Obtener todas las especialidades
            $query = "SELECT id_especialidad, nombre_especialidad FROM especialidad";
            $stmt = $pdo->query($query);
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($especialidades ?: []);
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['propietario'], $data['especialidadId'], $data['especialidadNombre'], $data['doctor'], $data['id_usuario'], $data['fecha'], $data['horario'])) {
            echo json_encode(["error" => true, "mensaje" => "Faltan datos requeridos."]);
            exit;
        }

        $propietario = $data['propietario'];
        $especialidadNombre = $data['especialidadNombre'];
        $doctor = $data['doctor'];
        $id_usuario = $data['id_usuario'];
        $fecha = $data['fecha'];
        $hora = $data['horario'];

        // Obtener ID del doctor
        $query = "SELECT id_doctores FROM doctores WHERE nombre = :doctor";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':doctor', $doctor, PDO::PARAM_STR);
        $stmt->execute();
        $doctorData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($doctorData) {
            $id_doctor = $doctorData['id_doctores'];

            // Verificar si ya existe una cita en ese horario para el doctor
            $query = "SELECT COUNT(*) as count FROM cita WHERE id_doctor = :id_doctor AND fecha = :fecha AND horario = :horario";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':horario', $hora, PDO::PARAM_STR);
            $stmt->execute();
            $existingCita = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingCita['count'] > 0) {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "El doctor ya tiene una cita en ese horario."
                ]);
                exit;
            }

            // Insertar nueva cita
            $query = "INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario) 
                      VALUES (:propietario, :especialidadNombre, :doctor, :id_usuario, :id_doctor, :fecha, :horario) RETURNING id_cita";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
            $stmt->bindParam(':especialidadNombre', $especialidadNombre, PDO::PARAM_STR);
            $stmt->bindParam(':doctor', $doctor, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':horario', $hora, PDO::PARAM_STR);
            $stmt->execute();
            $id_cita = $stmt->fetchColumn();

            if ($id_cita) {
                echo json_encode([
                    "id_cita" => $id_cita,
                    "mensaje" => "Cita registrada con éxito."
                ]);
            } else {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "No se pudo registrar la cita."
                ]);
            }
        } else {
            echo json_encode(["error" => true, "mensaje" => "Doctor no encontrado."]);
        }
    } else {
        echo json_encode(["error" => true, "mensaje" => "Método de solicitud no soportado."]);
    }
} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "mensaje" => "Error del servidor: " . $e->getMessage()
    ]);
}
?>
