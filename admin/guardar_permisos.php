<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db.php'; // Archivo de conexión a la base de datos

// Obtener el ID del docente
$docente_id = $_POST['id'];

// Obtener permisos
$permissions = [
    'tutorias' => isset($_POST['tutorias']) ? 1 : 0,
    'asesorias' => isset($_POST['asesorias']) ? 1 : 0,
    'educacion_dual' => isset($_POST['educacion_dual']) ? 1 : 0,
];

// Actualizar permisos en la base de datos
$query = "UPDATE usuarios SET permissions = ? WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([json_encode($permissions), $docente_id]);

header("Location: admin_dashboard.php"); // Redirigir a la página de administración
exit();
