<?php require 'header.php';?>
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
        $query = "INSERT INTO instrumentacion_documentos (user_id, file_name, file_type, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $user_id, $file_name, $file_type, $comment);
        $stmt->execute();
        $document_id = $stmt->insert_id;
        $stmt->close();

/*        // Insertar registro en la tabla de historial
        $query_historial = "INSERT INTO historial_documentos (user_id, action, document_id) VALUES (?, 'upload', ?)";
        $stmt_historial = $conn->prepare($query_historial);
        $stmt_historial->bind_param("ii", $user_id, $document_id);
        $stmt_historial->execute();
        $stmt_historial->close();-->*/

        header("Location: instrumentacion_didactica.php");
        exit();
    } else {
        $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Hubo un error al subir el archivo.</div>";
    }
}

// Manejar la eliminación de archivos
if (isset($_POST['delete'])) {
    $file_id = $_POST['file_id'];

    // Obtener el nombre del archivo para eliminarlo del servidor
    $query = "SELECT file_name FROM instrumentacion_documentos WHERE id = ?";
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
        $query = "DELETE FROM instrumentacion_documentos WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $file_id);
        $stmt->execute();
        $stmt->close();

        // Insertar registro en la tabla de historial
        $query_historial = "INSERT INTO historial_documentos (user_id, action, document_id) VALUES (?, 'delete', ?)";
        $stmt_historial = $conn->prepare($query_historial);
        $stmt_historial->bind_param("ii", $_SESSION['user_id'], $file_id);
        $stmt_historial->execute();
        $stmt_historial->close();

        header("Location: instrumentacion_didactica.php");
        exit();
    }
}

