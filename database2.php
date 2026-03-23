<?php
// Configuración de la base de datos
$host = 'localhost'; // Cambia a tu host (ejemplo: 127.0.0.1 o una dirección IP remota)
$dbname = 'infecar'; // Reemplaza con el nombre de tu base de datos
$username = 'root'; // Reemplaza con el usuario de tu base de datos
$password = 'M4rk3t1c?2017'; // Reemplaza con la contraseña del usuario

try {
    // Crear la conexión PDO
    $db2 = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configurar el modo de error de PDO
    $db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Mostrar un mensaje de error en caso de falla
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
