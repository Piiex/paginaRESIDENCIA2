<?php
session_start();
include '../db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener datos del usuario
$user_id = $_SESSION['user_id'];
$query = "SELECT estado, role, permissions FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../login.php"); // Usuario no encontrado
    exit();
}

$user_data = $result->fetch_assoc();

// Validar estado
if ($user_data['estado'] !== 'activo') {
    echo "<div class='alert alert-danger text-center mt-3'>Tu cuenta está inactiva. Ponte en contacto con el administrador.</div>";
    exit();
}

// Validar permisos
$permissions = json_decode($user_data['permissions'], true);

// Verificar si tiene acceso a la página actual (por ejemplo, 'tutorias')
if (!isset($permissions['tutorias']) || $permissions['tutorias'] !== '1') {
    header("Location:acces_denied.php");
    exit();
}


// Aquí va el resto de tu código para manejar la subida de archivos y la visualización

// Manejar la subida de archivos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = $_POST['file_type'];
    $comment = $_POST['comment'];

    // Mover el archivo a la carpeta de uploads
    $upload_dir = 'uploads/';
    if (move_uploaded_file($file_tmp, $upload_dir . basename($file_name))) {
        // Insertar registro en la base de datos
        $query = "INSERT INTO tutorias_documentos (user_id, file_name, file_type, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $user_id, $file_name, $file_type, $comment);
        $stmt->execute();
        $stmt->close();

        // Redirigir a la misma página para evitar reenvío de formulario
        header("Location: tutorias.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger text-center'>Hubo un error al subir el archivo. Inténtalo nuevamente.</div>";
    }
}

// Manejar la eliminación de archivos
if (isset($_POST['delete'])) {
    $file_id = $_POST['file_id'];

    // Obtener el nombre del archivo para eliminarlo del servidor
    $query = "SELECT file_name FROM tutorias_documentos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_name = $row['file_name'];
        $file_path = 'uploads/' . $file_name;

        // Eliminar archivo del servidor
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Eliminar registro de la base de datos
        $query = "DELETE FROM tutorias_documentos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();
        
        // Redirigir a la misma página para evitar reenvío de formulario
        header("Location: tutorias.php");
        exit();
        
    }
}
include 'header.php';  // Incluir el header
// Mostrar archivos subidos
$query = "SELECT * FROM tutorias_documentos WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Tutorías</title>
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
        .file-list {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .file-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem;
        }
        .file-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    

    <div class="container main-content">
        <h1 class="mb-4">Gestión de Documentos de Tutoría</h1>
        
        <?php if (isset($message)) echo $message; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-upload"></i> Subir Nuevo Documento</h5>
                    </div>
                    <div class="card-body">
                        <form action="tutorias.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">Selecciona el archivo (PDF o Word):</label>
                                <input type="file" class="form-control" name="file" id="file" accept=".pdf, .doc, .docx" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="file_type" class="form-label">Tipo de documento:</label>
                                <select class="form-select" name="file_type" id="file_type" required>
                                    <option value="evidencias">Evidencias</option>
                                    <option value="tutoria_individual">Tutoria Individual</option>
                                    <option value="rubrica">Rubrica</option>
                                    <option value="oficio_asignacion">Oficio de Asignación</option>
                                    <option value="diagnostico_inicial">Diagnóstico Inicial</option>
                                    <option value="plan_de_accion_tutorado">Plan de Acción Tutorados</option>
                                    <option value="informe_semestral">Informe Semestral</option>
                                    <option value="diagnostico_final">Diagnóstico Final</option>
                                    <option value="grafica_seguimiento">Gráfica de Seguimiento</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentario:</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" name="upload" class="btn btn-primary"><i class="fas fa-cloud-upload-alt"></i> Subir archivo</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="file-list">
                    <h5 class="text-center mb-4">Documentos Subidos</h5>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="file-item">
                            <p><strong><?php echo $row['file_name']; ?></strong></p>
                            <p><?php echo ucfirst(str_replace("_", " ", $row['file_type'])); ?></p>
                            <p><?php echo $row['comment']; ?></p>
                            <form action="tutorias.php" method="POST" class="d-inline">
                                <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Eliminar</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