// Obtener documentos del usuario
$query = "SELECT * FROM instrumentacion_documentos WHERE user_id = ? ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Obtener documentos admin
$query_admin = "SELECT * FROM instrumentacion_documentos WHERE file_type = 'admin' ORDER BY uploaded_at DESC";
$stmt_admin = $conn->prepare($query_admin);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de Instrumentación Didáctica</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .document-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            background-color: #ffffff;
            transition: box-shadow 0.3s ease;
        }
        .document-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .document-header {
            padding: 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 8px 8px 0 0;
        }
        .document-body {
            padding: 1rem;
        }
        .document-preview {
            margin: 1rem 0;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        .document-actions {
            padding: 1rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .file-type-badge {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background-color: #e9ecef;
            color: #495057;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .preview-placeholder {
            padding: 2rem;
            text-align: center;
            background-color: #f8f9fa;
            border: 1px dashed #dee2e6;
        }
        .upload-form {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .main-content {
            margin-bottom: 3rem;
        }
        .btn-upload {
            min-width: 120px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container main-content mt-4">
    <h1 class="mb-4">Gestión de Documentos de Instrumentación Didáctica</h1>
    
    <?php if (isset($message)) echo $message; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card upload-form">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-upload"></i> Subir Nuevo Documento</h5>
                </div>
                <div class="card-body">
                    <form action="instrumentacion_didactica.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="form-label">Selecciona el archivo:</label>
                            <input type="file" class="form-control" name="file" id="file" accept=".pdf, .doc, .docx" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file_type" class="form-label">Tipo de documento:</label>
                            <select class="form-select" name="file_type" id="file_type" required>
                                <option value="">Selecciona un tipo...</option>
                                <option value="Horario">Horario</option>
                                <option value="Inst Didáctica">Instrumentación Didáctica</option>
                                <option value="Caled Exámenes">Calendario de Exámenes</option>
                                <option value="Listas Asistencia">Listas de Asistencia</option>
                                <option value="Plantilla-Rejilla-Rub">Plantilla/Rejilla/Rúbrica</option>
                                <option value="Evidencias Aprend">Evidencias de Aprendizaje</option>
                                <option value="Oficio de Liberación">Oficio de Liberación</option>
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                    <option value="admin">Documento Administrativo</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Comentario:</label>
                            <textarea class="form-control" name="comment" id="comment" rows="3" placeholder="Añade un comentario opcional..."></textarea>
                        </div>
                        
                        <button type="submit" name="upload" class="btn btn-primary btn-upload">
                            <i class="fas fa-cloud-upload-alt"></i> Subir archivo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Información</h5>
                </div>
                <div class="card-body">
                    <h6>Tipos de archivos permitidos:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-file-pdf text-danger"></i> PDF (.pdf)</li>
                        <li><i class="fas fa-file-word text-primary"></i> Word (.doc, .docx)</li>
                    </ul>
                    <hr>
                    <h6>Notas:</h6>
                    <ul class="mb-0">
                        <li>Tamaño máximo por archivo: 10MB</li>
                        <li>Se recomienda usar nombres descriptivos para los archivos</li>
                        <li>Los documentos subidos pueden ser eliminados posteriormente</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Documentos del Usuario -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Mis Documentos</h5>
                </div>
                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="document-card">
                                <div class="document-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($row['file_name']); ?></h5>
                                            <span class="file-type-badge">
                                                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['file_type']); ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($row['uploaded_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="document-body">
                                    <?php if ($row['comment']): ?>
                                        <p class="mb-3">
                                            <i class="fas fa-comment text-muted"></i> 
                                            <?php echo htmlspecialchars($row['comment']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="document-preview">
                                        <?php
                                        $file_extension = strtolower(pathinfo($row['file_name'], PATHINFO_EXTENSION));
                                        $file_path = 'uploads/' . htmlspecialchars($row['file_name']);
                                        
                                        if ($file_extension === 'pdf'): ?>
                                            <iframe src="<?php echo $file_path; ?>" width="100%" height="400px" frameborder="0"></iframe>
                                        <?php elseif (in_array($file_extension, ['doc', 'docx'])): ?>
                                            <div class="preview-placeholder">
                                                <i class="fas fa-file-word fa-3x text-primary mb-3"></i>
                                                <h6>Documento de Word</h6>
                                                <p class="text-muted mb-0">Vista previa no disponible</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="preview-placeholder">
                                                <i class="fas fa-file fa-3x text-secondary mb-3"></i>
                                                <h6>Archivo</h6>
                                                <p class="text-muted mb-0">Vista previa no disponible</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="document-actions">
                                    <a href="<?php echo $file_path; ?>" class="btn btn-primary" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <?php if ($file_extension === 'pdf'): ?>
                                        <a href="<?php echo $file_path; ?>" class="btn btn-secondary" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> Ver en nueva pestaña
                                        </a>
                                    <?php endif; ?>
                                    <form action="instrumentacion_didactica.php" method="POST" class="d-inline">
                                        <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger" 
                                                onclick="return confirm('¿Estás seguro de que quieres eliminar este archivo?');">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No has subido documentos todavía.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Documentos Administrativos -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos Administrativos</h5>
                </div>
                <div class="card-body">
                    <?php if ($result_admin->num_rows > 0): ?>
                        <?php while ($row_admin = $result_admin->fetch_assoc()): ?>
                            <div class="document-card">
                                <div class="document-header">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1"><?php echo htmlspecialchars($row_admin['file_name']); ?></h5>
                                            <span class="file-type-badge">
                                                <i class="fas fa-tag"></i> Documento Administrativo
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($row_admin['uploaded_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="document-body">
                                    <?php if ($row_admin['comment']): ?>
                                        <p class="mb-3">
                                            <i class="fas fa-comment text-muted"></i> 
                                            <?php echo htmlspecialchars($row_admin['comment']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="document-preview">
                                        <?php
                                        $file_extension = strtolower(pathinfo($row_admin['file_name'], PATHINFO_EXTENSION));
                                        $file_path = 'uploads/' . htmlspecialchars($row_admin['file_name']);
                                        
                                        if ($file_extension === 'pdf'): ?>
                                            <iframe src="<?php echo $file_path; ?>" width="100%" height="400px" frameborder="0"></iframe>
                                        <?php elseif (in_array($file_extension, ['doc', 'docx'])): ?>
                                            <div class="preview-placeholder">
                                                <i class="fas fa-file-word fa-3x text-primary mb-3"></i>
                                                <h6>Documento de Word</h6>
                                                <p class="text-muted mb-0">Vista previa no disponible</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="preview-placeholder">
                                                <i class="fas fa-file fa-3x text-secondary mb-3"></i>
                                                <h6>Archivo</h6>
                                                <p class="text-muted mb-0">Vista previa no disponible</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="document-actions">
                                    <a href="<?php echo $file_path; ?>" class="btn btn-primary" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <?php if ($file_extension === 'pdf'): ?>
                                        <a href="<?php echo $file_path; ?>" class="btn btn-secondary" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> Ver en nueva pestaña
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay documentos administrativos disponibles.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const uploadForm = document.querySelector('form');
    const fileInput = document.getElementById('file');
    const fileTypeSelect = document.getElementById('file_type');

    uploadForm.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validar archivo
        if (!fileInput.value) {
            alert('Por favor selecciona un archivo');
            isValid = false;
        }

        // Validar tipo de documento
        if (!fileTypeSelect.value) {
            alert('Por favor selecciona un tipo de documento');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    // Preview del nombre del archivo
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const fileName = this.files[0].name;
            const fileSize = this.files[0].size;
            const maxSize = 10 * 1024 * 1024; // 10MB en bytes

            if (fileSize > maxSize) {
                alert('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
                this.value = '';
                return;
            }

            // Validar extensión
            const validExtensions = ['pdf', 'doc', 'docx'];
            const fileExtension = fileName.split('.').pop().toLowerCase();
            
            if (!validExtensions.includes(fileExtension)) {
                alert('Tipo de archivo no permitido. Por favor sube un archivo PDF o Word.');
                this.value = '';
            }
        }
    });
});
</script>
</body>
</html>