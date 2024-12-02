<?php
session_start();
include '../db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener la información del usuario
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Manejar la actualización de datos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $clave_actual = $_POST['clave_actual'];
    $clave_nueva = $_POST['clave_nueva'];
  

    // Verificar la contraseña actual
    if (password_verify($clave_actual, $user['clave'])) {
        // Actualizar información del usuario
        if (!empty($clave_nueva)) {
            $hashed_clave = password_hash($clave_nueva, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET nombre = ?, correo = ?, clave = ?, estado = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $nombre, $correo, $hashed_clave, $estado, $role, $user_id);
        } else {
            $query = "UPDATE usuarios SET nombre = ?, correo = ?, estado = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssi", $nombre, $correo, $estado, $role, $user_id);
        }

        $stmt->execute();
        $stmt->close();

        // Redirigir a la misma página para evitar reenvío de formulario
        header("Location: perfil.php");
        exit();
    } else {
        $error_message = "La contraseña actual es incorrecta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../admin.php">Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content mt-4">
        <h1 class="mb-4">Perfil de Usuario</h1>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-user-edit"></i> Editar Información</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                <form action="perfil.php" method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" class="form-control" name="correo" id="correo" value="<?php echo htmlspecialchars($user['correo']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="clave_actual" class="form-label">Contraseña Actual:</label>
                        <input type="password" class="form-control" name="clave_actual" id="clave_actual" required>
                    </div>

                    <div class="mb-3">
                        <label for="clave_nueva" class="form-label">Nueva Contraseña (dejar vacío si no se desea cambiar):</label>
                        <input type="password" class="form-control" name="clave_nueva" id="clave_nueva">
                    </div>

                    <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
