<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Documentos de Docentes</title>
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
            transition: transform 0.3s ease-in-out;
            margin-bottom: 2rem;
        }
        .docente-card:hover {
            transform: translateY(-5px);
        }
        .document-card {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body><?php include 'header.php'; ?>
    <?php
      

    // Obtener todos los docentes y sus documentos
    $sql = "SELECT u.id as docente_id, u.nombre as docente_nombre, d.id as documento_id, d.file_name, d.file_type, d.comment, d.upload_date
            FROM usuarios u
            LEFT JOIN tutorias_documentos d ON u.id = d.user_id
            WHERE u.role = 'docente'
            ORDER BY u.nombre, d.upload_date";
    $result = $conn->query($sql);
    
    $docentes = [];
    while ($row = $result->fetch_assoc()) {
        $docentes[$row['docente_id']]['nombre'] = $row['docente_nombre'];
        if ($row['documento_id']) {
            $docentes[$row['docente_id']]['documentos'][] = $row;
        }
    }
    ?>

    <div class="container main-content">
        <h1 class="mb-4">Docentes y sus Documentos Tutorias</h1>

        <?php if (!empty($docentes)): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($docentes as $docente_id => $docente): ?>
                    <div class="col">
                        <div class="card h-100 docente-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($docente['nombre']); ?></h5>
                                <?php if (!empty($docente['documentos'])): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($docente['documentos'] as $documento): ?>
                                            <?php $file_name = str_replace(' ', '%20', $documento['file_name']); ?>
                                            <li class="list-group-item document-card">
                                                <h6><?php echo htmlspecialchars($documento['file_name']); ?></h6>
                                                <p><strong>Comentario:</strong> <?php echo htmlspecialchars($documento['comment']); ?></p>
                                                <a href="../docente/uploads/<?php echo $file_name; ?>" class="btn btn-primary btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <a href="../docente/uploads/<?php echo $file_name; ?>" class="btn btn-success btn-sm" download>
                                                    <i class="fas fa-download"></i> Descargar
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <form action="acciones/download_zip.php" method="post">
                                        <input type="hidden" name="files" value="<?php echo htmlentities(serialize(array_column($docente['documentos'], 'file_name'))); ?>">
                                        <input type="hidden" name="docente_nombre" value="<?php echo htmlspecialchars($docente['nombre']); ?>">
                                        <button type="submit" class="btn btn-success mt-3">
                                            <i class="fas fa-download"></i> Descargar todos los archivos en ZIP
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p>No se encontraron documentos para este docente.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No hay docentes registrados.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
