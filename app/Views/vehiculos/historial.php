<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i> Historial de Vehículos</h4>
    <a href="/vehiculos" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm mb-4 bg-light border-0">
    <div class="card-body py-3">
        <form action="/vehiculos/historial" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-bold small">Buscar por placa o ID:</label>
                <input type="text" class="form-control" name="search" placeholder="Ingrese placa o ID del vehículo..." value="<?php echo htmlspecialchars($search); ?>">
                <div class="form-text text-muted"><small>Puede buscar por placa (ej: ABC-123) o por ID numérico (ej: 5).</small></div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-18:</strong> Error al consultar la información.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($search): ?>
    <?php if (empty($resultados)): ?>
        <div class="alert alert-warning text-center">
            <i class="fa-solid fa-exclamation-triangle"></i> <strong>MSJ-17:</strong> El vehículo no existe en el sistema.
        </div>
    <?php else: ?>
        <div class="alert alert-success alert-dismissible fade show"><strong>MSJ-16:</strong> Consulta realizada exitosamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php foreach ($resultados as $vehiculo): ?>
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-car me-2"></i> <?php echo htmlspecialchars($vehiculo->placa); ?> - <?php echo htmlspecialchars($vehiculo->marca . ' ' . $vehiculo->modelo); ?></span>
                    <span class="badge bg-secondary"><?php echo count($vehiculo->ordenes); ?> servicio(s)</span>
                </div>
                <div class="card-body">
                    <?php if (count($vehiculo->ordenes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:30px"></th>
                                        <th># Orden</th>
                                        <th>Cliente</th>
                                        <th>Falla</th>
                                        <th>Diagnóstico</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculo->ordenes as $o): ?>
                                        <?php $uniqid = uniqid('det_'); ?>
                                        <tr>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $uniqid; ?>" aria-expanded="false">
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </button>
                                            </td>
                                            <td><strong>ORD-<?php echo str_pad($o->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo htmlspecialchars($o->cliente_nombre); ?></td>
                                            <td><small><?php echo substr($o->falla_reportada, 0, 40); ?></small></td>
                                            <td><small><?php echo substr($o->diagnostico ?? $o->observaciones_tecnicas ?? '—', 0, 40); ?></small></td>
                                            <td><?php echo date('d/m/Y', strtotime($o->fecha_recepcion)); ?></td>
                                            <td>
                                                <?php $badges = ['Abierta'=>'warning','En proceso'=>'info','Cerrada'=>'primary','Entregada'=>'success','Cancelada'=>'danger']; ?>
                                                <span class="badge bg-<?php echo $badges[$o->estado] ?? 'secondary'; ?>"><?php echo ucfirst($o->estado); ?></span>
                                            </td>
                                            <td>S/ <?php echo number_format($o->total, 2); ?></td>
                                            <td><a href="/ordenes/detalle?id=<?php echo $o->id; ?>" class="btn btn-sm btn-info"><i class="fa-solid fa-eye"></i></a></td>
                                        </tr>
                                        <tr class="collapse" id="<?php echo $uniqid; ?>">
                                            <td colspan="9" class="bg-light p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Servicios:</strong>
                                                        <?php if (!empty($o->servicios)): ?>
                                                            <ul class="list-unstyled small mb-0 mt-1">
                                                                <?php foreach ($o->servicios as $s): ?>
                                                                    <li><i class="fa-solid fa-wrench text-info me-1"></i> <?php echo htmlspecialchars($s->servicio_nombre ?? ('Servicio #' . $s->servicio_id)); ?> x<?php echo $s->cantidad; ?> — S/ <?php echo number_format($s->subtotal, 2); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="text-muted small mb-0 mt-1">—</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Repuestos:</strong>
                                                        <?php if (!empty($o->repuestos)): ?>
                                                            <ul class="list-unstyled small mb-0 mt-1">
                                                                <?php foreach ($o->repuestos as $r): ?>
                                                                    <li><i class="fa-solid fa-boxes text-success me-1"></i> <?php echo htmlspecialchars($r->producto_nombre ?? ('Producto #' . $r->producto_id)); ?> x<?php echo $r->cantidad; ?> — S/ <?php echo number_format($r->subtotal, 2); ?></li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <p class="text-muted small mb-0 mt-1">—</p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-2 mb-0">Sin servicios registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Ingrese una placa o ID para consultar el historial completo del vehículo</h5>
            <p class="text-muted">Podrá ver todas las órdenes de servicio, diagnósticos, servicios y repuestos asociados.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
