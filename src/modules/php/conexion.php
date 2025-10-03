<?php
// C:\xampp\htdocs\GoCanSeguridadSistemas\src\modules\php\conexion.php
declare(strict_types=1);

// Ajusta si tu DB se llama distinto
$host = "localhost";
$dbname = "gocan";     // <-- tu base
$user = "postgres";
$password = "admin";
$port = "5432";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;  // <--- IMPORTANTE
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(["estado"=>"error","mensaje"=>"No se pudo conectar a la base de datos"]);
    exit;
}
