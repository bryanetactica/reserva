<?php
require 'database.php'; // Archivo de conexión

try {
    // Consulta para obtener todas las reservas
    $stmt = $db->prepare("SELECT email, seat_number FROM seats");
    $stmt->execute();
    $seats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si se accede directamente, mostrar la tabla en HTML con buscador y contador
    if (php_sapi_name() !== 'cli') {
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reservas de Asientos</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f4f4f4;
                }
                #searchInput {
                    width: 100%;
                    padding: 10px;
                    margin-bottom: 10px;
                    border: 1px solid #ddd;
                    font-size: 16px;
                }
                #counter {
                    margin-bottom: 10px;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <h1>Reservas de Asientos</h1>
            <input type='text' id='searchInput' placeholder='Buscar por email o número de asiento...' onkeyup='filterTable()'>
            <div id='counter'></div>";

        if (count($seats) > 0) {
            echo "<table id='seatsTable'>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Número de Asiento</th>
                    </tr>
                </thead>
                <tbody>";

            foreach ($seats as $seat) {
                echo "<tr>
                    <td>" . htmlspecialchars($seat['email']) . "</td>
                    <td>" . htmlspecialchars($seat['seat_number']) . "</td>
                </tr>";
            }

            echo "</tbody>
            </table>";
        } else {
            echo "<p>No se encontraron reservas.</p>";
        }

        echo "<script>
            function filterTable() {
                let input = document.getElementById('searchInput');
                let filter = input.value.toLowerCase();
                let table = document.getElementById('seatsTable');
                let tr = table.getElementsByTagName('tr');
                let visibleCount = 0;

                for (let i = 1; i < tr.length; i++) {
                    let tdEmail = tr[i].getElementsByTagName('td')[0];
                    let tdSeat = tr[i].getElementsByTagName('td')[1];
                    if (tdEmail || tdSeat) {
                        let emailText = tdEmail.textContent || tdEmail.innerText;
                        let seatText = tdSeat.textContent || tdSeat.innerText;
                        if (emailText.toLowerCase().indexOf(filter) > -1 || seatText.toLowerCase().indexOf(filter) > -1) {
                            tr[i].style.display = '';
                            visibleCount++;
                        } else {
                            tr[i].style.display = 'none';
                        }
                    }
                }
                document.getElementById('counter').innerText = 'Resultados encontrados: ' + visibleCount;
            }

            // Mostrar el total inicial de resultados
            document.getElementById('counter').innerText = 'Resultados encontrados: ' + (document.getElementById('seatsTable').getElementsByTagName('tr').length - 1);
        </script>";

        echo "</body></html>";
    } else {
        // Respuesta JSON para solicitudes AJAX
        header("Content-Type: application/json");
        echo json_encode(["success" => true, "data" => $seats]);
    }
} catch (Exception $e) {
    if (php_sapi_name() !== 'cli') {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
