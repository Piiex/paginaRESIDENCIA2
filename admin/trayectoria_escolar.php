<?php include 'header.php'; ?>
<?php
// Obtener los docentes con documentos de trayectoria subidos
$sql = "SELECT u.id as user_id, u.nombre, t.file_name, t.file_type, t.comment, t.uploaded_at 
        FROM usuarios u
        JOIN trayectoria_documentos t ON u.id = t.user_id
        WHERE u.role = 'docente'
        ORDER BY u.nombre, t.uploaded_at DESC";
$result = $conn->query($sql);
$docentes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $docentes[$row['nombre']][] = $row;
    }
} else {
    $error_message = "No se encontraron documentos.";
}

// Función para obtener el icono según el tipo de archivo
function getFileIcon($fileType) {
    if (strpos($fileType, 'image') !== false) {
        return 'fa-file-image';
    } elseif (strpos($fileType, 'pdf') !== false) {
        return 'fa-file-pdf';
    } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'msword') !== false) {
        return 'fa-file-word';
    } elseif (strpos($fileType, 'excel') !== false || strpos($fileType, 'spreadsheet') !== false) {
        return 'fa-file-excel';
    } else {
        return 'fa-file-alt';
    }
}

// Función para formatear la fecha
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trayectoria Escolar - Documentos Subidos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <style>
        .docente-container {
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .docente-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            position: relative;
        }
        .docente-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        .docente-stats {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            text-align: right;
            color: rgba(255, 255, 255, 0.9);
        }
        .docente-body {
            background: #fff;
            padding: 1.5rem;
        }
        .document-card {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .document-icon {
            font-size: 2rem;
            margin-right: 1rem;
            color: #6c757d;
        }
        .document-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 0.25rem;
            margin-right: 1rem;
        }
        .document-info {
            flex-grow: 1;
        }
        .document-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .file-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: #e9ecef;
            color: #495057;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .comment-text {
            font-style: italic;
            color: #6c757d;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Trayectoria Escolar - Documentos</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <button class="btn btn-outline-success" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Exportar
                </button>
            </div>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($docentes as $nombre => $documentos): ?>
                    <div class="col-12 docente-container">
                        <div class="docente-header">
                            <h2><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($nombre); ?></h2>
                            <div class="docente-stats">
                                <span><?php echo count($documentos); ?> documentos</span>
                            </div>
                        </div>
                        <div class="docente-body">
                            <?php foreach ($documentos as $documento): ?>
                                <div class="document-card d-flex">
                                    <?php if (strpos($documento['file_type'], 'image') !== false): ?>
                                        <a href="../docente/uploads/<?php echo htmlspecialchars($documento['file_name']); ?>" 
                                           data-lightbox="docente-<?php echo htmlspecialchars($documento['user_id']); ?>"
                                           data-title="<?php echo htmlspecialchars($documento['comment']); ?>">
                                            <img src="../docente/uploads/<?php echo htmlspecialchars($documento['file_name']); ?>" 
                                                 class="document-preview" alt="Vista previa">
                                        </a>
                                    <?php else: ?>
                                        <i class="fas <?php echo getFileIcon($documento['file_type']); ?> document-icon"></i>
                                    <?php endif; ?>
                                    
                                    <div class="document-info">
                                        <div class="file-badge">
                                            <i class="fas <?php echo getFileIcon($documento['file_type']); ?> me-1"></i>
                                            <?php echo strtoupper(pathinfo($documento['file_name'], PATHINFO_EXTENSION)); ?>
                                        </div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($documento['file_name']); ?></h5>
                                        <?php if (!empty($documento['comment'])): ?>
                                            <p class="comment-text">
                                                <i class="fas fa-comment-dots me-1"></i>
                                                <?php echo htmlspecialchars($documento['comment']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            Subido el <?php echo formatDate($documento['uploaded_at']); ?>
                                        </small>
                                        
                                        <div class="document-actions">
                                            <a href="uploads/<?php echo htmlspecialchars($documento['file_name']); ?>" 
                                               class="btn btn-sm btn-primary" 
                                               download>
                                                <i class="fas fa-download me-1"></i> Descargar
                                            </a>
                                            <a href="uploads/<?php echo htmlspecialchars($documento['file_name']); ?>" 
                                               class="btn btn-sm btn-secondary"
                                               target="_blank">
                                                <i class="fas fa-eye me-1"></i> Ver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        // Configuración de Lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'albumLabel': "Documento %1 de %2"
        });

        // Función para exportar a Excel
        function exportToExcel() {
            // Aquí puedes implementar la lógica para exportar a Excel
            alert('Función de exportación a Excel - Implementar según necesidades');
        }
    </script>
</body>
</html>
