<?php
// Verificar si ya hay una sesión iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario es docente
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'docente') {
    header("Location: ../login.php");
    exit();
}


include 'header.php';  // Incluir el header
// Conexión a la base de datos
include '../db.php';

// Si el formulario para crear un alumno ha sido enviado
if (isset($_POST['crear_alumno'])) {
    $nombre = $_POST['nombre'];
    $matricula = $_POST['matricula'];

    // Se agrega 'residente' como tipo de alumno al insertar
    $sql = "INSERT INTO alumnos (user_id, nombre, matricula, tipo_alumno) VALUES ('" . $_SESSION['user_id'] . "', '$nombre', '$matricula', 'residente')";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Alumno creado exitosamente.";
    } else {
        $error_message = "Error al crear el alumno: " . $conn->error;
    }
}

// Si el formulario para subir un documento ha sido enviado
if (isset($_POST['subir_documento'])) {
    $alumno_id = $_POST['alumno_id'];
    $tipo_documento = $_POST['tipo_documento'];
    $archivo = $_FILES['archivo']['name'];
    $ruta = "uploads/" . basename($archivo);
    $comentario = $_POST['comentario'];  // Nuevo campo de comentario
    
    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta)) {
        $sql = "INSERT INTO residencia_documentos (alumno_id, user_id, file_name, file_type, comment) VALUES ('$alumno_id', '" . $_SESSION['user_id'] . "', '$archivo', '$tipo_documento', '$comentario')";
        if ($conn->query($sql) === TRUE) {
            $success_message = "Documento subido exitosamente.";
        } else {
            $error_message = "Error al subir el documento: " . $conn->error;
        }
    } else {
        $error_message = "Error al mover el archivo.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residencia Profesional - Gestión</title>
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
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    

    <div class="container main-content">
        <h1 class="mb-4">Gestión de Documentos y Alumnos Residencia profesional</h1>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-user-plus"></i> Agregar Alumno</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Alumno:</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="matricula" class="form-label">Matrícula:</label>
                                <input type="text" class="form-control" name="matricula" id="matricula" required>
                            </div>
                            <button type="submit" name="crear_alumno" class="btn btn-primary"><i class="fas fa-user-plus"></i> Agregar Alumno</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-upload"></i> Subir Documento</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="alumno_id" class="form-label">Seleccionar Alumno:</label>
                                <select class="form-select" id="alumno_id" name="alumno_id" required>
                                    <option value="">Selecciona un alumno</option>
                                    <?php
                                    // Selecciona solo alumnos de tipo 'residente'
                                    $sql = "SELECT id, nombre FROM alumnos WHERE user_id = ? AND tipo_alumno = 'residente'";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['nombre'] . "</option>";
                                    }
                                    $stmt->close();
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="tipo_documento" class="form-label">Tipo de Documento:</label>
                                <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                    <option value="1">Formato de Evaluación y Sebuimiento de Residencia Profesional</option>
                                    <option value="2">Formato de Evaluación de Reporte de Residencia Profesional</option>
                                    <option value="3">Carta de Término</option>
                                    <option value="4">Proyecto y/o Informe de Residencia Profesional</option>
                                    <option value="5">Informe Semestral de Residencia Profesional</option>
                                    <option value="7">Oficio de Liberación de Residencia Profesional</option>
                                    <option value="8">Acta de Calificación de Residencia Profesional</option>
                                    






                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="archivo" class="form-label">Seleccionar Archivo</label>
                                <input type="file" class="form-control" id="archivo" name="archivo" required accept=".pdf, .doc, .docx">
                            </div>

                            <div class="mb-3">
                                <label for="comentario" class="form-label">Comentario (opcional):</label>
                                <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                            </div>

                            <button type="submit" name="subir_documento" class="btn btn-primary"><i class="fas fa-upload"></i> Subir Documento</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card file-list">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Documentos Subidos</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Tipo de Documento</th>
                                    <th>Archivo</th>
                                    <th>Comentario</th>  <!-- Nueva columna para comentario -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT alumnos.nombre, residencia_documentos.file_name, residencia_documentos.file_type, residencia_documentos.comment
                                        FROM residencia_documentos
                                        JOIN alumnos ON residencia_documentos.alumno_id = alumnos.id
                                        WHERE alumnos.user_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $_SESSION['user_id']);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>{$row['nombre']}</td>
                                            <td>{$row['file_type']}</td>
                                            <td><a href='uploads/{$row['file_name']}' target='_blank'>Ver Archivo</a></td>
                                            <td>{$row['comment']}</td> <!-- Mostrar comentario -->
                                          </tr>";
                                }
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
