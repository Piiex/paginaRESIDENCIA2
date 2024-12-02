<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Tiempo máximo de inactividad en segundos (10 minutos = 600 segundos)
$tiempo_inactividad = 600;

if (isset($_SESSION['ultima_actividad'])) {
    $inactividad = time() - $_SESSION['ultima_actividad'];
    if ($inactividad > $tiempo_inactividad) {
        // Cerrar sesión por inactividad
        session_unset();
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}

// Actualizar el tiempo de última actividad
$_SESSION['ultima_actividad'] = time();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../admin.php">
            <img src="LOGOTIPO.png" alt="Logo" class="d-inline-block align-text-top" style="height: 40px; margin-right: 10px;">
            Panel de Administración
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Contador de sesión -->
<div class="container mt-3">
    <div class="alert alert-warning text-center" role="alert">
        Tu sesión se cerrará automáticamente en <span id="contador"></span> minutos por inactividad.
    </div>
</div>

<script>
    // Tiempo máximo de inactividad en milisegundos (5 minutos = 300000 ms)
    const tiempoMaximoInactividad = 300000;
    let temporizadorInactividad;
    let tiempoRestante = tiempoMaximoInactividad / 1000; // Tiempo restante en segundos

    // Función para reiniciar el temporizador de inactividad
    function reiniciarTemporizador() {
        clearTimeout(temporizadorInactividad);
        tiempoRestante = tiempoMaximoInactividad / 1000; // Reiniciar el tiempo restante
        actualizarContador(); // Actualizar el contador de inmediato
        temporizadorInactividad = setTimeout(cerrarSesion, tiempoMaximoInactividad);
    }

    // Función para cerrar la sesión automáticamente
    function cerrarSesion() {
        alert('Tu sesión ha expirado por inactividad.');
        window.location.href = '../logout.php'; // Redirige a la página de logout
    }

    // Función para actualizar el contador de tiempo
    function actualizarContador() {
        const contador = document.getElementById('contador');
        let minutos = Math.floor(tiempoRestante / 60);
        let segundos = tiempoRestante % 60;
        contador.textContent = `${minutos}:${segundos < 10 ? '0' : ''}${segundos}`;
    }

    // Función para reducir el tiempo restante cada segundo
    function contarRegresivamente() {
        if (tiempoRestante > 0) {
            tiempoRestante--;
            actualizarContador();
        } else {
            cerrarSesion(); // Cierra la sesión si el contador llega a 0
        }
    }

    // Iniciar el temporizador del contador en reversa
    function iniciarContador() {
        reiniciarTemporizador();
        setInterval(contarRegresivamente, 1000); // Actualizar el contador cada segundo
    }

    // Reiniciar el temporizador cuando haya actividad del usuario
    window.onload = iniciarContador;
    window.onmousemove = reiniciarTemporizador;
    window.onkeypress = reiniciarTemporizador;
    window.onscroll = reiniciarTemporizador;
</script>

</body>
</html>

