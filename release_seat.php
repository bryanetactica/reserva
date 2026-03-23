<?php
header("Content-Type: application/json");
require 'database.php'; // Archivo de conexión

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(["success" => false, "message" => "No se proporcionó el email."]);
    exit;
}

try {
    // Eliminar el asiento reservado del usuario
    $stmt = $db->prepare("DELETE FROM seats WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Reserva liberada exitosamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "No se encontró una reserva para este email."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
