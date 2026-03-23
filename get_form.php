<?php
header("Content-Type: application/json");
require 'database.php';  // Conexión a infecar
require 'database2.php'; // Conexión a mktic

// Lista de correos a excluir de la comparación
$excludedEmails = [
    'digion@infecar.es',
    'moneiba.suarez@infecar.es',
    'lpostigo@gmail.com',
    'idaira.santana@infecar.es',
    'ana.deleon@infecar.es',
    'paulapenatebenitez@gmail.com',
    'emma.galvan@bitboxonline.com'
];

try {
    // Consulta a la base de datos infecar (obtenemos los emails y los números de asiento)
    $stmt1 = $db->prepare("SELECT email, seat_number FROM seats");
    $stmt1->execute();
    $seats = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Extraemos solo los emails de los resultados, los pasamos a minúsculas y excluimos los correos especificados
    $emailsSeats = array_map(function($seat) use ($excludedEmails) {
        return in_array(strtolower($seat['email']), array_map('strtolower', $excludedEmails)) ? null : strtolower($seat['email']);
    }, $seats);

    // Filtramos los correos excluidos (con null después de la comparación)
    $emailsSeats = array_filter($emailsSeats);

    // Consulta a la base de datos mktic (obtenemos los datos_del_firmante)
    $stmt2 = $db2->prepare("
        SELECT s.id, s.lead_id, f.submission_id, f.datos_del_firmante, f.stand
        FROM mrkt_form_submissions s
        INNER JOIN mrkt_form_results_83_expositore f ON f.submission_id = s.id
        WHERE s.form_id = ?
    ");
    
    $formId = '83';
    $stmt2->execute([$formId]);
    $formResults = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Extraemos los datos_del_firmante y los pasamos a minúsculas
    $emailsFormResults = array_map(function($formResult) {
        return strtolower($formResult['datos_del_firmante']);  // Convertimos todos los emails a minúsculas
    }, $formResults);

    // Inicializamos un array para almacenar los emails de seats que no aparecen en datos_del_firmante
    $notMatchingEmails = [];

    // Recorrer todos los emails de seats y verificar si no están en datos_del_firmante
    foreach ($emailsSeats as $emailSeat) {
        if (!in_array($emailSeat, $emailsFormResults)) {
            // Si el email no está en datos_del_firmante, lo agregamos al array
            $notMatchingEmails[] = $emailSeat;
        }
    }

    // Ahora, eliminamos los registros correspondientes a los emails no coincidentes en la tabla seats
    if (!empty($notMatchingEmails)) {
       // Preparamos la consulta para eliminar los registros
       $placeholders = implode(',', array_fill(0, count($notMatchingEmails), '?'));
       $stmtDelete = $db->prepare("DELETE FROM seats WHERE email IN ($placeholders)");
       $stmtDelete->execute($notMatchingEmails);
    }

    // Respuesta combinada con los resultados de las consultas y los emails que no coinciden
    echo json_encode([
        "success" => true,
        "seats" => $seats,  // Resultados de la primera consulta (asientos)
        "formResults" => $formResults,  // Resultados de la segunda consulta (formularios)
        "notMatchingEmails" => array_unique($notMatchingEmails)  // Emails que no coinciden
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
