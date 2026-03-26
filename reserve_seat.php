<?php
header("Content-Type: application/json");
require 'database.php'; // Archivo de conexión

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? null;
$seat_number = $data['seat_number'] ?? null;
$standID = $data['standID'] ?? null;

if (!$email || !$seat_number || !$standID) {
    echo json_encode(["success" => false, "message" => "Email, número de Estand y standID son requeridos."]);
    exit;
}

try {

    // Verificar si el usuario ya tiene un Estand reservado
    $stmt = $db->prepare("SELECT seat_number FROM seats WHERE email = ?");
    $stmt->execute([$email]);
    $existingSeat = $stmt->fetchColumn();

    if ($existingSeat) {
        echo json_encode(["success" => false, "message" => "Ya tienes reservado el Estand: $existingSeat. Para reservar otro estand clickea en el botón verde 'restablecer selección'."]);
        exit;
    }

    // Validación del seat_number según el standID
    $seat_lower = strtolower($seat_number);
    if (($standID == 1 && !str_starts_with($seat_lower, 'm')) ||
        ($standID == 2 && !str_starts_with($seat_lower, 's'))
    ) {
        $message = ($standID == 1) ? "Selecciona un espacio con la letra M." : "Selecciona un espacio con la letra SS.";
        echo json_encode(["success" => false, "message" => $message]);
        exit;
    }

    // Verificar si el Estand ya está ocupado
    $stmt = $db->prepare("SELECT email FROM seats WHERE seat_number = ?");
    $stmt->execute([$seat_number]);
    $occupiedBy = $stmt->fetchColumn();

    if ($occupiedBy) {
        echo json_encode(["success" => false, "message" => "El Estand ya está ocupado."]);
        exit;
    }

    // Reservar el Estand
    $stmt = $db->prepare("INSERT INTO seats (email, seat_number) VALUES (?, ?)");
    $stmt->execute([$email, $seat_number]);

    echo json_encode(["success" => true, "message" => "Estand reservado con éxito."]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
