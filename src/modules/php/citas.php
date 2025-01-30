<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['especialidad_id']) && is_numeric($_GET['especialidad_id'])) {
            // Obtener doctores según la especialidad
            $especialidadId = $_GET['especialidad_id'];
            $stmt = $pdo->prepare("SELECT nombre FROM doctores WHERE id_especialidad = :especialidad_id");
            $stmt->bindParam(':especialidad_id', $especialidadId, PDO::PARAM_INT);
            $stmt->execute();
            $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($doctores ?: []); // Devolver un array vacío si no hay doctores
            exit;
        } elseif (!isset($_GET['especialidad_id'])) {
            // Obtener todas las especialidades
            $stmt = $pdo->query("SELECT id_especialidad, nombre_especialidad FROM especialidad");
            $especialidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($especialidades ?: []); // Devolver un array vacío si no hay especialidades
            exit;
        } else {
            echo json_encode(["error" => true, "mensaje" => "ID de especialidad inválido o no proporcionado."]);
            exit;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['propietario'], $data['especialidadId'], $data['especialidadNombre'], $data['doctor'], $data['id_usuario'], $data['fecha'], $data['horario'])) {
            echo json_encode(["error" => true, "mensaje" => "Faltan datos requeridos."]);
            exit;
        }

        $propietario = $data['propietario'];
        $especialidadId = $data['especialidadId'];
        $especialidadNombre = $data['especialidadNombre'];
        $doctor = $data['doctor'];
        $id_usuario = $data['id_usuario'];
        $fecha = $data['fecha'];
        $hora = $data['horario'];

        // Obtener ID del doctor
        $stmt = $pdo->prepare("SELECT id_doctores FROM doctores WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $doctor, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_doctor = $row['id_doctores'];

            // Verificar disponibilidad del doctor
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cita WHERE id_doctor = :id_doctor AND fecha = :fecha AND horario = :horario");
            $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':horario', $hora, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                echo json_encode(["error" => true, "mensaje" => "El doctor ya tiene una cita en ese horario."]);
            } else {
                // Registrar la cita
                $stmt = $pdo->prepare("INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario) 
                                       VALUES (:propietario, :servicio, :doctor, :id_usuario, :id_doctor, :fecha, :horario)");
                $stmt->bindParam(':propietario', $propietario, PDO::PARAM_STR);
                $stmt->bindParam(':servicio', $especialidadNombre, PDO::PARAM_STR);
                $stmt->bindParam(':doctor', $doctor, PDO::PARAM_STR);
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(':id_doctor', $id_doctor, PDO::PARAM_INT);
                $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
                $stmt->bindParam(':horario', $hora, PDO::PARAM_STR);
                $stmt->execute();

                // Obtener el ID de la cita recién creada
                $id_cita = $pdo->lastInsertId();
                echo json_encode(["id_cita" => $id_cita, "mensaje" => "Cita registrada con éxito."]);
            }
        } else {
            echo json_encode(["error" => true, "mensaje" => "Doctor no encontrado."]);
        }
    } else {
        echo json_encode(["error" => true, "mensaje" => "Método de solicitud no soportado."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => true, "mensaje" => "Error de conexión: " . $e->getMessage()]);
}
?>
