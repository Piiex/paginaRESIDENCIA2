<?php
session_start();
include '../docente/header.php';
include '../db.php';

// Verificación de sesión y rol de usuario
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'docente') {
    header("Location: ../login.php");
    exit();
}

$user_id = filter_var($_SESSION['user_id'], FILTER_SANITIZE_NUMBER_INT);

// Verificación del rol encargado_tutorias
$stmt = $conn->prepare("SELECT encargado FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($encargado);
$stmt->fetch();
$stmt->close();

if ($encargado !== 'encargado_tutorias') {
    // Redirige si el usuario no tiene el rol encargado_tutorias
    header("Location: error.php"); // Cambia a la ruta que prefieras para manejar errores
    exit();
}

// Continuar con el código de la página

// File type verification function
function isAllowedFileType($fileType) {
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    return in_array($fileType, $allowedTypes);
}

// Paginación y consulta de documentos
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("
    SELECT 
        td.id, td.file_name, td.file_type, td.upload_date, td.comment, td.user_id, 
        u.nombre AS docente_nombre,
        COUNT(td.id) OVER() AS total_count
    FROM tutorias_documentos td
    LEFT JOIN usuarios u ON td.user_id = u.id
    ORDER BY td.upload_date DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$documents = $stmt->get_result();
$total_records = $documents->fetch_assoc()['total_count'];
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Documental - Tutorías</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.2/viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.10.2/viewer.min.js"></script>
    <style>
        /* Estilos personalizados */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --background-color: #f5f6fa;
        }
        body { background-color: var(--background-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .main-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .document-card { background: white; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; height: 100%; overflow: hidden; }
        .document-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.15); }
        .preview-container { height: 250px; overflow: hidden; position: relative; background: #f8f9fa; cursor: pointer; }
        .preview-container iframe, .preview-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
        .preview-container:hover iframe, .preview-container:hover img { transform: scale(1.05); }
        .document-info { padding: 1.5rem; }
        .file-type-badge { position: absolute; top: 10px; right: 10px; z-index: 10; }
        .comment-section { max-height: 100px; overflow-y: auto; padding: 0.5rem; background: #f8f9fa; border-radius: 8px; margin: 1rem 0; }
        .action-buttons { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .action-button { flex: 1; padding: 0.5rem; border-radius: 6px; transition: all 0.2s ease; }
        .modal-preview { max-height: 80vh; width: 100%; }
    </style>
</head>
<body>
<div class="main-container">
    <div class="page-header">
        <h1 class="display-4">Sistema de Gestión Documental</h1>
        <p class="lead">Visualización y gestión de documentos de tutorías</p>
    </div>

    <?php if ($total_records > 0): ?>
        <div class="row g-4">
            <?php foreach ($documents as $document): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="document-card">
                        <div class="preview-container" data-bs-toggle="modal" data-bs-target="#previewModal<?= $document['id'] ?>">
                            <span class="badge bg-<?= isAllowedFileType($document['file_type']) ? 'success' : 'warning' ?> file-type-badge">
                                <?= htmlspecialchars($document['file_type']) ?>
                            </span>
                            <?php if (strpos($document['file_type'], 'pdf') !== false): ?>
                                <iframe src="../docente/uploads/<?= urlencode($document['file_name']) ?>" class="preview-frame"></iframe>
                            <?php elseif (strpos($document['file_type'], 'image') !== false): ?>
                                <img src="../docente/uploads/<?= urlencode($document['file_name']) ?>" class="preview-image" alt="Vista previa">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-file-alt fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="document-info">
                            <h5 class="card-title text-truncate mb-3">
                                <?= htmlspecialchars($document['file_name']) ?>
                            </h5>
                            
                            <div class="document-metadata mb-3">
                                <small class="text-muted d-block">
                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($document['docente_nombre']) ?>
                                </small>
                                <small class="text-muted d-block">
                                    <i class="fas fa-calendar me-2"></i><?= date('d/m/Y H:i', strtotime($document['upload_date'])) ?>
                                </small>
                            </div>

                            <div class="comment-section">
                                <?= !empty($document['comment']) ? nl2br(htmlspecialchars($document['comment'])) : '<p class="text-muted">Sin comentarios</p>' ?>
                            </div>

                            <div class="action-buttons">
                                <a href="../docente/uploads/<?= urlencode($document['file_name']) ?>" class="btn btn-outline-primary action-button" target="_blank">
                                    <i class="fas fa-download me-1"></i> Descargar
                                </a>
                                <button class="btn btn-outline-warning action-button" data-bs-toggle="modal" data-bs-target="#commentModal<?= $document['id'] ?>">
                                    <i class="fas fa-comment me-1"></i> Comentar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modals de vista previa y comentarios permanecen iguales -->
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Navegación de páginas" class="d-flex justify-content-center">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted">No se encontraron documentos para mostrar.</p>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

 