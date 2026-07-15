<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i> Historial de Vehículos</h4>
    <a href="/vehiculos" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm mb-4 bg-light border-0">
    <div class="card-body py-3">
        <form action="/vehiculos/historial" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-bold small">Buscar por placa:</label>
                <input type="text" class="form-control" name="search" placeholder="Ingrese placa del vehículo..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($search): ?>
    <?php if (empty($resultados)): ?>
        <div class="alert alert-warning text-center">
            <i class="fa-solid fa-exclamation-triangle"></i> No se encontraron vehículos con la placa "<?php echo htmlspecialchars($search); ?>".
        </div>
    <?php else: ?>
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
                                        <th># Orden</th>
                                        <th>Cliente</th>
                                        <th>Falla</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Total</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculo->ordenes as $o): ?>
                                        <tr>
                                            <td><strong>ORD-<?php echo str_pad($o->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo htmlspecialchars($o->cliente_nombre); ?></td>
                                            <td><small><?php echo substr($o->falla_reportada, 0, 40); ?></small></td>
                                            <td><?php echo date('d/m/Y', strtotime($o->fecha_recepcion)); ?></td>
                                            <td>
                                                <?php $badges = ['pendiente'=>'warning','diagnostico'=>'info','reparado'=>'primary','entregado'=>'success','cancelado'=>'danger']; ?>
                                                <span class="badge bg-<?php echo $badges[$o->estado] ?? 'secondary'; ?>"><?php echo ucfirst($o->estado); ?></span>
                                            </td>
                                            <td>S/ <?php echo number_format($o->total, 2); ?></td>
                                            <td><a href="/ordenes/detalle?id=<?php echo $o->id; ?>" class="btn btn-sm btn-info"><i class="fa-solid fa-eye"></i></a></td>
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
            <h5 class="text-muted">Ingrese una placa para consultar el historial completo del vehículo</h5>
            <p class="text-muted">Podrá ver todas las órdenes de servicio asociadas a ese vehículo.</p>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
