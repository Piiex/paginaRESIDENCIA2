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
    $user_id = $_SESSION['user_id'];
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_type = $_POST['file_type'];
    $comment = $_POST['comment'];

    // Mover el archivo a la carpeta de uploads
    $upload_dir = 'uploads/';
    if (move_uploaded_file($file_tmp, $upload_dir . basename($file_name))) {
        // Insertar registro en la base de datos
        $query = "INSERT INTO investigacion_documentos (user_id, file_name, file_type, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $user_id, $file_name, $file_type, $comment);
        $stmt->execute();
        $stmt->close();

        // Redirigir a la misma página para evitar reenvío de formulario
        header("Location: investigacion.php");
        exit();
    } else {
        $message = "<div class='message error'>Hubo un error al subir el archivo.</div>";
    }
}

// Manejar la eliminación de archivos
if (isset($_POST['delete'])) {
    $file_id = $_POST['file_id'];

    // Obtener el nombre del archivo para eliminarlo del servidor
    $query = "SELECT file_name FROM investigacion_documentos WHERE id = ?";
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
        $query = "DELETE FROM investigacion_documentos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Redirigir a la misma página para evitar reenvío de formulario
        header("Location: investigacion.php");
        exit();
    }
}
include 'header.php';  // Incluir el header
// Mostrar archivos subidos
$query = "SELECT * FROM investigacion_documentos WHERE user_id = ?";
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
    <title>Documentos de Investigación</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
   

    <div class="container main-content mt-4">
        <h1 class="mb-4">Gestión de Documentos de Investigación</h1>
        
        <?php if (isset($message)) echo $message; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-upload"></i> Subir Nuevo Documento</h5>
                    </div>
                    <div class="card-body">
                        <form action="investigacion.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="file" class="form-label">Selecciona el archivo:</label>
                                <input type="file" class="form-control" name="file" id="file" accept=".pdf, .doc, .docx" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="file_type" class="form-label">Tipo de documento:</label>
                                <select class="form-select" name="file_type" id="file_type" required>
                                    <option value="articulo">Artículo</option>
                                    <option value="tesis">Tesis</option>
                                    <option value="proyecto">Proyecto</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentario:</label>
                                <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" name="upload" class="btn btn-primary"><i class="fas fa-cloud-upload-alt"></i> Subir archivo</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos Subidos</h5>
                    </div>
                    <div class="card-body file-list">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="file-item">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($row['file_name']); ?></h6>
                                    <p class="mb-1"><strong>Tipo:</strong> <?php echo htmlspecialchars($row['file_type']); ?></p>
                                    <p class="mb-1"><strong>Comentario:</strong> <?php echo htmlspecialchars($row['comment']); ?></p>
                                    <p class="mb-2"><small class="text-muted">Subido el: <?php echo $row['uploaded_at']; ?></small></p>
                                    <form action="investigacion.php" method="POST" class="d-inline">
                                        <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este archivo?');"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center my-3">No se han subido documentos todavía.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
