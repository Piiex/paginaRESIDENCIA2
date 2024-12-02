<?php include 'header.php'; ?>

<?php

$admin_role = 'admin'; 
$current_user_role = $_SESSION['role']; 
$current_user_id = $_SESSION['user_id']; // Asegúrate de tener el ID de usuario en la sesión

// Manejar la subida de archivos si el usuario es administrador
if ($current_user_role === $admin_role) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $comment = $_POST['comment'];

        // Verificar si se ha subido un archivo
        if (!empty($_FILES['file']['name'])) {
            $file_name = $_FILES['file']['name'];
            $file_type = 'admin'; // Asignar tipo "admin" automáticamente para archivos subidos por admin
            $uploaded_at = date('Y-m-d H:i:s');

            // Mover el archivo al directorio de uploads
            $upload_dir = '../docente/uploads/';
            $upload_file = $upload_dir . basename($file_name);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
                // Insertar información del archivo en la base de datos, incluyendo el user_id
                $sql = "INSERT INTO instrumentacion_documentos (file_name, file_type, comment, uploaded_at, user_id) 
                        VALUES ('$file_name', '$file_type', '$comment', '$uploaded_at', '$current_user_id')";
                
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Archivo subido correctamente.";
                    // Redirigir para evitar la resubida en recarga
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $error_message = "Error al subir el archivo: " . $conn->error;
                }
            } else {
                $error_message = "Error al mover el archivo.";
            }
        } else {
            $error_message = "Por favor selecciona un archivo.";
        }
    }
}

// Obtener los documentos subidos por los docentes
$sql_docentes = "SELECT u.nombre, d.file_name, d.file_type, d.comment, d.uploaded_at 
                 FROM usuarios u
                 JOIN instrumentacion_documentos d ON u.id = d.user_id
                 WHERE u.role = 'docente'";
$result_docentes = $conn->query($sql_docentes);

// Obtener los documentos generales subidos por los administradores (user_id IS NOT NULL)
$sql_generales = "SELECT file_name, file_type, comment, uploaded_at, user_id 
                 FROM instrumentacion_documentos 
                 WHERE file_type = 'admin' AND user_id = '$current_user_id'";  // Filtrar documentos de admin por su user_id
$result_generales = $conn->query($sql_generales);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Documentos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Administrar Documentos</h1>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <!-- Formulario para subir documentos generales -->
        <h2 class="mb-4">Subir Documentos Generales</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Seleccionar Archivo</label>
                <input class="form-control" type="file" id="file" name="file" required>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comentario</label>
                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Subir Archivo</button>
        </form>

        <hr>

        <!-- Mostrar los documentos subidos por los docentes -->
        <h2 class="mb-4">Documentos Subidos por Docentes</h2>
        <?php if ($result_docentes && $result_docentes->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($docente_doc = $result_docentes->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong>Docente:</strong> <?php echo htmlspecialchars($docente_doc['nombre']); ?><br>
                        <strong>Archivo:</strong> <?php echo htmlspecialchars($docente_doc['file_name']); ?><br>
                        <strong>Tipo:</strong> <?php echo htmlspecialchars($docente_doc['file_type']); ?><br>
                        <strong>Comentario:</strong> <?php echo htmlspecialchars($docente_doc['comment']); ?><br>
                        <strong>Subido en:</strong> <?php echo htmlspecialchars($docente_doc['uploaded_at']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No se han subido documentos por los docentes todavía.</p>
        <?php endif; ?>

        <hr>

        <!-- Mostrar los documentos generales subidos por el administrador -->
        <h2 class="mb-4">Documentos Generales (Tipo: Admin)</h2>
        <?php if ($result_generales && $result_generales->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($doc_general = $result_generales->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong>Archivo:</strong> <?php echo htmlspecialchars($doc_general['file_name']); ?><br>
                        <strong>Tipo:</strong> <?php echo htmlspecialchars($doc_general['file_type']); ?><br>
                        <strong>Comentario:</strong> <?php echo htmlspecialchars($doc_general['comment']); ?><br>
                        <strong>Subido en:</strong> <?php echo htmlspecialchars($doc_general['uploaded_at']); ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No se han subido documentos generales de tipo admin todavía.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
