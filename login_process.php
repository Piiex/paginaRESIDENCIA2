<?php
// Archivo: login_process.php
session_start();
include 'db.php';

// Obtener los datos del formulario
$correo = $_POST['correo'];
$clave = md5($_POST['clave']); // Usando md5 ya que en tu BD hashashaste con esta función

// Preparar la consulta SQL
$sql = "SELECT * FROM usuarios WHERE correo = ? AND clave = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $correo, $clave);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Si el login es exitoso, obtener los datos del usuario
    $usuario = $result->fetch_assoc();
    
    // Almacenar los datos en la sesión
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['role'] = $usuario['role'];
    
    // Redirigir según el rol
    if ($usuario['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
} else {
    // Si el login falla, redirigir al formulario de login con error
    header("Location: login.php?error=1");
    exit();
}
?>
