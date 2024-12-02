<?php
session_start();
include '../db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Manejar la inserción de alumnos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $user_id = $_SESSION['user_id'];
    $nombre = $_POST['nombre'];
    $matricula = $_POST['matricula'];
    $tipo_alumno = 'dual'; // Establecer tipo_alumno automáticamente a "dual"

    // Insertar nuevo alumno
    $query = "INSERT INTO alumnos (user_id, nombre, matricula, tipo_alumno) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $user_id, $nombre, $matricula, $tipo_alumno);
    $stmt->execute();
    $stmt->close();

    header("Location: educacion_dual.php");
    exit();
}

// Manejar la subida de archivos para un alumno específico
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $user_id = $_SESSION['user_id'];
    $alumno_id = $_POST['alumno_id'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = $_POST['file_type'];
    $comment = $_POST['comment'];

    // Mover el archivo a la carpeta de uploads
    $upload_dir = 'uploads/';
    if (move_uploaded_file($file_tmp, $upload_dir . basename($file_name))) {
        // Insertar registro en la base de datos
        $query = "INSERT INTO educacion_dual_documentos (user_id, alumno_id, file_name, file_type, comment) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisss", $user_id, $alumno_id, $file_name, $file_type, $comment);
        $stmt->execute();
        $stmt->close();

        header("Location: educacion_dual.php");
        exit();
    } else {
        $message = "<div class='message error'>Hubo un error al subir el archivo.</div>";
    }
}

// Manejar la eliminación de archivos
if (isset($_POST['delete'])) {
    $file_id = $_POST['file_id'];

    // Obtener el nombre del archivo para eliminarlo del servidor
    $query = "SELECT file_name FROM educacion_dual_documentos WHERE id = ?";
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
        $query = "DELETE FROM educacion_dual_documentos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        header("Location: educacion_dual.php");
        exit();
    }
}
include 'header.php';  // Incluir el header
// Mostrar alumnos y sus documentos filtrando por tipo "dual"
$query = "SELECT a.id AS alumno_id, a.nombre, d.id AS doc_id, d.file_name, d.file_type, d.comment 
          FROM alumnos a 
          LEFT JOIN educacion_dual_documentos d ON a.id = d.alumno_id 
          WHERE a.user_id = ? AND a.tipo_alumno = 'dual'"; // Filtrar solo alumnos de tipo "dual"
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Educación Dual</title>
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
        <h1 class="mb-4">Gestión de Documentos y Alumnos duales</h1>

        <?php if (isset($message)) echo $message; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Agregar Alumno</h5>
                    </div>
                    <div class="card-body">
                        <form action="educacion_dual.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Alumno:</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="matricula" class="form-label">Matrícula:</label>
                                <input type="text" class="form-control" name="matricula" id="matricula" required>
                            </div>
                            <!-- Se eliminó el campo tipo_alumno -->
                            <button type="submit" name="add_student" class="btn btn-primary"><i class="fas fa-user-plus"></i> Agregar Alumno</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-upload"></i> Subir Documento</h5>
                    </div>
                    <div class="card-body">
                        <form action="educacion_dual.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="alumno_id" class="form-label">Seleccionar Alumno:</label>
                                <select class="form-select" name="alumno_id" id="alumno_id" required>
                                    <?php
                                    // Obtener solo alumnos de tipo "dual"
                                    $query = "SELECT id, nombre FROM alumnos WHERE user_id = ? AND tipo_alumno = 'dual'";
                                    $stmt = $conn->prepare($query);
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $alumnos = $stmt->get_result();

                                    while ($alumno = $alumnos->fetch_assoc()) {
                                        echo "<option value='" . $alumno['id'] . "'>" . $alumno['nombre'] . "</option>";
                                    }
                                    $stmt->close();
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">Seleccionar Archivo:</label>
                                <input type="file" class="form-control" name="file" id="file" accept=".pdf,.doc,.docx" required>
                            </div>
                            <div class="mb-3">
                                <label for="file_type" class="form-label">Tipo de Documento:</label>
                                <select class="form-select" name="file_type" id="file_type" required>
                                    <option value="1">Plan de Formación</option>
                                    <option value="2">Plan de Seguimiento</option>
                                    <option value="3">Reporte de Actividades</option>
                                    <option value="4">Calificaciones</option>
                                    <option value="5">Seguimiento a Egresados (cuando corresponda)</option>
                                    <option value="6">Convenios (Marco, aprendizaje y específico)</option>
                                    
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentarios:</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                            </div>
                            <button type="submit" name="upload" class="btn btn-primary"><i class="fas fa-upload"></i> Subir Documento</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card file-list">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos Subidos</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <?php if (!is_null($row['file_name'])) : ?>
                                <div class="file-item">
                                    <p><strong>Alumno:</strong> <?= $row['nombre']; ?></p>
                                    <p><strong>Archivo:</strong> <a href="uploads/<?= $row['file_name']; ?>" target="_blank"><?= $row['file_name']; ?></a></p>
                                    <p><strong>Tipo de Documento:</strong> <?= $row['file_type']; ?></p>
                                    <p><strong>Comentarios:</strong> <?= $row['comment']; ?></p>
                                    <form action="educacion_dual.php" method="POST" style="display: inline-block;">
                                        <input type="hidden" name="file_id" value="<?= $row['doc_id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Eliminar</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
