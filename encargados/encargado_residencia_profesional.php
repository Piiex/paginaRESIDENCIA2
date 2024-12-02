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

// Verificación del cargo encargado de asesorías
$stmt = $conn->prepare("SELECT encargado FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($encargado);
$stmt->fetch();
$stmt->close();

if ($encargado !== 'encargado_residencia_profesional') {
    header("Location: error.php");
    exit();
}

// Paginación y consulta de documentos
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("
    SELECT 
        ad.id, ad.file_name, ad.file_type, ad.uploaded_at, ad.comment, ad.user_id, 
        u.nombre AS docente_nombre,
        COUNT(ad.id) OVER() AS total_count
    FROM residencia_documentos ad
    LEFT JOIN usuarios u ON ad.user_id = u.id
    ORDER BY ad.uploaded_at DESC
    LIMIT ? OFFSET ?
");


$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$documents = $stmt->get_result();
$total_records = $documents->num_rows > 0 ? $documents->fetch_assoc()['total_count'] : 0;
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Residencia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1 class="my-4">Residencia</h1>
    
    <?php if ($documents->num_rows > 0): ?>
        <div class="row">
            <?php while ($doc = $documents->fetch_assoc()): ?>
                <div class="col-md-4">
    <div class="card mb-4 shadow-sm">
        <!-- Vista previa del documento -->
        <div class="card-img-top">
            <?php if ($doc['file_type'] === 'application/pdf'): ?>
                <!-- Contenedor para el canvas PDF -->
                <canvas id="pdf-preview-<?= $doc['id'] ?>" style="width: 100%; height: 200px;"></canvas>
                
                <!-- Script de vista previa PDF con PDF.js -->
                <script>
                    const url = "../docente/uploads/<?= urlencode($doc['file_name']) ?>";
                    const canvas = document.getElementById("pdf-preview-<?= $doc['id'] ?>");
                    const context = canvas.getContext("2d");

                    // Cargar el PDF con PDF.js
                    pdfjsLib.getDocument(url).promise.then(pdf => {
                        // Cargar la primera página
                        pdf.getPage(1).then(page => {
                            const scale = 0.5;
                            const viewport = page.getViewport({ scale: scale });

                            // Ajustar el tamaño del canvas al viewport
                            canvas.width = viewport.width;
                            canvas.height = viewport.height;

                            // Renderizar la página en el canvas
                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    });
                </script>
            <?php elseif (strpos($doc['file_type'], 'image/') === 0): ?>
                <!-- Miniatura para imágenes -->
                <img src="../docente/uploads/<?= urlencode($doc['file_name']) ?>" 
                     class="img-fluid" 
                     style="max-height: 200px; object-fit: cover;" 
                     alt="Vista previa de <?= htmlspecialchars($doc['file_name']) ?>">
            <?php else: ?>
                <!-- Texto alternativo para archivos no compatibles con vista previa -->
                <div class="d-flex align-items-center justify-content-center" style="height: 200px; background-color: #f5f5f5;">
                    <p>Vista previa no disponible</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Información del documento -->
        <div class="card-body">
    <h5 class="card-title"><?= htmlspecialchars($doc['file_name']) ?></h5>
    <p class="card-text">Subido por: <?= htmlspecialchars($doc['docente_nombre']) ?></p>
    <p class="card-text"><?= htmlspecialchars($doc['comment'] ?: 'Sin comentarios') ?></p>
    <p><small class="text-muted"><?= date("d/m/Y", strtotime($doc['uploaded_at'])) ?></small></p>
    <a href="../docente/uploads/<?= urlencode($doc['file_name']) ?>" class="btn btn-primary" target="_blank">Ver Documento</a>
    <a href="editar_documento.php?id=<?= $doc['id'] ?>" class="btn btn-warning">Editar</a>
    <a href="eliminar_documento.php?id=<?= $doc['id'] ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar este documento?')">Eliminar</a>
</div>

    </div>
</div>


            <?php endwhile; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <p>No hay documentos disponibles.</p>
    <?php endif; ?>
</div>
</body>
<script src="https://mozilla.github.io/pdf.js/build/pdf.js"></script>

</html>


