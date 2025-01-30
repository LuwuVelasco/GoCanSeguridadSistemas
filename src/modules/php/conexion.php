<?php
// Parámetros de conexión a la base de datos
$host = 'dpg-cudd7utds78s73e1spvg-a.oregon-postgres.render.com';
$dbname = 'gocan';
$user = 'admin';
$password = 'oVRI7HAmMceeD1yJfmgrcykinmuc9aYz';
$port = '5432';

try {
    // Crear la conexión con PDO
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a la base de datos: " . $e->getMessage()]);
    exit;
}

// Retornar la conexión para ser usada en otros scripts
return $pdo;
?>
