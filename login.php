<?php
session_start();
include 'db.php'; // Asegúrate de que este archivo exista y contenga la conexión a la base de datos

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo']; // Cambiado de 'email' a 'correo'
    $clave = $_POST['clave']; // Cambiado de 'password' a 'clave'

    // Consulta para obtener los datos del usuario
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?"); // Cambiado de 'email' a 'correo'
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar el estado del usuario
        if ($user['estado'] === 'inactivo') {
            $error_message = "Tu cuenta está deshabilitada. Comunícate con el administrador.";
        } else {
            // Verificar la contraseña usando password_verify()
            if (password_verify($clave, $user['clave'])) { // Cambiado de 'password' a 'clave'
                // Si la contraseña es correcta, guardar datos del usuario en la sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['role'] = $user['role'];

                // Redirigir según el rol del usuario
                if ($user['role'] == 'admin') {
                    header("Location: admin.php");
                } elseif ($user['role'] == 'alumnos') {
                    header("Location: alumnos/dashboard_alumno.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error_message = "Contraseña incorrecta.";
            }
        }
    } else {
        $error_message = "No se encontró una cuenta con ese correo electrónico.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Tutorías</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo img {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-logo">
                        <img src="LOGOTIPO.png" alt="Logo" class="img-fluid">
                    </div>
                    <h2 class="text-center mb-4">Iniciar Sesión</h2>
                    <?php
                    if (!empty($error_message)) {
                        echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
                    }
                    ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="clave" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="clave" name="clave" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

