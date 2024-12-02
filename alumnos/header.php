<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'alumnos') {
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../LOGOTIPO.png" alt="Logo" class="d-inline-block align-text-top" style="height: 40px; margin-right: 10px;">
            REPORTES RESIDENCIA
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Sección para mostrar la fecha y hora actuales -->
<div class="container mt-3">
    <div class="alert alert-info text-center" role="alert">
        <span id="fechaHora"></span>
    </div>
</div>

<script>
// Función para mostrar la fecha y hora actuales
function mostrarFechaHora() {
    const fechaHora = new Date();
    const opciones = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
    document.getElementById('fechaHora').textContent = fechaHora.toLocaleString('es-ES', opciones);
}

// Actualizar la fecha y hora al cargar la página
window.onload = mostrarFechaHora;
</script>
