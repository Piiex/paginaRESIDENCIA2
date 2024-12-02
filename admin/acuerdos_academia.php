<?php include 'header.php'; ?>

<?php
// Obtener los docentes con documentos subidos en acuerdos_academicos_documentos
$sql = "SELECT u.id as user_id, u.nombre, d.file_name, d.file_type, d.comment, d.uploaded_at 
        FROM usuarios u
        JOIN acuerdos_academicos_documentos d ON u.id = d.user_id
        WHERE u.role = 'docente'";
$result = $conn->query($sql);

$docentes = [];
if ($result && $result->num_rows > 0) {
    // Agrupar documentos por docente
    while ($row = $result->fetch_assoc()) {
        $docentes[$row['nombre']][] = $row;
    }
} else {
    $error_message = "No se encontraron documentos.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acuerdos Académicos - Documentos Subidos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .docente-container {
            margin-bottom: 1.5rem;
        }
        .docente-header {
            background-color: #28a745;
            color: white;
            padding: 8px;
            border-radius: 5px;
        }
        .docente-body {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .preview {
            margin-top: 10px;
            display: inline-block;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Acuerdos Académicos - Documentos Subidos</h1>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php else: ?>
            <?php foreach ($docentes as $nombre => $documentos): ?>
                <div class="docente-container">
                    <div class="docente-header">
                        <h2><?php echo htmlspecialchars($nombre); ?></h2>
                    </div>
                    <div class="docente-body">
                        <ul class="list-group">
                            <?php foreach ($documentos as $documento): ?>
                                <li class="list-group-item">
                                    <strong>Archivo:</strong> <?php echo htmlspecialchars($documento['file_name']); ?><br>
                                    <strong>Tipo:</strong> <?php echo htmlspecialchars($documento['file_type']); ?><br>
                                    <strong>Comentario:</strong> <?php echo htmlspecialchars($documento['comment']); ?><br>
                                    <strong>Subido en:</strong> <?php echo htmlspecialchars($documento['uploaded_at']); ?><br>
                                    <?php if (in_array($documento['file_type'], ['image/png', 'image/jpeg', 'image/jpg'])): ?>
                                        <img src="ruta_a_archivo/<?php echo htmlspecialchars($documento['file_name']); ?>" alt="Vista previa" class="preview">
                                    <?php elseif ($documento['file_type'] === 'application/pdf'): ?>
                                        <embed src="ruta_a_archivo/<?php echo htmlspecialchars($documento['file_name']); ?>" type="application/pdf" width="100" height="100" />
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
