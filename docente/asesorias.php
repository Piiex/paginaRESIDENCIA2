<?php
session_start();
include '../db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
  
}


// Manejar la subida de archivos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $usuario_id = $_SESSION['user_id']; // Cambiado a usuario_id
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = $_POST['file_type'];
    $comment = $_POST['comment'];

    // Validar el tipo de archivo
    $allowed_types = ['pdf', 'doc', 'docx'];
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    if (!in_array($file_ext, $allowed_types)) {
        $message = "<div class='message error'>Solo se permiten archivos PDF y Word.</div>";
    } else {
        // Mover el archivo a la carpeta de uploads
        $upload_dir = 'uploads/';
        if (move_uploaded_file($file_tmp, $upload_dir . basename($file_name))) {
            // Insertar registro en la base de datos
            $query = "INSERT INTO asesorias_documentos (usuario_id, file_name, file_type, comment) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $usuario_id, $file_name, $file_type, $comment);
            $stmt->execute();
            $stmt->close();

            header("Location: asesorias.php");
            exit();
        } else {
            $message = "<div class='message error'>Hubo un error al subir el archivo.</div>";
        }
    }
}

// Manejar la eliminación de archivos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $file_name = $_POST['file_name'];

    // Eliminar el archivo del servidor
    $file_path = 'uploads/' . basename($file_name);
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Eliminar el registro de la base de datos
    $query = "DELETE FROM asesorias_documentos WHERE file_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $file_name);
    $stmt->execute();
    $stmt->close();

    header("Location: asesorias.php");
    exit();
}
include 'header.php';  // Incluir el header

// Mostrar archivos agrupados por tipo
$query = "SELECT file_type, GROUP_CONCAT(file_name SEPARATOR ', ') AS files FROM asesorias_documentos WHERE usuario_id = ? GROUP BY file_type"; // Cambiado a usuario_id
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
    <title>Gestión de Asesorías</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    

    <div class="container mt-5">
        <h1>Gestión de Documentos de Asesorías</h1>

        <?php if (isset($message)) echo $message; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Subir Nuevo Documento
                    </div>
                    <div class="card-body">
                        <form action="asesorias.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">Selecciona el archivo:</label>
                                <input type="file" class="form-control" name="file" id="file" accept=".pdf, .doc, .docx" required>
                            </div>
                            <div class="mb-3">
                                <label for="file_type" class="form-label">Tipo de documento:</label>
                                <select class="form-select" name="file_type" id="file_type" required>
                                    <option value="Plan de Acción del Asesor Académico">Plan de Acción del Asesor Académico</option>
                                    <option value="Informe Final del Asesor">Informe Final del Asesor</option>
                                    <option value="Informe Parcial del Asesor">Informe Parcial del Asesor</option>
                                    <option value="Informe semestral del coordinador">Informe semestral del coordinador</option>
                                    <option value="Rúbrica">Rúbrica</option>
                                    <option value="Lista de asistencia">Lista de asistencia</option>
                                    





                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentario:</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="upload" class="btn btn-primary">Subir archivo</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <h5><?php echo htmlspecialchars($row['file_type']); ?>:</h5>
                    <?php $files = explode(', ', $row['files']); ?>
                    <ul>
                        <?php foreach ($files as $file): ?>
                            <li>
                                <a href="uploads/<?php echo htmlspecialchars($file); ?>" target="_blank"><?php echo htmlspecialchars($file); ?></a>
                                <form action="asesorias.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($file); ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
