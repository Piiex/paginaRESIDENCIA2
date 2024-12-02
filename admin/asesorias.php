<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Docentes y Documentos</title>
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
        .docente-card {
            margin-bottom: 2rem;
        }
        .documento-card {
            margin-bottom: 1rem;
        }
        .preview-image {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

    <?php
    // Función para generar vista previa
    function generatePreview($filePath, $fileType) {
        $preview = '';
        if (strpos($fileType, 'image/') === 0) {
            $preview = "<img src='$filePath' class='preview-image' alt='Vista previa'>";
        } elseif ($fileType === 'application/pdf') {
            $preview = "<embed src='$filePath' type='application/pdf' width='100%' height='200px'>";
        } else {
            $preview = "<p>Vista previa no disponible para este tipo de archivo.</p>";
        }
        return $preview;
    }

    // Consulta para obtener docentes
    $docentes_query = "SELECT id, nombre FROM usuarios WHERE role = 'docente'";
    $docentes_result = $conn->query($docentes_query);
    ?>

    <div class="container main-content">
        <h1 class="mb-4">Docentes y sus Documentos Asesorias</h1>

        <?php if ($docentes_result->num_rows > 0): ?>
            <?php while ($docente = $docentes_result->fetch_assoc()): ?>
                <div class="card docente-card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($docente['nombre']); ?> (ID: <?php echo $docente['id']; ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php
                        // Consulta para obtener documentos del docente
                        $docs_query = "SELECT * FROM asesorias_documentos WHERE usuario_id = " . $docente['id'];
                        $docs_result = $conn->query($docs_query);

                        if ($docs_result->num_rows > 0):
                        ?>
                            <h3>Documentos registrados:</h3>
                            <div class="row">
                            <?php 
                            $files = []; // Para almacenar los archivos que se agregarán al ZIP
                            while ($doc = $docs_result->fetch_assoc()): 
                                $filePath = "../docente/uploads/" . $doc['file_name']; // Ajusta esta ruta según tu estructura de directorios
                                $preview = generatePreview($filePath, $doc['file_type']);
                                $files[] = $filePath; // Agregar el archivo al arreglo
                            ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card documento-card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($doc['file_name']); ?></h5>
                                            <p class="card-text">
                                                <strong>Tipo:</strong> <?php echo htmlspecialchars($doc['file_type']); ?><br>
                                                <strong>Subido el:</strong> <?php echo htmlspecialchars($doc['uploaded_at']); ?><br>
                                                <strong>Comentario:</strong> <?php echo htmlspecialchars($doc['comment']); ?>
                                            </p>
                                            <div class="preview-container">
                                                <?php echo $preview; ?>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <a href="<?php echo $filePath; ?>" class="btn btn-primary btn-sm" target="_blank">
                                                <i class="fas fa-eye"></i> Ver completo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            </div>

                            <!-- Botón para descargar todos los archivos en un ZIP -->
                            <form action="acciones/download_zip.php" method="post">
    <input type="hidden" name="files" value="<?php echo htmlentities(serialize($files)); ?>">
    <input type="hidden" name="docente_nombre" value="<?php echo htmlspecialchars($docente['nombre']); ?>">
    <button type="submit" class="btn btn-success">
        <i class="fas fa-download"></i> Descargar todos los archivos en ZIP
    </button>
</form>


                        <?php else: ?>
                            <p>Este docente no tiene documentos registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No hay docentes registrados.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
