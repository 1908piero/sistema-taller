<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Personal</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalCrear()">
            <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <?php if($_GET['msg'] == 'guardado' || $_GET['msg'] == 'actualizado' || $_GET['msg'] == 'estado_cambiado'): ?>
        <div class="alert alert-success alert-dismissible fade show"><strong>MSJ-22:</strong> Operación realizada correctamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'email_duplicado'): ?>
        <div class="alert alert-warning alert-dismissible fade show"><strong>MSJ-23:</strong> El usuario ya existe en el sistema.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'rol_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-24:</strong> Rol no válido o sin permisos asignados.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'nombre_requerido' || $_GET['msg'] == 'nombre_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show">El nombre solo debe contener letras (máximo 100 caracteres).<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'error_propio'): ?>
        <div class="alert alert-warning alert-dismissible fade show">No puedes desactivar tu propia cuenta.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-03:</strong> Error interno. Intente nuevamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email / Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($usuarios)): ?>
                        <?php foreach($usuarios as $user): ?>
                            <tr class="<?php echo ($user->estado == 0) ? 'table-secondary opacity-75' : ''; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user->nombre); ?>&background=random&size=32" class="rounded-circle me-2">
                                        <strong><?php echo $user->nombre; ?></strong>
                                    </div>
                                </td>
                                <td><?php echo $user->email; ?></td>
                                <td>
                                    <?php if($user->rol == 'Admin'): ?>
                                        <span class="badge bg-danger">ADMIN</span>
                                    <?php elseif($user->rol == 'Jefe'): ?>
                                        <span class="badge bg-dark">JEFE</span>
                                    <?php elseif($user->rol == 'Recepcionista'): ?>
                                        <span class="badge bg-success">RECEPCIONISTA</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">TÉCNICO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user->estado == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Bloqueado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick='editarUsuario(<?php echo json_encode($user); ?>)'>
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <?php if($user->id != $_SESSION['user_id']): ?>
                                        <?php if($user->estado == 1): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="cambiarEstado(<?php echo $user->id; ?>, 0)">
                                                <i class="fa-solid fa-lock"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="cambiarEstado(<?php echo $user->id; ?>, 1)">
                                                <i class="fa-solid fa-lock-open"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUsuario" action="/usuarios/guardar" method="POST">
                <input type="hidden" name="id" id="userId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" id="password">
                        <div class="form-text" id="passHelp"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" name="rol" id="rol" required>
                            <option value="Recepcionista">Recepcionista</option>
                            <option value="Técnico">Técnico</option>
                            <option value="Admin">Administrador</option>
                            <option value="Jefe">Jefe de Taller</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="formEstado" action="/usuarios/cambiar-estado" method="POST">
    <input type="hidden" name="id" id="idEstado">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));

    function abrirModalCrear() {
        document.getElementById('formUsuario').reset();
        document.getElementById('userId').value = '';
        document.getElementById('tituloModal').innerText = 'Nuevo Usuario';
        document.getElementById('formUsuario').action = '/usuarios/guardar';
        document.getElementById('passHelp').innerText = 'Obligatoria para usuarios nuevos.';
        document.getElementById('password').required = true;
        modal.show();
    }

    function editarUsuario(user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('nombre').value = user.nombre;
        document.getElementById('email').value = user.email;
        document.getElementById('rol').value = user.rol;
        document.getElementById('password').value = ''; 
        document.getElementById('password').required = false;
        
        document.getElementById('tituloModal').innerText = 'Editar Usuario';
        document.getElementById('formUsuario').action = '/usuarios/actualizar';
        document.getElementById('passHelp').innerText = 'Dejar en blanco para mantener contraseña.';
        modal.show();
    }

    function cambiarEstado(id, estado) {
        if(confirm('¿Seguro de cambiar acceso?')) {
            document.getElementById('idEstado').value = id;
            document.getElementById('nuevoEstado').value = estado;
            document.getElementById('formEstado').submit();
        }
    }
</script>