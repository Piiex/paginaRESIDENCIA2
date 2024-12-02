<?php
// Archivo: db.php

$host = "localhost";    // Cambia por el nombre del servidor en Hostinger
$user = "u934185700_case";   // Tu usuario de base de datos 
$password = "Runaway11._"; // Tu clave de base de datos 
$database = "u934185700_admin";     // El nombre de la base de datos 

// Crear la conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
