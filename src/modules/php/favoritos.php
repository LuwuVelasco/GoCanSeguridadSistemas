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

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id_usuario'])) {
        // Obtener la cantidad de productos asociados al id_usuario
        $id_usuario = $_GET['id_usuario'];
        $stmt = $conn->prepare("SELECT COUNT(*) AS cantidad FROM producto WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver la cantidad de productos en formato JSON
        echo json_encode($resultado);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener productos favoritos del usuario
        $data = json_decode(file_get_contents("php://input"));
        $id_usuario = $data->id_usuario;

        $stmt = $conn->prepare("SELECT nombre, descripcion, precio FROM producto WHERE id_usuario = :id_usuario");
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($favoritos);
    } else {
        echo json_encode(['error' => 'Método no soportado']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>