<?php
session_start();
include '../db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Mostrar archivos subidos
$query = "SELECT * FROM acuerdos_academicos_documentos WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';  // Incluir el header
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos de Acuerdos Académicos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="container main-content mt-4">
        <h1 class="mb-4">Documentos de Acuerdos Académicos</h1>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos Subidos</h5>
                    </div>
                    <div class="card-body file-list">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="file-item mb-4">
                                    <h6 class="mb-2"><?php echo htmlspecialchars($row['file_name']); ?></h6>
                                    <p class="mb-1"><strong>Comentario:</strong> <?php echo htmlspecialchars($row['comment']); ?></p>
                                    <p class="mb-2"><small class="text-muted">Subido el: <?php echo $row['uploaded_at']; ?></small></p>
                                    
                                    <?php 
                                    $file_extension = pathinfo($row['file_name'], PATHINFO_EXTENSION);
                                    $file_path = 'uploads/' . $row['file_name'];
                                    
                                    // Botón para descargar el archivo
                                    ?>
                                    <a href="<?php echo $file_path; ?>" class="btn btn-success mb-3" download>
                                        <i class="fas fa-download"></i> Descargar <?php echo htmlspecialchars($row['file_name']); ?>
                                    </a>
                                    
                                    <?php 
                                    // Vista previa para PDF sin barra de herramientas
                                    if ($file_extension == 'pdf'): ?>
                                        <iframe src="<?php echo $file_path; ?>#toolbar=0" width="100%" height="500px"></iframe>
                                    <?php 
                                    // Enlace de descarga para otros archivos
                                    elseif (in_array($file_extension, ['doc', 'docx'])): ?>
                                        <a href="<?php echo $file_path; ?>" class="btn btn-primary" target="_blank">
                                            <i class="fas fa-download"></i> Descargar <?php echo htmlspecialchars($row['file_name']); ?>
                                        </a>
                                    <?php endif; ?>
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
