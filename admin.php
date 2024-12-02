<?php

// Verificar si ya hay una sesión iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Tiempo máximo de inactividad en segundos (10 minutos = 600 segundos)
$tiempo_inactividad = 600;

if (isset($_SESSION['ultima_actividad'])) {
    $inactividad = time() - $_SESSION['ultima_actividad'];
    if ($inactividad > $tiempo_inactividad) {
        // Cerrar sesión por inactividad
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Actualizar el tiempo de última actividad
$_SESSION['ultima_actividad'] = time();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Función para generar URLs de admin
function getAdminModuleUrl($moduleName) {
    return "admin/{$moduleName}.php";
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Gestión Educativa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --hover-color: #3b82f6;
            --text-primary: #1f2937;
            --bg-light: #f8fafc;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.75rem 0;
        }

        .navbar-brand img {
            height: 35px;
            margin-right: 10px;
        }

        .navbar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .main-content {
            margin-top: 1.5rem;
        }

        .module-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 100%;
            padding: 0.5rem;
        }

        .module-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .module-icon {
            font-size: 1.8rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin: 0 auto 0.8rem;
        }

        .card-body {
            padding: 1rem;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .btn-module {
            padding: 0.4rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s;
            width: 100%;
            background-color: var(--bg-light);
            color: #64748b;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-module:hover {
            background-color: var(--hover-color);
            color: white;
            border-color: var(--hover-color);
        }

        .btn-module i {
            font-size: 0.9rem;
        }

        .dashboard-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dashboard-title i {
            color: var(--primary-color);
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }

        .alert i {
            color: #856404;
        }

        .session-timer {
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 4px;
        }

        @media (min-width: 768px) {
            .row-cols-md-4 > * {
                flex: 0 0 auto;
                width: 25%;
            }
        }

        /* Animación para los iconos */
        .module-icon i {
            transition: transform 0.3s ease;
        }

        .module-card:hover .module-icon i {
            transform: scale(1.1);
        }
    </style>

    <!-- Contador de sesión en el encabezado -->
    <script>
        // Tiempo máximo de inactividad en milisegundos (10 minutos = 600000 ms)
        let tiempoMaximoInactividad = 300000;
        let temporizadorInactividad;
        let tiempoRestante = tiempoMaximoInactividad / 500; // Tiempo restante en segundos

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
            window.location.href = 'logout.php'; // Redirige a la página de logout
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
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="LOGOTIPO.png" alt="Logo" class="d-inline-block align-text-top">
                <span class="fw-semibold">Panel de Administración</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-user-shield"></i>
                            <span>Perfil Admin</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <h1 class="dashboard-title">
            <i class="fas fa-th-large"></i>
            Panel de Control
        </h1>
        <div class="row row-cols-1 row-cols-md-4 g-3">
    <?php
    $modules = [
        ['tutorias', 'Tutorías', 'fa-chalkboard-teacher', 'bg-blue-500', 'fa-arrow-right'],
        ['asesorias', 'Asesorías', 'fa-users', 'bg-green-500', 'fa-arrow-right'],
        ['educacion_dual', 'Educación Dual', 'fa-graduation-cap', 'bg-indigo-500', 'fa-arrow-right'],
        ['residencia_profesional', 'Residencia', 'fa-building', 'bg-yellow-500', 'fa-arrow-right'],
        ['instrumentacion_didactica', 'Instrumentación', 'fa-book', 'bg-red-500', 'fa-arrow-right'],
        ['trayectoria_escolar', 'Trayectoria', 'fa-chart-line', 'bg-purple-500', 'fa-arrow-right'],
        ['atributos_egreso', 'Atributos', 'fa-award', 'bg-pink-500', 'fa-arrow-right'],
        ['investigacion', 'Investigación', 'fa-microscope', 'bg-cyan-500', 'fa-arrow-right'],
        ['acuerdos_academia', 'Acuerdos', 'fa-handshake', 'bg-teal-500', 'fa-arrow-right'],
        ['admin_usuarios', 'Usuarios', 'fa-users-cog', 'bg-orange-500', 'fa-arrow-right'],
        ['editar_permisiones', 'Permisos', 'fa-user-shield', 'bg-lime-500', 'fa-arrow-right'],
        ['eventos', 'Eventos', 'fa-calendar-alt', 'bg-rose-500', 'fa-arrow-right'],
    ];

    foreach ($modules as $module):
    ?>
        <div class="col">
            <div class="module-card">
                <div class="card-body text-center">
                    <i class="fas <?php echo $module[2]; ?> module-icon <?php echo $module[3]; ?> text-white"></i>
                    <h5 class="card-title"><?php echo $module[1]; ?></h5>
                    <a href="<?php echo getAdminModuleUrl($module[0]); ?>" class="btn btn-module">
                        <span>Gestionar</span>
                        <i class="fas <?php echo $module[4]; ?>"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    </div>

    <div class="container mt-3">
        <div class="alert" role="alert">
            <i class="fas fa-clock"></i>
            <span>Tiempo restante de sesión:</span>
            <span id="contador" class="session-timer"></span>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>