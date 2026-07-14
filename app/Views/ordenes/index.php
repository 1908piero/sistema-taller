<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Órdenes de Servicio</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalOrden">
            <i class="fa-solid fa-plus"></i> Nueva Orden
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'guardado') echo "Orden generada exitosamente.";
            elseif($_GET['msg'] == 'estado_actualizado') echo "Estado actualizado.";
            elseif($_GET['msg'] == 'error') echo "<i class='fa-solid fa-triangle-exclamation'></i> Error al guardar. Verifica que todos los campos requeridos estén llenos.";
            else echo "Acción realizada.";
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
                        <th># Ticket</th>
                        <th>Cliente</th>
                        <th>Equipo / Falla</th>
                        <th>Fecha Ingreso</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($ordenes)): ?>
                        <?php foreach($ordenes as $orden): ?>
                            <?php 
                                $badgeClass = 'bg-secondary';
                                if($orden->estado == 'pendiente') $badgeClass = 'bg-warning text-dark';
                                if($orden->estado == 'diagnostico') $badgeClass = 'bg-info text-dark';
                                if($orden->estado == 'reparado') $badgeClass = 'bg-primary';
                                if($orden->estado == 'entregado') $badgeClass = 'bg-success';
                                if($orden->estado == 'cancelado') $badgeClass = 'bg-danger';
                            ?>
                            <tr>
                                <td><strong><?php echo 'ORD-' . str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td>
                                    <?php echo $orden->cliente_nombre; ?><br>
                                    <small class="text-muted"><i class="fa-solid fa-phone"></i> <?php echo $orden->cliente_telefono; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo $orden->equipo_tipo . ' ' . $orden->equipo_marca; ?></strong><br>
                                    <small class="text-muted"><?php echo substr($orden->falla_reportada, 0, 50) . '...'; ?></small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($orden->fecha_recepcion)); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($orden->estado); ?></span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Estado</button>
                                        <ul class="dropdown-menu">
                                            <li><form action="/ordenes/cambiar-estado" method="POST"><input type="hidden" name="id" value="<?php echo $orden->id; ?>"><input type="hidden" name="nuevo_estado" value="pendiente"><button class="dropdown-item">Pendiente</button></form></li>
                                            <li><form action="/ordenes/cambiar-estado" method="POST"><input type="hidden" name="id" value="<?php echo $orden->id; ?>"><input type="hidden" name="nuevo_estado" value="diagnostico"><button class="dropdown-item">En Diagnóstico</button></form></li>
                                            <li><form action="/ordenes/cambiar-estado" method="POST"><input type="hidden" name="id" value="<?php echo $orden->id; ?>"><input type="hidden" name="nuevo_estado" value="reparado"><button class="dropdown-item">Reparado</button></form></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><form action="/ordenes/cambiar-estado" method="POST"><input type="hidden" name="id" value="<?php echo $orden->id; ?>"><input type="hidden" name="nuevo_estado" value="entregado"><button class="dropdown-item text-success">Entregar</button></form></li>
                                        </ul>
                                    </div>
                                    <a href="/ordenes/detalle?id=<?php echo $orden->id; ?>" class="btn btn-sm btn-primary" title="Ver Detalle"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalOrden" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Orden de Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/ordenes/guardar" method="POST">
                <div class="modal-body">
                    <h6 class="border-bottom pb-2 mb-3 text-primary">1. Datos del Cliente</h6>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Cliente *</label>
                        <select class="form-select" name="cliente_id" required>
                            <option value="">-- Buscar Cliente --</option>
                            <?php foreach($clientes as $cliente): ?>
                                <?php if($cliente->estado == 1): ?>
                                    <option value="<?php echo $cliente->id; ?>"><?php echo $cliente->nombre . ' - ' . $cliente->telefono; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <h6 class="border-bottom pb-2 mb-3 text-primary mt-4">2. Datos del Equipo</h6>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label class="form-label">Tipo *</label>
                            <select class="form-select" name="equipo_tipo" required>
                                <option value="Laptop">Laptop</option>
                                <option value="PC Escritorio">PC Escritorio</option>
                                <option value="Impresora">Impresora</option>
                                <option value="Celular">Celular</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca *</label>
                            <input type="text" class="form-control" name="equipo_marca" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="equipo_modelo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Serie / IMEI</label>
                        <input type="text" class="form-control" name="equipo_serie">
                    </div>
                    <h6 class="border-bottom pb-2 mb-3 text-primary mt-4">3. Detalle del Servicio</h6>
                    <div class="mb-3">
                        <label class="form-label">Falla Reportada *</label>
                        <textarea class="form-control" name="falla_reportada" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha Promesa</label>
                        <input type="date" class="form-control" name="fecha_promesa">
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

<?php require_once __DIR__ . '/../partials/footer.php'; ?>