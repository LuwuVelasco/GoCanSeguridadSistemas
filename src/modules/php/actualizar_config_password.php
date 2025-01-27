<?php
header('Content-Type: application/json');
include 'conexion.php'; // Asegúrate de que este archivo defina la variable $pdo
date_default_timezone_set('America/La_Paz');

try {
    if (!isset($_POST['tiempo_vida_util'], $_POST['numero_historico'])) {
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Faltan datos obligatorios: tiempo de vida útil y número histórico.",
        ]);
        exit;
    }

    $tiempoVidaUtil = (int) $_POST['tiempo_vida_util'];
    $numeroHistorico = (int) $_POST['numero_historico'];

    if ($tiempoVidaUtil <= 0 || $numeroHistorico <= 0) {
        echo json_encode([
            "estado" => "error",
            "mensaje" => "Los valores deben ser mayores a 0.",
        ]);
        exit;
    }

    // Verificar si existe una configuración con ID 1
    $sqlSelect = "SELECT COUNT(*) AS total FROM configuracion_passwords WHERE id_configuracion = 1";
    $stmtSelect = $pdo->query($sqlSelect);
    $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if ($row['total'] > 0) {
        // Actualizar la configuración existente
        $sqlUpdate = "
            UPDATE configuracion_passwords
            SET tiempo_vida_util = :tiempo_vida_util,
                numero_historico = :numero_historico,
                fecha_configuracion = NOW()
            WHERE id_configuracion = 1
        ";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':tiempo_vida_util', $tiempoVidaUtil, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':numero_historico', $numeroHistorico, PDO::PARAM_INT);
        $stmtUpdate->execute();
    } else {
        // Insertar nueva configuración con ID 1
        $sqlInsert = "
            INSERT INTO configuracion_passwords (id_configuracion, tiempo_vida_util, numero_historico, fecha_configuracion)
            VALUES (1, :tiempo_vida_util, :numero_historico, NOW())
        ";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->bindParam(':tiempo_vida_util', $tiempoVidaUtil, PDO::PARAM_INT);
        $stmtInsert->bindParam(':numero_historico', $numeroHistorico, PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    // Registrar log de usuario
    $logSql = "
        INSERT INTO log_usuarios (id_usuario, accion, descripcion, fecha_hora)
        VALUES (NULL, 'configuracion_cambio', 'Cambio en la configuración de contraseñas', NOW())
    ";
    $pdo->exec($logSql);

    echo json_encode([
        "estado" => "success",
        "mensaje" => "Configuración de contraseñas actualizada exitosamente."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Error al actualizar la configuración: " . $e->getMessage()
    ]);
}