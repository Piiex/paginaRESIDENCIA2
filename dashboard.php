<?php
session_start();
include 'db.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];

// Obtener los permisos y el cargo del usuario desde la base de datos
$stmt = $conn->prepare("SELECT permissions, encargado FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $permissions = json_decode($user['permissions'], true); // Decodificar permisos
    $encargado = $user['encargado']; // Cargo asignado
} else {
    echo "No se encontraron permisos para este usuario.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Docente</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        .main-content {
            margin-top: 2rem;
        }
        .module-card {
            transition: transform 0.3s ease-in-out;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .module-icon {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .cargo-section {
            background-color: #ffebcc;
            border-left: 4px solid #ffa500;
            padding: 15px;
            margin-bottom: 2rem;
        }
        .cargo-section h4 {
            color: #d35400;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <img src="LOGOTIPO.png" alt="Logo" class="d-inline-block align-text-top">
                Sistema de Gestión Docente
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="docente/perfil.php"><i class="fas fa-user"></i> Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container main-content">
        <h1 class="mb-4">Bienvenido(a), <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>

        <!-- Sección destacada para el cargo -->
        <?php if (!empty($encargado)): ?>
        <div class="cargo-section">
            <h4><i class="fas fa-user-tie"></i> Usted es coordinador de: <strong><?php echo htmlspecialchars($encargado); ?></strong></h4>
            <p>Como coordinador de este módulo, tiene responsabilidades adicionales. Puede administrar el módulo usando el siguiente enlace:</p>
            <a href="encargados/<?php echo strtolower(str_replace(' ', '_', $encargado)); ?>.php" class="btn btn-warning"><i class="fas fa-cogs"></i> Administrar <?php echo htmlspecialchars($encargado); ?></a>
        </div>
        <?php endif; ?>

        <h2 class="mb-4">Módulos disponibles:</h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php
            $modules = [
                'tutorias' => ['Tutorías', 'fa-chalkboard-teacher'],
                'asesorias' => ['Asesorías', 'fa-users'],
                'educacion_dual' => ['Educación Dual', 'fa-graduation-cap'],
                'residencia_profesional' => ['Residencia Profesional', 'fa-building'],
                'instrumentacion_didactica' => ['Instrumentación Didáctica', 'fa-book'],
                'trayectoria_escolar' => ['Trayectoria Escolar', 'fa-chart-line'],
                'atributos_egreso' => ['Atributos de Egreso', 'fa-award'],
                'investigacion' => ['Investigación', 'fa-microscope'],
                'acuerdos_academia' => ['Acuerdos de Academia', 'fa-handshake'],
                'eventos' => ['Eventos', 'fa-calendar-alt'] // Módulo de Eventos, de libre acceso
            ];

            // Mostrar módulos según los permisos del usuario
            foreach ($modules as $key => $value):
                if ($key !== 'eventos' && isset($permissions[$key]) && $permissions[$key] == 1):
            ?>
                <div class="col mb-3">
                    <div class="module-card h-100">
                        <div class="card-body">
                            <i class="fas <?php echo $value[1]; ?> module-icon"></i>
                            <h5 class="card-title"><?php echo $value[0]; ?></h5>
                            <a href="docente/<?php echo $key; ?>.php" class="btn btn-primary">Acceder</a>
                        </div>
                    </div>
                </div>
            <?php
                elseif ($key === 'eventos'): // Mostrar módulo de eventos sin restricción de permisos
            ?>
                <div class="col mb-3">
                    <div class="module-card h-100">
                        <div class="card-body">
                            <i class="fas <?php echo $value[1]; ?> module-icon"></i>
                            <h5 class="card-title"><?php echo $value[0]; ?></h5>
                            <a href="docente/<?php echo $key; ?>.php" class="btn btn-primary">Acceder</a>
                        </div>
                    </div>
                </div>
            <?php
                endif;
            endforeach;
            ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>


