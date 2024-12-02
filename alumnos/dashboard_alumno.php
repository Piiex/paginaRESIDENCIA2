<?php
session_start();
include ('header.php');
require_once('db.php');

// Función para exportar a CSV
if (isset($_POST['exportar_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reportes_residencia.csv');

    $output = fopen('php://output', 'w');

    // Encabezados del CSV
    fputcsv($output, ['TECNOLOGICO DE ESTUDIOS SUPERIORES DE VILLLA GUERRERO', '', 'FECHA REVISION', '', 'LOGO CARRERA']);
    fputcsv($output, ['NOMBRE DE LA CARRERA', 'NOMBRE DEL ESTUDIANTE', 'REPORTE', '1', '']);
    fputcsv($output, ['RESIDENCIA PROFESIONAL']);
    fputcsv($output, ['FECHA', 'FECHA DE ELABORACION DE REPORTE', '', '', '']);
    fputcsv($output, ['']);
    fputcsv($output, ['SEMANA 1']);
    fputcsv($output, ['SEMANA', 'FECHA', 'ACTIVIDAD', 'HORARIO', 'OBSERVACIONES']);

    // Consultar datos
    $stmt = $conn->prepare("SELECT * FROM alumnos_reportesResidencia WHERE usuario_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($row = $resultado->fetch_assoc()) {
        fputcsv($output, [
            $row['semana'],
            $row['fecha'],
            $row['actividad'],
            $row['horario'],
            $row['observaciones']
        ]);
    }
    fclose($output);
    exit();
}

// Insertar reporte en la tabla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['exportar_csv'])) {
    $evidencias = "";
    if (isset($_FILES['evidencias']['name']) && $_FILES['evidencias']['name'] != "") {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["evidencias"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $evidencias = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["evidencias"]["tmp_name"], $evidencias)) {
            // Éxito
        } else {
            $mensaje_error = "Error al subir el archivo";
        }
    }

    $stmt = $conn->prepare("INSERT INTO alumnos_reportesResidencia 
        (semana, actividad, evidencias, observaciones, fecha, usuario_id, horario, fecha_elaboracion, id_alumno) 
        VALUES (?, ?, ?, ?, CURDATE(), ?, ?, CURDATE(), ?)");

    $stmt->bind_param("issssis", $_POST['semana'], $_POST['actividad'], $evidencias, $_POST['observaciones'], $_SESSION['user_id'], $_POST['horario'], $_SESSION['user_id']);

    if ($stmt->execute()) {
        $mensaje = "Reporte guardado exitosamente";
         header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $mensaje_error = "Error al guardar el reporte: " . $stmt->error;
    }
}

// Consultar reportes del usuario actual
$stmt = $conn->prepare("SELECT ar.*, u.nombre as nombre_alumno 
                        FROM alumnos_reportesResidencia ar 
                        JOIN usuarios u ON ar.usuario_id = u.id 
                        WHERE ar.usuario_id = ?
                        ORDER BY ar.fecha DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reportes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes de Residencia</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <style>
        .img-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin-top: 10px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Mensajes de éxito y error -->
        <?php if (isset($mensaje)): ?>
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 border border-green-400">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($mensaje_error)): ?>
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-700 border border-red-400">
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>

        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Reportes de Residencia</h1>
            <form method="POST" class="inline">
                <button type="submit" name="exportar_csv" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar a CSV
                </button>
            </form>
        </div>

        <!-- Formulario de nuevo reporte -->
        <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Nuevo Reporte</h2>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="semana" class="block text-gray-700 font-semibold mb-2">Semana</label>
                        <input type="number" name="semana" required 
                               class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="horario" class="block text-gray-700 font-semibold mb-2">Horario</label>
                        <input type="text" name="horario" required placeholder="Ej: 9:00 - 14:00" 
                               class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label for="actividad" class="block text-gray-700 font-semibold mb-2">Actividad</label>
                        <textarea name="actividad" required rows="3" 
                                  class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Describe las actividades realizadas..."></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="evidencias" class="block text-gray-700 font-semibold mb-2">Evidencias</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col w-full h-32 border-4 border-dashed hover:bg-gray-100 hover:border-gray-300">
                                <div class="flex flex-col items-center justify-center pt-7">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500">Arrastra y suelta el archivo aquí o <span class="text-blue-500">explora</span></p>
                                </div>
                                <input type="file" name="evidencias" class="hidden" accept="image/*" onchange="previewImage(event)">
                            </label>
                        </div>
                        <img id="preview" class="img-preview" src="#" alt="Vista previa" style="display:none;">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observaciones" class="block text-gray-700 font-semibold mb-2">Observaciones</label>
                        <textarea name="observaciones" required rows="3" 
                                  class="shadow-sm appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Escribe tus observaciones..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Guardar Reporte</button>
                </div>
            </form>
        </div>

        <!-- Tabla de reportes -->
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Historial de Reportes</h2>
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Semana</th>
                    <th class="py-3 px-6 text-left">Actividad</th>
                    <th class="py-3 px-6 text-left">Evidencia</th>
                    <th class="py-3 px-6 text-left">Observaciones</th>
                    <th class="py-3 px-6 text-left">Fecha</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php foreach ($reportes as $reporte): ?>
                    <tr class="border-b border-gray-300 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left"><?php echo $reporte['semana']; ?></td>
                        <td class="py-3 px-6 text-left"><?php echo $reporte['actividad']; ?></td>
                        <td class="py-3 px-6 text-left">
                            <?php if ($reporte['evidencias']): ?>
                                <a href="<?php echo $reporte['evidencias']; ?>" data-lightbox="evidencia" data-title="<?php echo $reporte['actividad']; ?>">
                                    <img src="<?php echo $reporte['evidencias']; ?>" class="img-preview" alt="Evidencia">
                                </a>
                            <?php else: ?>
                                Sin evidencia
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-6 text-left"><?php echo $reporte['observaciones']; ?></td>
                        <td class="py-3 px-6 text-left"><?php echo date('d/m/Y', strtotime($reporte['fecha'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }

                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>


