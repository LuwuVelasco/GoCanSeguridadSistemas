<?php
header('Content-Type: application/json');
include 'conexion.php';
session_start(); // Para obtener la sesión del usuario logueado

$id_mascota = $_POST['id_mascota'] ?? '';
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

// Verificar si todos los campos están presentes
if (empty($id_mascota) || empty($nombre_mascota) || empty($fecha_nacimiento) || empty($tipo) || empty($raza) || empty($nombre_propietario)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Buscar el propietario por nombre para obtener su ID
$query_usuario = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result_usuario = pg_query_params($conexion, $query_usuario, [$nombre_propietario]);

if ($result_usuario && pg_num_rows($result_usuario) > 0) {
    $row_usuario = pg_fetch_assoc($result_usuario);
    $id_usuario = $row_usuario['id_usuario'];

    // Consultar los datos actuales de la mascota antes de actualizar
    $query_mascota_actual = "SELECT * FROM mascota WHERE id_mascota = $1";
    $result_mascota_actual = pg_query_params($conexion, $query_mascota_actual, [$id_mascota]);

    if ($result_mascota_actual && pg_num_rows($result_mascota_actual) > 0) {
        $mascota_actual = pg_fetch_assoc($result_mascota_actual);

        // Comparar los valores antiguos y nuevos
        $cambios = [];
        if ($mascota_actual['nombre_mascota'] !== $nombre_mascota) {
            $cambios['nombre_mascota'] = [
                'antes' => $mascota_actual['nombre_mascota'],
                'después' => $nombre_mascota
            ];
        }
        if ($mascota_actual['fecha_nacimiento'] !== $fecha_nacimiento) {
            $cambios['fecha_nacimiento'] = [
                'antes' => $mascota_actual['fecha_nacimiento'],
                'después' => $fecha_nacimiento
            ];
        }
        if ($mascota_actual['tipo'] !== $tipo) {
            $cambios['tipo'] = [
                'antes' => $mascota_actual['tipo'],
                'después' => $tipo
            ];
        }
        if ($mascota_actual['raza'] !== $raza) {
            $cambios['raza'] = [
                'antes' => $mascota_actual['raza'],
                'después' => $raza
            ];
        }
        if ($mascota_actual['id_usuario'] !== $id_usuario) {
            $cambios['id_usuario'] = [
                'antes' => $mascota_actual['id_usuario'],
                'después' => $id_usuario
            ];
        }

        // Actualizar los datos de la mascota
        $query = "
            UPDATE mascota
            SET 
                nombre_mascota = $1,
                fecha_nacimiento = $2,
                tipo = $3,
                raza = $4,
                id_usuario = $5
            WHERE id_mascota = $6";

        $params = [$nombre_mascota, $fecha_nacimiento, $tipo, $raza, $id_usuario, $id_mascota];
        $result = pg_query_params($conexion, $query, $params);

        if ($result) {
            // Obtener datos del usuario logueado (quién realiza la acción)
            $idUsuarioActual    = $_SESSION['id_usuario'] ?? null;
            $nombreUsuarioActual= $_SESSION['nombre_usuario'] ?? null;

            // Registrar en el log de aplicación solo si hubo cambios
            if (!empty($cambios)) {
                // Construir el valor para "valor_original"
                $cambioDetalles = [];
                foreach ($cambios as $campo => $valores) {
                    $cambioDetalles[] = "Antes: {$valores['antes']}, Después: {$valores['después']}";
                }
                $valorOriginal = implode("; ", $cambioDetalles); // Unir todos los cambios con "; "
            
                // Construir el valor para "dato_modificado"
                $camposModificados = implode(", ", array_keys($cambios)); // Solo los nombres de los campos
            
                // Registrar en el log de aplicación
                registrarLogAplicacion(
                    $idUsuarioActual,
                    $nombreUsuarioActual,
                    'editar_mascota', // Acción
                    "Se editó la mascota con ID $id_mascota", // Descripción
                    'editar_mascota', // Función afectada
                    $camposModificados, // Lista de campos modificados
                    $valorOriginal // Resumen de los cambios
                );
            }
            

            echo json_encode(["estado" => "success"]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la mascota"]);
        }
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "La mascota no existe"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}

// Función para registrar en log_aplicacion
function registrarLogAplicacion(
    $idUsuarioActual,
    $nombreUsuarioActual,
    $accion,
    $descripcion,
    $funcionAfectada,
    $datoModificado,
    $valorOriginal
) {
    $url = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/registrar_log_aplicacion.php';

    // Construir datos a enviar
    $data = http_build_query([
        'id_usuario'       => $idUsuarioActual,
        'nombre_usuario'   => $nombreUsuarioActual,
        'accion'           => $accion,
        'descripcion'      => $descripcion,
        'funcion_afectada' => $funcionAfectada,
        'dato_modificado'  => $datoModificado,
        'valor_original'   => $valorOriginal
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $data,
        ],
    ];
    $context = stream_context_create($options);

    // Llamada al archivo que hace la inserción real en log_aplicacion
    file_get_contents($url, false, $context);
}
?>
