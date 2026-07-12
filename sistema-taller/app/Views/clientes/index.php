<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Clientes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalCrear()">
            <i class="fa-solid fa-plus"></i> Nuevo Cliente
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'guardado') echo "Cliente registrado exitosamente.";
            elseif($_GET['msg'] == 'actualizado') echo "Datos del cliente actualizados.";
            elseif($_GET['msg'] == 'estado_cambiado') echo "El estado del cliente ha cambiado.";
            else echo "Operación realizada correctamente.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($clientes)): ?>
                        <?php foreach($clientes as $cliente): ?>
                            <tr class="<?php echo ($cliente->estado == 0) ? 'table-secondary text-muted' : ''; ?>">
                                <td>
                                    <strong><?php echo $cliente->nombre; ?></strong><br>
                                    <small class="text-muted"><?php echo $cliente->direccion; ?></small>
                                </td>
                                <td><?php echo $cliente->telefono; ?></td>
                                <td><?php echo $cliente->email; ?></td>
                                <td>
                                    <?php if($cliente->estado == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/clientes/perfil?id=<?php echo $cliente->id; ?>" class="btn btn-sm btn-outline-info" title="Ver Historial 360">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <button class="btn btn-sm btn-outline-primary" onclick='editarCliente(<?php echo json_encode($cliente); ?>)' title="Editar Rápido">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <?php if($cliente->estado == 1): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmarCambioEstado(<?php echo $cliente->id; ?>, 0, '<?php echo $cliente->nombre; ?>')" title="Desactivar">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="confirmarCambioEstado(<?php echo $cliente->id; ?>, 1, '<?php echo $cliente->nombre; ?>')" title="Activar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCliente" action="/clientes/guardar" method="POST">
                <input type="hidden" name="id" id="clienteId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" id="direccion">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="formEstado" action="/clientes/cambiar-estado" method="POST">
    <input type="hidden" name="id" id="idEstado">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    const modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));

    function abrirModalCrear() {
        document.getElementById('formCliente').reset();
        document.getElementById('clienteId').value = '';
        document.getElementById('tituloModal').innerText = 'Nuevo Cliente';
        document.getElementById('formCliente').action = '/clientes/guardar';
        modalCliente.show();
    }

    function editarCliente(cliente) {
        document.getElementById('clienteId').value = cliente.id;
        document.getElementById('nombre').value = cliente.nombre;
        document.getElementById('telefono').value = cliente.telefono;
        document.getElementById('email').value = cliente.email;
        document.getElementById('direccion').value = cliente.direccion;
        document.getElementById('tituloModal').innerText = 'Editar Cliente';
        document.getElementById('formCliente').action = '/clientes/actualizar';
        modalCliente.show();
    }

    function confirmarCambioEstado(id, nuevoEstado, nombre) {
        const accion = nuevoEstado === 1 ? 'ACTIVAR' : 'DESACTIVAR';
        if(confirm(`¿Deseas ${accion} al cliente "${nombre}"?`)) {
            document.getElementById('idEstado').value = id;
            document.getElementById('nuevoEstado').value = nuevoEstado;
            document.getElementById('formEstado').submit();
        }
    }
</script>