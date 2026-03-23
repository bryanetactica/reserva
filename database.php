<?php
// Configuración de la base de datos
$host = '10.132.0.59'; // Cambia a tu host (ejemplo: 127.0.0.1 o una dirección IP remota)
$dbname = 'marketic-facturacion-bryan'; // Reemplaza con el nombre de tu base de datos
$username = 'root'; // Reemplaza con el usuario de tu base de datos
$password = 'M4rk3t1c?2017'; // Reemplaza con la contraseña del usuario

try {
    // Crear la conexión PDO
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configurar el modo de error de PDO
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mostrar un mensaje de error en caso de falla
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
