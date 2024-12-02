 <?php
// Verificar si ya hay una sesión iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
require 'db.php';


// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_permissions = $_POST['permissions'] ?? [];

    // Actualizar permisos para cada usuario
    foreach ($user_permissions as $user_id => $permissions) {
        // Convertir permisos a formato JSON
        $permissions_json = json_encode($permissions);
        $stmt = $conn->prepare("UPDATE usuarios SET permissions = ? WHERE id = ?");
        $stmt->bind_param("si", $permissions_json, $user_id);
        $stmt->execute();
    }

    header("Location: editar_permisiones.php"); // Redirige después de guardar
    exit();
}

// Obtener todos los usuarios que no son admin
$resultado = $conn->query("SELECT * FROM usuarios WHERE role != 'admin'");
$usuarios = $resultado->fetch_all(MYSQLI_ASSOC);

// Módulos disponibles
$modulos = [
    'tutorias' => 'Tutorías',
    'asesorias' => 'Asesorías',
    'educacion_dual' => 'Educación Dual',
    'residencia_profesional' => 'Residencia Profesional',
    'instrumentacion_didactica' => 'Instrumentación Didáctica',
    'trayectoria_escolar' => 'Trayectoria Escolar',
    'atributos_egreso' => 'Atributos de Egreso',
    'investigacion' => 'Investigación',
    'acuerdos_academia' => 'Acuerdos de Academia'
];

$conn->close(); // Cerrar la conexión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Permisos de Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --header-height: 60px;
        }

        body {
            background-color: #f8f9fa;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-wrapper {
            flex: 1;
            padding: 2rem 1rem;
        }

        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            border-bottom: none;
            padding: 1.25rem;
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
        }

        .table-container {
            margin: 2rem 2rem;
            overflow-x: auto;
            position: relative;
        }

        .table {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .table th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 1rem;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem;
        }

        .form-check-input {
            width: 2.5em;
            height: 1.25em;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }

        .actions-bar {
            background-color: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            margin-top: 2rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @media (max-width: 768px) {
            .table-container {
                margin: 0;
                border-radius: 0;
            }
            
            .content-wrapper {
                padding: 1rem 0.5rem;
            }
            
            .card {
                border-radius: 0;
            }

            .btn {
                width: 100%;
                margin: 0.5rem 0;
            }

            .actions-bar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                border-radius: 0;
                z-index: 1000;
            }

            .page-wrapper {
                padding-bottom: 100px;
            }
        }

        /* Animaciones */
        .table tr {
            transition: background-color 0.3s ease;
        }

        .table tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .form-check-input {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <?php include 'header.php'; ?>

        <section class="main-header">
            <div class="container-fluid">
                <h1 class="h3 mb-0">Administración de Módulos para el Docente</h1>
            </div>
        </section>

        <main class="content-wrapper">
            <div class="container-fluid">
                <form method="POST" action="">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt me-2"></i>
                                <h5 class="mb-0 text-white">Permisos de Módulos</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-container">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <?php foreach ($modulos as $modulo_key => $modulo_name): ?>
                                                <th class="text-center"><?php echo htmlspecialchars($modulo_name); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td class="fw-medium"><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                                <td><i class="fas fa-envelope me-2 text-muted"></i><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                                <td>
                                                    <?php if ($usuario['estado'] == 'activo'): ?>
                                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php 
                                                    $permissions = json_decode($usuario['permissions'], true) ?? [];
                                                ?>
                                                <?php foreach ($modulos as $modulo_key => $modulo_name): ?>
                                                    <td class="text-center">
                                                        <div class="form-check form-switch d-flex justify-content-center">
                                                            <input type="checkbox" class="form-check-input" 
                                                                   id="perm_<?php echo $usuario['id']; ?>_<?php echo $modulo_key; ?>"
                                                                   name="permissions[<?php echo $usuario['id']; ?>][<?php echo $modulo_key; ?>]" 
                                                                   value="1" 
                                                                   <?php echo isset($permissions[$modulo_key]) && $permissions[$modulo_key] == 1 ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="actions-bar">
                        <div class="container-fluid">
                            <div class="row g-3 justify-content-between">
                                <div class="col-12 col-md-auto">
                                    <a href="../admin.php" class="btn btn-secondary w-100 w-md-auto">
                                        <i class="fas fa-arrow-left me-2"></i>Volver al Panel
                                    </a>
                                </div>
                                <div class="col-12 col-md-auto">
                                    <button type="submit" class="btn btn-primary w-100 w-md-auto">
                                        <i class="fas fa-save me-2"></i>Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>