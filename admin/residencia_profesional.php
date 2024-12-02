<?php include 'header.php'; ?>
<?php

// Obtener la lista de alumnos de tipo "residencia"
$query = "SELECT id, nombre FROM alumnos WHERE tipo_alumno = 'residente'";
$stmt = $conn->prepare($query);
$stmt->execute();
$alumnos = $stmt->get_result();

// Manejar la selección de un alumno para ver sus documentos
$selected_alumno_id = null;
$documentos = [];

if (isset($_GET['alumno_id'])) {
    $selected_alumno_id = $_GET['alumno_id'];

    // Obtener los documentos del alumno seleccionado
    $query = "SELECT id, file_name, file_type, comment FROM residencia_documentos WHERE alumno_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $selected_alumno_id);
    $stmt->execute();
    $documentos = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Gestión de Residencias Profesionales</title>
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
        .card {
            margin-bottom: 1rem;
        }
        .file-list {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .file-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem;
        }
        .file-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    

    <div class="container main-content">
        <h1 class="mb-4">Gestión de Alumnos de Residencias Profesionales</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card file-list">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users"></i> Alumnos de Residencias</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php while ($alumno = $alumnos->fetch_assoc()) : ?>
                                <li class="list-group-item">
                                    <a href="?alumno_id=<?= $alumno['id']; ?>" class="text-decoration-none">
                                        <?= $alumno['nombre']; ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <?php if ($selected_alumno_id): ?>
                    <div class="card file-list">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos de Alumno</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($documentos->num_rows > 0): ?>
                                <ul class="list-group">
                                    <?php while ($doc = $documentos->fetch_assoc()) : ?>
                                        <li class="list-group-item">
                                            <strong>Archivo:</strong> 
                                            <a href="../docente/uploads/<?= $doc['file_name']; ?>" target="_blank"><?= $doc['file_name']; ?></a><br>
                                            <strong>Tipo de Documento:</strong> <?= $doc['file_type']; ?><br>
                                            <strong>Comentarios:</strong> <?= $doc['comment']; ?>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>No hay documentos para este alumno.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
