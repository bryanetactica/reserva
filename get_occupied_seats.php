<?php
header("Content-Type: application/json");
require 'database.php'; // Archivo de conexión

try {
    // Obtener todos los asientos ocupados
    $stmt = $db->prepare("SELECT email, seat_number FROM seats");
    $stmt->execute();
    $occupiedSeats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Comprobar si el usuario actual tiene un asiento reservado
    $email = $_GET['email'] ?? null;
    $userSeat = null;

    if ($email) {
        $stmt = $db->prepare("SELECT seat_number FROM seats WHERE email = ?");
        $stmt->execute([$email]);
        $userSeat = $stmt->fetchColumn();
    }

    echo json_encode([
        "success" => true,
        "occupiedSeats" => $occupiedSeats,
        "userSeat" => $userSeat,
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
