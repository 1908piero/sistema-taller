<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Catálogo de Servicios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio">
            <i class="fa-solid fa-plus"></i> Nuevo Servicio
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'guardado') echo "<strong>MSJ-29:</strong> Servicio agregado al catálogo correctamente.";
            elseif($_GET['msg'] == 'actualizado') echo "Servicio actualizado.";
            elseif($_GET['msg'] == 'estado_cambiado') echo "Estado actualizado.";
            else echo "Operación realizada.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Duración</th>
                        <th class="text-end">Precio</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($servicios)): ?>
                        <?php foreach($servicios as $s): ?>
                            <tr class="<?php echo ($s->estado == 0) ? 'table-secondary opacity-75' : ''; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($s->nombre); ?></strong>
                                    <?php if($s->descripcion): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($s->descripcion); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info text-dark"><?php echo ucfirst($s->categoria); ?></span></td>
                                <td><?php echo $s->duracion_estimada ?: '-'; ?></td>
                                <td class="text-end fw-bold"><?php echo $sistema->simbolo_moneda . ' ' . number_format($s->precio, 2); ?></td>
                                <td>
                                    <?php if($s->estado == 1): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick='editarServicio(<?php echo json_encode($s); ?>)' title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if($s->estado == 1): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="cambiarEstado(<?php echo $s->id; ?>, 0)" title="Desactivar">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="cambiarEstado(<?php echo $s->id; ?>, 1)" title="Activar">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-3">No hay servicios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formServicio" action="/servicios/guardar" method="POST">
                <input type="hidden" name="id" id="servId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="descripcion" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Precio *</label>
                            <input type="number" step="0.01" class="form-control" name="precio" id="precio" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="categoria" id="categoria">
                                <option value="diagnostico">Diagnóstico</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="reparacion">Reparación</option>
                                <option value="software">Software</option>
                                <option value="general" selected>General</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duración</label>
                            <input type="text" class="form-control" name="duracion_estimada" id="duracion" placeholder="Ej: 2 horas">
                        </div>
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

<form id="formEstado" action="/servicios/cambiar-estado" method="POST">
    <input type="hidden" name="id" id="idEstado">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    const modalServicio = new bootstrap.Modal(document.getElementById('modalServicio'));

    function abrirModalCrear() {
        document.getElementById('formServicio').reset();
        document.getElementById('servId').value = '';
        document.getElementById('tituloModal').innerText = 'Nuevo Servicio';
        document.getElementById('formServicio').action = '/servicios/guardar';
        modalServicio.show();
    }

    function editarServicio(s) {
        document.getElementById('servId').value = s.id;
        document.getElementById('nombre').value = s.nombre;
        document.getElementById('descripcion').value = s.descripcion || '';
        document.getElementById('precio').value = s.precio;
        document.getElementById('categoria').value = s.categoria;
        document.getElementById('duracion').value = s.duracion_estimada || '';
        document.getElementById('tituloModal').innerText = 'Editar Servicio';
        document.getElementById('formServicio').action = '/servicios/actualizar';
        modalServicio.show();
    }

    function cambiarEstado(id, estado) {
        if(confirm('¿Confirmar cambio de estado?')) {
            document.getElementById('idEstado').value = id;
            document.getElementById('nuevoEstado').value = estado;
            document.getElementById('formEstado').submit();
        }
    }
</script>
