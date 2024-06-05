<?php
header('Content-Type: application/json');

// Conectar a la base de datos
$conexion = pg_connect("host=localhost dbname=gocan user=postgres password=admin");
if (!$conexion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos"]);
    exit;
}

// Recibir los datos del formulario
$nombre_mascota = $_POST['nombre_mascota'] ?? '';
$edad = $_POST['edad'] ?? 0;
$periodo = $_POST['period'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$raza = $_POST['raza'] ?? '';
$nombre_propietario = $_POST['nombre_propietario'] ?? '';

// Convertir edad a días, meses o años según el periodo
$edad_year = 0;
$edad_month = 0;
$edad_day = 0;

switch ($periodo) {
    case 'dia':
        $edad_day = $edad;
        break;
    case 'mes':
        $edad_month = $edad;
        break;
    case 'ano':
        $edad_year = $edad;
        break;
    default:
        echo json_encode(["estado" => "error", "mensaje" => "Periodo no válido"]);
        exit;
}

// Validar que los campos no estén vacíos
if (empty($nombre_mascota) || empty($tipo) || empty($raza) || empty($nombre_propietario) || !is_numeric($edad)) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios y la edad debe ser un número válido"]);
    exit;
}

// Verificar si el nombre del propietario existe
$query = "SELECT id_usuario FROM usuario WHERE nombre = $1";
$result = pg_query_params($conexion, $query, [$nombre_propietario]);

if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);
    $id_usuario = $row['id_usuario'];

    // Iniciar una transacción
    pg_query($conexion, 'BEGIN');

    // Insertar la mascota en la tabla mascota
    $insert_mascota_query = "INSERT INTO mascota (nombre_mascota, edad_year, edad_month, edad_day, id_usuario, tipo, raza) 
                             VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id_mascota";
    $insert_mascota_result = pg_query_params($conexion, $insert_mascota_query, [$nombre_mascota, $edad_year, $edad_month, $edad_day, $id_usuario, $tipo, $raza]);

    if ($insert_mascota_result) {
        // Confirmar la transacción
        pg_query($conexion, 'COMMIT');
        echo json_encode(["estado" => "success", "mensaje" => "Mascota registrada exitosamente"]);
    } else {
        // Revertir la transacción
        pg_query($conexion, 'ROLLBACK');
        echo json_encode(["estado" => "error", "mensaje" => "Error al registrar la mascota"]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "El propietario no existe"]);
}
?>
