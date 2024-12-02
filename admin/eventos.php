<?php
include 'header.php';

// Manejo de la creación y edición de eventos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $evidencias = $_FILES['evidencias']['name'];
        $target_dir = "evidencias/";
        $target_file = $target_dir . basename($_FILES["evidencias"]["name"]);

        move_uploaded_file($_FILES["evidencias"]["tmp_name"], $target_file);

        $sql = "INSERT INTO eventos (titulo, descripcion, fecha_inicio, fecha_fin, evidencias, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $titulo, $descripcion, $fecha_inicio, $fecha_fin, $evidencias);
        $stmt->execute();
    } elseif (isset($_POST['edit_event'])) {
        $id = $_POST['id'];
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];

        if (!empty($_FILES['evidencias']['name'])) {
            $evidencias = $_FILES['evidencias']['name'];
            $target_dir = "evidencias/";
            $target_file = $target_dir . basename($_FILES["evidencias"]["name"]);
            move_uploaded_file($_FILES["evidencias"]["tmp_name"], $target_file);
        } else {
            $evidencias = $_POST['old_evidencias'];
        }

        $sql = "UPDATE eventos SET titulo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, evidencias = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $titulo, $descripcion, $fecha_inicio, $fecha_fin, $evidencias, $id);
        $stmt->execute();
    } elseif (isset($_POST['delete_event'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM eventos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    // Redireccionar para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Obtener eventos
$sql = "SELECT * FROM eventos ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --border-radius: px;
            --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f0f2f5;
            padding: 0rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            
            color: var(--primary-color);
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Form Styles */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #357abd;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .event-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            cursor: pointer;
        }

        .event-content {
            padding: 1.5rem;
        }

        .event-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .event-description {
            color: #666;
            margin-bottom: 1rem;
        }

        .event-dates {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .event-actions {
            display: flex;
            gap: 1rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal.show {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            transition: color 0.2s;
        }

        .modal-close:hover {
            color: var(--danger-color);
        }

        /* Image Preview Modal */
        .image-preview-modal {
            background-color: rgba(0,0,0,0.9);
        }

        .image-preview-content {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            padding: 2rem;
        }

        .preview-image {
            max-width: 90%;
            max-height: 90vh;
            object-fit: contain;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Gestión de Eventos</h1>

        <!-- Formulario de Agregar Evento -->
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Evidencias</label>
                    <input type="file" name="evidencias" class="form-control" accept="image/*" required>
                </div>
                <button type="submit" name="add_event" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar Evento
                </button>
            </form>
        </div>

        <!-- Grid de Eventos -->
        <div class="events-grid">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <div class="event-card">
                    <img src="evidencias/<?php echo htmlspecialchars($row['evidencias']); ?>" 
                         alt="<?php echo htmlspecialchars($row['titulo']); ?>"
                         class="event-image"
                         onclick="openImagePreview('evidencias/<?php echo htmlspecialchars($row['evidencias']); ?>')">
                    <div class="event-content">
                        <h3 class="event-title"><?php echo htmlspecialchars($row['titulo']); ?></h3>
                        <p class="event-description"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                        <div class="event-dates">
                            <span><i class="fas fa-calendar-start"></i> <?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></span>
                            <span><i class="fas fa-calendar-end"></i> <?php echo date('d/m/Y', strtotime($row['fecha_fin'])); ?></span>
                        </div>
                        <div class="event-actions">
                            <button class="btn btn-primary" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_event" class="btn btn-danger" 
                                        onclick="return confirm('¿Estás seguro de eliminar este evento?');">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Modal de Edición -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal('editModal')">&times;</span>
                <h2>Editar Evento</h2>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-group">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" id="editTitulo" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="editDescripcion" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="editFechaInicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="editFechaFin" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Evidencia (opcional)</label>
                        <input type="file" name="evidencias" id="editEvidencias" class="form-control" accept="image/*">
                    </div>
                    <input type="hidden" name="old_evidencias" id="oldEvidencias">
                    <button type="submit" name="edit_event" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Evento
                    </button>
                </form>
            </div>
        </div>

        <!-- Modal de Vista Previa de Imagen -->
        <div id="imagePreviewModal" class="modal image-preview-modal">
            <span class="modal-close" onclick="closeModal('imagePreviewModal')">&times;</span><div class="image-preview-content">
                <img id="previewImage" src="" alt="Vista previa" class="preview-image">
            </div>
        </div>
    </div>

    <script>
        // Función para abrir el modal de edición
        function openEditModal(event) {
            document.getElementById("editId").value = event.id;
            document.getElementById("editTitulo").value = event.titulo;
            document.getElementById("editDescripcion").value = event.descripcion;
            document.getElementById("editFechaInicio").value = event.fecha_inicio;
            document.getElementById("editFechaFin").value = event.fecha_fin;
            document.getElementById("oldEvidencias").value = event.evidencias;

            const modal = document.getElementById("editModal");
            modal.classList.add("show");
            document.body.style.overflow = 'hidden';
        }

        // Función para abrir el modal de vista previa de imagen
        function openImagePreview(imageSrc) {
            const modal = document.getElementById("imagePreviewModal");
            const previewImage = document.getElementById("previewImage");
            previewImage.src = imageSrc;
            modal.classList.add("show");
            document.body.style.overflow = 'hidden';

            // Añadir controles de zoom con la rueda del mouse
            previewImage.addEventListener('wheel', function(e) {
                e.preventDefault();
                let scale = 1;
                if (this.style.transform) {
                    scale = parseFloat(this.style.transform.replace('scale(', '').replace(')', ''));
                }
                
                if (e.deltaY < 0) {
                    // Zoom in
                    scale = Math.min(scale + 0.1, 3);
                } else {
                    // Zoom out
                    scale = Math.max(scale - 0.1, 0.5);
                }
                
                this.style.transform = `scale(${scale})`;
            });
        }

        // Función para cerrar cualquier modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove("show");
            document.body.style.overflow = 'auto';
            
            // Si es el modal de imagen, resetear el zoom
            if (modalId === 'imagePreviewModal') {
                document.getElementById("previewImage").style.transform = 'scale(1)';
            }
        }

        // Cerrar modales al hacer clic fuera de ellos
        window.onclick = function(event) {
            const modals = document.getElementsByClassName("modal");
            for (let modal of modals) {
                if (event.target === modal) {
                    modal.classList.remove("show");
                    document.body.style.overflow = 'auto';
                    if (modal.id === 'imagePreviewModal') {
                        document.getElementById("previewImage").style.transform = 'scale(1)';
                    }
                }
            }
        }

        // Prevenir que el formulario se envíe si se presiona Enter
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        // Animación suave al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.event-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Preview de imagen antes de subir
        document.querySelector('input[name="evidencias"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.style.maxWidth = '200px';
                    preview.style.marginTop = '10px';
                    
                    // Eliminar preview anterior si existe
                    const oldPreview = this.parentElement.querySelector('.upload-preview');
                    if (oldPreview) {
                        oldPreview.remove();
                    }
                    
                    preview.classList.add('upload-preview');
                    this.parentElement.appendChild(preview);
                }.bind(this);
                reader.readAsDataURL(file);
            }
        });

        // Validación de fechas
        document.querySelector('input[name="fecha_fin"]').addEventListener('change', function() {
            const fechaInicio = document.querySelector('input[name="fecha_inicio"]').value;
            const fechaFin = this.value;
            
            if (fechaFin < fechaInicio) {
                alert('La fecha de fin no puede ser anterior a la fecha de inicio');
                this.value = fechaInicio;
            }
        });
    </script>
</body>
</html>

