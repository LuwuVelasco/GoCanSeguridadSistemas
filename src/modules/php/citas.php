<?php
header('Content-Type: application/json');
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['especialidad_id']) && is_numeric($_GET['especialidad_id'])) {
            // Obtener doctores según la especialidad
            $especialidadId = $_GET['especialidad_id'];
            $query = "SELECT nombre FROM doctores WHERE id_especialidad = $1";
            $result = pg_prepare($conexion, "query_doctores", $query);
            $result = pg_execute($conexion, "query_doctores", [$especialidadId]);

            $doctores = [];
            while ($row = pg_fetch_assoc($result)) {
                $doctores[] = $row;
            }

            echo json_encode($doctores ?: []);
            exit;
        } else {
            // Obtener todas las especialidades usando pg_query()
            $query = "SELECT id_especialidad, nombre_especialidad FROM especialidad";
            $result = pg_query($conexion, $query);

            if (!$result) {
                echo json_encode(['error' => true, 'mensaje' => 'Error al obtener especialidades']);
                exit;
            }

            $especialidades = [];
            while ($row = pg_fetch_assoc($result)) {
                $especialidades[] = $row;
            }

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
        $query = "SELECT id_doctores FROM doctores WHERE nombre = $1";
        $result = pg_prepare($conexion, "query_doctor", $query);
        $result = pg_execute($conexion, "query_doctor", [$doctor]);

        if (pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $id_doctor = $row['id_doctores'];

            // Verificar si ya existe una cita en ese horario para el doctor
            $query = "SELECT COUNT(*) as count FROM cita WHERE id_doctor = $1 AND fecha = $2 AND horario = $3";
            $result = pg_prepare($conexion, "query_cita_existente", $query);
            $result = pg_execute($conexion, "query_cita_existente", [$id_doctor, $fecha, $hora]);
            $existingCita = pg_fetch_assoc($result);

            if ($existingCita['count'] > 0) {
                echo json_encode([
                    "error" => true,
                    "mensaje" => "El doctor ya tiene una cita en ese horario."
                ]);
                exit;
            }

            // Insertar nueva cita
            $query = "INSERT INTO cita (propietario, servicio, doctor, id_usuario, id_doctor, fecha, horario) 
                      VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id_cita";
            $result = pg_prepare($conexion, "query_insert_cita", $query);
            $result = pg_execute($conexion, "query_insert_cita", [$propietario, $especialidadNombre, $doctor, $id_usuario, $id_doctor, $fecha, $hora]);

            if ($row = pg_fetch_assoc($result)) {
                echo json_encode([
                    "id_cita" => $row['id_cita'],
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
