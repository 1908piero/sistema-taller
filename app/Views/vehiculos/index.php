<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 'guardado'): ?>
        <div class="alert alert-success">Vehículo registrado correctamente.</div>
    <?php elseif ($_GET['msg'] == 'actualizado'): ?>
        <div class="alert alert-success">Vehículo actualizado.</div>
    <?php elseif ($_GET['msg'] == 'placa_duplicada'): ?>
        <div class="alert alert-danger"><strong>RN-02:</strong> Ya existe un vehículo con esa placa. La placa debe ser única.</div>
    <?php elseif ($_GET['msg'] == 'cliente_invalido'): ?>
        <div class="alert alert-danger"><strong>MSJ-05:</strong> Debe seleccionar un cliente registrado.</div>
    <?php elseif ($_GET['msg'] == 'error'): ?>
        <div class="alert alert-danger"><strong>MSJ-06:</strong> Error al guardar datos del vehículo.</div>
    <?php elseif ($_GET['msg'] == 'placa_invalida'): ?>
        <div class="alert alert-danger">La placa debe tener exactamente 7 caracteres alfanuméricos (ej: ABC-123).</div>
    <?php elseif ($_GET['msg'] == 'marca_invalida'): ?>
        <div class="alert alert-danger">La marca solo debe contener letras (máximo 50 caracteres).</div>
    <?php elseif ($_GET['msg'] == 'modelo_invalido'): ?>
        <div class="alert alert-danger">El modelo solo debe contener caracteres alfanuméricos (máximo 50).</div>
    <?php elseif ($_GET['msg'] == 'anio_invalido'): ?>
        <div class="alert alert-danger">El año debe estar entre 1900 y 2030.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-car me-2"></i> Gestión de Vehículos</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVehiculo">
        <i class="fa-solid fa-plus"></i> Nuevo Vehículo
    </button>
</div>

<!-- Buscador -->
<form method="GET" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="search" class="form-control" placeholder="Buscar por placa..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i></button>
        <?php if ($search): ?>
            <a href="/vehiculos" class="btn btn-outline-danger"><i class="fa-solid fa-times"></i></a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Placa</th>
                        <th>Marca / Modelo</th>
                        <th>Año</th>
                        <th>Color</th>
                        <th>Propietario</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vehiculos) > 0): ?>
                        <?php foreach ($vehiculos as $v): ?>
                        <tr>
                            <td><?php echo $v->id; ?></td>
                            <td><strong><?php echo htmlspecialchars($v->placa); ?></strong></td>
                            <td><?php echo htmlspecialchars($v->marca . ' ' . $v->modelo); ?></td>
                            <td><?php echo $v->año; ?></td>
                            <td><?php echo htmlspecialchars($v->color ?? '-'); ?></td>
                            <td><a href="/clientes/perfil?id=<?php echo $v->cliente_id; ?>"><?php echo htmlspecialchars($v->cliente_nombre ?? 'Sin cliente'); ?></a></td>
                            <td>
                                <?php if ($v->estado): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/vehiculos/perfil?id=<?php echo $v->id; ?>" class="btn btn-sm btn-info" title="Ver historial"><i class="fa-solid fa-history"></i></a>
                                <button class="btn btn-sm btn-warning editar-vehiculo" data-id="<?php echo $v->id; ?>" data-cliente="<?php echo $v->cliente_id; ?>" data-placa="<?php echo htmlspecialchars($v->placa); ?>" data-marca="<?php echo htmlspecialchars($v->marca); ?>" data-modelo="<?php echo htmlspecialchars($v->modelo); ?>" data-año="<?php echo $v->año; ?>" data-color="<?php echo htmlspecialchars($v->color); ?>" data-vin="<?php echo htmlspecialchars($v->vin); ?>" data-motor="<?php echo htmlspecialchars($v->tipo_motor); ?>" data-obs="<?php echo htmlspecialchars($v->observaciones); ?>" title="Editar"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm <?php echo $v->estado ? 'btn-danger' : 'btn-success'; ?> cambiar-estado-vehiculo" data-id="<?php echo $v->id; ?>" data-estado="<?php echo $v->estado ? '0' : '1'; ?>">
                                    <i class="fa-solid <?php echo $v->estado ? 'fa-ban' : 'fa-check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No hay vehículos registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Nuevo/Editar Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formVehiculo" method="POST" action="/vehiculos/guardar">
                <input type="hidden" name="id" id="vehiculo_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Propietario *</label>
                            <select name="cliente_id" id="v_cliente_id" class="form-select" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Placa *</label>
                            <input type="text" name="placa" id="v_placa" class="form-control text-uppercase" placeholder="ABC-123" required maxlength="7">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Año</label>
                            <input type="number" name="año" id="v_año" class="form-control" min="1900" max="2030">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Marca *</label>
                            <input type="text" name="marca" id="v_marca" class="form-control" placeholder="Toyota" required maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Modelo *</label>
                            <input type="text" name="modelo" id="v_modelo" class="form-control" placeholder="Corolla" required maxlength="50">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" id="v_color" class="form-control" placeholder="Rojo">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">VIN / Número de serie</label>
                            <input type="text" name="vin" id="v_vin" class="form-control" placeholder="1HGCM82633A004352">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de Motor</label>
                            <select name="tipo_motor" id="v_tipo_motor" class="form-select">
                                <option value="">Seleccionar...</option>
                                <option value="Gasolina">Gasolina</option>
                                <option value="Diésel">Diésel</option>
                                <option value="Gas">Gas (GNV/GLP)</option>
                                <option value="Híbrido">Híbrido</option>
                                <option value="Eléctrico">Eléctrico</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="v_obs" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form oculto para cambiar estado -->
<form id="formCambiarEstado" method="POST" action="/vehiculos/cambiar-estado">
    <input type="hidden" name="id" id="ce_id">
    <input type="hidden" name="nuevo_estado" id="ce_estado">
</form>

<script>
document.querySelectorAll('.editar-vehiculo').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Editar Vehículo';
        document.getElementById('vehiculo_id').value = this.dataset.id;
        document.getElementById('v_cliente_id').value = this.dataset.cliente;
        document.getElementById('v_placa').value = this.dataset.placa;
        document.getElementById('v_marca').value = this.dataset.marca;
        document.getElementById('v_modelo').value = this.dataset.modelo;
        document.getElementById('v_año').value = this.dataset.año;
        document.getElementById('v_color').value = this.dataset.color;
        document.getElementById('v_vin').value = this.dataset.vin;
        document.getElementById('v_tipo_motor').value = this.dataset.motor;
        document.getElementById('v_obs').value = this.dataset.obs;
        document.getElementById('formVehiculo').action = '/vehiculos/actualizar';
        new bootstrap.Modal(document.getElementById('modalVehiculo')).show();
    });
});

document.querySelectorAll('.cambiar-estado-vehiculo').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('¿Está seguro de cambiar el estado de este vehículo?')) {
            document.getElementById('ce_id').value = this.dataset.id;
            document.getElementById('ce_estado').value = this.dataset.estado;
            document.getElementById('formCambiarEstado').submit();
        }
    });
});

// Reset modal on new
document.getElementById('modalVehiculo').addEventListener('hidden.bs.modal', function() {
    document.getElementById('modalTitle').textContent = 'Nuevo Vehículo';
    document.getElementById('formVehiculo').reset();
    document.getElementById('vehiculo_id').value = '';
    document.getElementById('formVehiculo').action = '/vehiculos/guardar';
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
