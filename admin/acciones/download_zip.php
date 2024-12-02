<?php
if (isset($_POST['files']) && isset($_POST['docente_nombre'])) {
    $files = unserialize(html_entity_decode($_POST['files']));
    $docente_nombre = preg_replace('/[^a-zA-Z0-9_-]/', '_', $_POST['docente_nombre']); // Limpiar nombre del docente para el ZIP
    $tempDir = sys_get_temp_dir() . '/asesorias_' . $docente_nombre . '/';

    // Crear un directorio temporal para almacenar los archivos
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Copiar los archivos a la carpeta temporal
    foreach ($files as $file) {
        if (file_exists($file)) {
            copy($file, $tempDir . basename($file));
        }
    }

    // Nombre del archivo ZIP
    $zip_name = 'asesorias_' . $docente_nombre . '.zip';
    $zip_path = $tempDir . $zip_name;
    $zip = new ZipArchive;

    // Crear el archivo ZIP
    if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
        foreach ($files as $file) {
            $zip->addFile($tempDir . basename($file), basename($file)); // Añadir al ZIP desde la carpeta temporal
        }
        $zip->close();

        // Forzar la descarga del archivo ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_name . '"');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);

        // Eliminar el archivo ZIP y los archivos temporales
        unlink($zip_path); // Borrar el ZIP
        foreach ($files as $file) {
            unlink($tempDir . basename($file)); // Borrar cada archivo
        }
        rmdir($tempDir); // Eliminar el directorio temporal
        exit();
    } else {
        echo 'Error al crear el archivo ZIP.';
    }
}
?>

