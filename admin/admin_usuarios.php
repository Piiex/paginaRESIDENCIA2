<?php
include 'header.php';
require_once 'db.php';


// Verificar si el usuario tiene permiso para acceder a esta vista
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Agregar usuario
if (isset($_POST['add_user'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $estado = $_POST['estado'];
    $role = $_POST['role'];
    $encargado = $_POST['encargado'];
    $permissions = $_POST['permissions'];

    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, clave, estado, role, encargado, permissions) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sssssss", $nombre, $correo, $clave, $estado, $role, $encargado, $permissions);
        if (!$stmt->execute()) {
            echo "Error al insertar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta: " . $conn->error;
    }
}

// Editar usuario
if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $estado = $_POST['estado'];
    $role = $_POST['role'];
    $encargado = $_POST['encargado'];

    // Obtener el valor actual de permissions
    $stmt = $conn->prepare("SELECT permissions FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_permissions = $result->fetch_assoc()['permissions'];
    $stmt->close();

    // Asignar permissions desde el formulario, o mantener el valor actual si no se envió
    $permissions = $_POST['permissions'] ?? $current_permissions;

    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, correo = ?, estado = ?, role = ?, encargado = ?, permissions = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ssssssi", $nombre, $correo, $estado, $role, $encargado, $permissions, $id);
        if (!$stmt->execute()) {
            echo "Error al actualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta: " . $conn->error;
    }
}


// Obtener usuarios
$usuarios = $conn->query("SELECT * FROM usuarios");

?>

<div class="container mt-4">
    <h2>Gestión de Usuarios</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Agregar Usuario</button>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Rol</th>
                <th>Coordinador</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $usuario['id']; ?></td>
                    <td><?php echo $usuario['nombre']; ?></td>
                    <td><?php echo $usuario['correo']; ?></td>
                    <td><?php echo $usuario['estado']; ?></td>
                    <td><?php echo $usuario['role']; ?></td>
                    <td><?php echo $usuario['encargado']; ?></td>
                    <td>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                data-id="<?php echo $usuario['id']; ?>" 
                                data-nombre="<?php echo $usuario['nombre']; ?>" 
                                data-correo="<?php echo $usuario['correo']; ?>" 
                                data-estado="<?php echo $usuario['estado']; ?>" 
                                data-role="<?php echo $usuario['role']; ?>" 
                                data-encargado="<?php echo $usuario['encargado']; ?>" 
                                data-permissions="<?php echo htmlspecialchars($usuario['permissions']); ?>">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para agregar usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Agregar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="clave" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="clave" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-select" name="role">
                            <option value="admin">Admin</option>
                            <option value="docente">Docente</option>
                            <option value="alumnos">Alumnos</option>
                            <option value="sin_cargo">Sin rol</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="encargado" class="form-label">Coordinador</label>
                        <select class="form-select" name="encargado">
                            <option value="">Selecciona un cargo al coordinador</option>
                            <option value="encargado_tutorias">Encargado Tutorías</option>
                            <option value="encargado_asesorias">Encargado Asesorías</option>
                            <option value="encargado_educacion_dual">Encargado Educación Dual</option>
                            <option value="encargado_residencia_profesional">Encargado Residencia Profesional</option>
                            <option value="encargado_instrumentacion_didactica">Encargado Instrumentación Didáctica</option>
                            <option value="encargado_trayectoria_escolar">Encargado Trayectoria Escolar</option>
                            <option value="encargado_atributos_egreso">Encargado Atributos de Egreso</option>
                            <option value="encargado_investigacion">Encargado Investigación</option>
                            <option value="encargado_acuerdos_academia">Encargado Acuerdos Academia</option>
                        </select>
                    </div>
                    <button type="submit" name="add_user" class="btn btn-primary">Agregar Usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_correo" class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo" id="edit_correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_estado" class="form-label">Estado</label>
                        <select class="form-select" name="estado" id="edit_estado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Rol</label>
                        <select class="form-select" name="role" id="edit_role">
                            <option value="admin">Admin</option>
                            <option value="docente">Docente</option>
                            <option value="alumnos">Alumnos</option>
                            <option value="sin_cargo">Sin rol</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_encargado" class="form-label">Coordinador</label>
                        <select class="form-select" name="encargado" id="edit_encargado">
                            <option value="">Selecciona un encargado</option>
                            <option value="encargado_tutorias">Encargado Tutorías</option>
                            <option value="encargado_asesorias">Encargado Asesorías</option>
                            <option value="encargado_educacion_dual">Encargado Educación Dual</option>
                            <option value="encargado_residencia_profesional">Encargado Residencia Profesional</option>
                            <option value="encargado_instrumentacion_didactica">Encargado Instrumentación Didáctica</option>
                            <option value="encargado_trayectoria_escolar">Encargado Trayectoria Escolar</option>
                            <option value="encargado_atributos_egreso">Encargado Atributos de Egreso</option>
                            <option value="encargado_investigacion">Encargado Investigación</option>
                            <option value="encargado_acuerdos_academia">Encargado Acuerdos Academia</option>
                        </select>
                    </div>
                    <button type="submit" name="edit_user" class="btn btn-warning">Actualizar Usuario</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const editUserModal = document.getElementById('editUserModal');
editUserModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget; // Botón que abre el modal
    const id = button.getAttribute('data-id');
    const nombre = button.getAttribute('data-nombre');
    const correo = button.getAttribute('data-correo');
    const estado = button.getAttribute('data-estado');
    const role = button.getAttribute('data-role');
    const encargado = button.getAttribute('data-encargado');
    const permissions = button.getAttribute('data-permissions');

    // Actualizar el modal
    const editIdInput = document.getElementById('edit_id');
    const editNombreInput = document.getElementById('edit_nombre');
    const editCorreoInput = document.getElementById('edit_correo');
    const editEstadoInput = document.getElementById('edit_estado');
    const editRoleInput = document.getElementById('edit_role');
    const editEncargadoInput = document.getElementById('edit_encargado');

    editIdInput.value = id;
    editNombreInput.value = nombre;
    editCorreoInput.value = correo;
    editEstadoInput.value = estado;
    editRoleInput.value = role;
    editEncargadoInput.value = encargado;
});
</script>

<?php include 'footer.php'; ?>

 <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
   