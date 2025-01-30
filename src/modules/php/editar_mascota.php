<?php
header('Content-Type: application/json');
session_start();
$pdo = include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

try {
    // Obtener los datos enviados en la solicitud (JSON o x-www-form-urlencoded)
    $data = $_POST ?: json_decode(file_get_contents("php://input"), true);

    $id_mascota = $data['id_mascota'] ?? null;
    $nombre_mascota = $data['nombre_mascota'] ?? null;
    $fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
    $tipo = $data['tipo'] ?? null;
    $raza = $data['raza'] ?? null;
    $nombre_propietario = $data['nombre_propietario'] ?? null;

    // Verificar si todos los campos están presentes y no están vacíos
    if (!$id_mascota || !$nombre_mascota || !$fecha_nacimiento || !$tipo || !$raza || !$nombre_propietario) {
        echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
        exit;
    }

    // Buscar el propietario por nombre para obtener su ID
    $query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = :nombre";
    $stmt = $pdo->prepare($query_usuario);
    $stmt->bindParam(':nombre', $nombre_propietario, PDO::PARAM_STR);
    $stmt->execute();
    $row_usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row_usuario) {
        echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
        exit;
    }

    $id_usuario = $row_usuario['id_usuario'];

    // Consultar los datos actuales de la mascota antes de actualizar
    $query_mascota_actual = "SELECT * FROM mascota WHERE id_mascota = :id_mascota";
    $stmt = $pdo->prepare($query_mascota_actual);
    $stmt->bindParam(':id_mascota', $id_mascota, PDO::PARAM_INT);
    $stmt->execute();
    $mascota_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mascota_actual) {
        echo json_encode(["estado" => "error", "mensaje" => "La mascota no existe"]);
        exit;
    }

    // Comparar los valores antiguos y nuevos
    $cambios = [];
    if ($mascota_actual['nombre_mascota'] !== $nombre_mascota) {
        $cambios['nombre_mascota'] = ["antes" => $mascota_actual['nombre_mascota'], "después" => $nombre_mascota];
    }
    if ($mascota_actual['fecha_nacimiento'] !== $fecha_nacimiento) {
        $cambios['fecha_nacimiento'] = ["antes" => $mascota_actual['fecha_nacimiento'], "después" => $fecha_nacimiento];
    }
    if ($mascota_actual['tipo'] !== $tipo) {
        $cambios['tipo'] = ["antes" => $mascota_actual['tipo'], "después" => $tipo];
    }
    if ($mascota_actual['raza'] !== $raza) {
        $cambios['raza'] = ["antes" => $mascota_actual['raza'], "después" => $raza];
    }
    if ($mascota_actual['id_usuario'] !== $id_usuario) {
        $cambios['id_usuario'] = ["antes" => $mascota_actual['id_usuario'], "después" => $id_usuario];
    }

    // Actualizar los datos de la mascota
    $query = "
        UPDATE mascota
        SET 
            nombre_mascota = :nombre_mascota,
            fecha_nacimiento = :fecha_nacimiento,
            tipo = :tipo,
            raza = :raza,
            id_usuario = :id_usuario
        WHERE id_mascota = :id_mascota
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nombre_mascota', $nombre_mascota, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':raza', $raza, PDO::PARAM_STR);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(':id_mascota', $id_mascota, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Obtener datos del usuario logueado (quién realiza la acción)
        $idUsuarioActual = $_SESSION['id_usuario'] ?? null;
        $nombreUsuarioActual = $_SESSION['nombre_usuario'] ?? null;

        // Registrar en el log de aplicación solo si hubo cambios
        if (!empty($cambios)) {
            $cambioDetalles = [];
            foreach ($cambios as $campo => $valores) {
                $cambioDetalles[] = "Antes: {$valores['antes']}, Después: {$valores['después']}";
            }
            $valorOriginal = implode("; ", $cambioDetalles);
            $camposModificados = implode(", ", array_keys($cambios));

            // Registrar en el log de aplicación
            registrarLogAplicacion(
                $idUsuarioActual,
                $nombreUsuarioActual,
                'editar_mascota',
                "Se editó la mascota con ID $id_mascota",
                'editar_mascota',
                $camposModificados,
                $valorOriginal
            );
        }

        echo json_encode(["estado" => "success", "mensaje" => "Mascota actualizada exitosamente"]);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
    }
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la base de datos: " . $e->getMessage()]);
}

// Función para registrar en log_aplicacion
function registrarLogAplicacion($idUsuarioActual, $nombreUsuarioActual, $accion, $descripcion, $funcionAfectada, $datoModificado, $valorOriginal) {
    $url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_log_aplicacion.php';

    $data = http_build_query([
        'id_usuario' => $idUsuarioActual,
        'nombre_usuario' => $nombreUsuarioActual,
        'accion' => $accion,
        'descripcion' => $descripcion,
        'funcion_afectada' => $funcionAfectada,
        'dato_modificado' => $datoModificado,
        'valor_original' => $valorOriginal
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}
?>
