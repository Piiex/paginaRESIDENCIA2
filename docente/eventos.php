<?php
session_start();
include 'header.php';
include '../db.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$user_id = $_SESSION['user_id'];

// Obtener los eventos desde la base de datos
$stmt = $conn->prepare("SELECT * FROM eventos ORDER BY fecha_creacion DESC");
$stmt->execute();
$result = $stmt->get_result();
$eventos = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-content {
            margin-top: 2rem;
        }
        .event-card {
            margin-bottom: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .event-card img {
            cursor: pointer;
            transition: transform 0.3s;
        }
        .event-card img:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="container main-content">
        <h1 class="mb-4">Eventos</h1>
        <div class="row">
            <?php if (count($eventos) > 0): ?>
                <?php foreach ($eventos as $evento): ?>
                    <div class="col-md-4">
                        <div class="card event-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($evento['descripcion']); ?></p>
                                <p class="card-text"><strong>Fecha de Inicio:</strong> <?php echo htmlspecialchars($evento['fecha_inicio']); ?></p>
                                <p class="card-text"><strong>Fecha de Fin:</strong> <?php echo htmlspecialchars($evento['fecha_fin']); ?></p>
                                <p class="card-text"><strong>Evidencias:</strong></p>
                                <div class="gallery">
                                    <?php
                                    $carpetaImagenes = '../admin/evidencias/'; // Define la ruta base
                                    $imagenes = explode(',', $evento['evidencias']); // Suponiendo que las evidencias son nombres de archivos separados por comas
                                    foreach ($imagenes as $imagen): ?>
                                        <img src="<?php echo htmlspecialchars($carpetaImagenes . trim($imagen)); ?>" alt="Evidencia" class="img-fluid" style="max-height: 100px; margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $evento['id']; ?>">
                                    <?php endforeach; ?>
                                </div>
                                <p class="card-text"><small class="text-muted">Creado el: <?php echo htmlspecialchars($evento['fecha_creacion']); ?></small></p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para imágenes -->
                    <div class="modal fade" id="modal-<?php echo $evento['id']; ?>" tabindex="-1" aria-labelledby="modalLabel-<?php echo $evento['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel-<?php echo $evento['id']; ?>"><?php echo htmlspecialchars($evento['titulo']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <?php foreach ($imagenes as $imagen): ?>
                                        <img src="<?php echo htmlspecialchars($carpetaImagenes . trim($imagen)); ?>" alt="Evidencia" class="img-fluid" style="margin-bottom: 10px;">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay eventos disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

