<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

include 'conexion.php'; // Asegúrate de que `conexion.php` devuelve `$pdo`

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['email'])) {
    $email = $data['email'];

    try {
        // Consulta para verificar si el correo ya existe
        $sql = "SELECT COUNT(*) AS total FROM usuario WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $row['total'];

        // Verificar si el correo ya está registrado
        if ($total > 0) {
            echo json_encode(["estado" => "error", "mensaje" => "El correo ya está registrado"]);
        } else {
            echo json_encode(["estado" => "success", "mensaje" => "El correo está disponible"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["estado" => "error", "mensaje" => "Error en la consulta: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan campos requeridos"]);
}
?>
