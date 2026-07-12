<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-car me-2"></i> Vehículo: <strong><?php echo htmlspecialchars($vehiculo->placa); ?></strong></h4>
    <a href="/vehiculos" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Volver</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-info-circle me-2"></i> Datos del Vehículo</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Placa</th><td><strong><?php echo htmlspecialchars($vehiculo->placa); ?></strong></td></tr>
                    <tr><th>Marca</th><td><?php echo htmlspecialchars($vehiculo->marca); ?></td></tr>
                    <tr><th>Modelo</th><td><?php echo htmlspecialchars($vehiculo->modelo); ?></td></tr>
                    <tr><th>Año</th><td><?php echo $vehiculo->año ?: '-'; ?></td></tr>
                    <tr><th>Color</th><td><?php echo htmlspecialchars($vehiculo->color ?: '-'); ?></td></tr>
                    <tr><th>VIN</th><td><code><?php echo htmlspecialchars($vehiculo->vin ?: '-'); ?></code></td></tr>
                    <tr><th>Motor</th><td><?php echo htmlspecialchars($vehiculo->tipo_motor ?: '-'); ?></td></tr>
                    <tr><th>Propietario</th><td><a href="/clientes/perfil?id=<?php echo $vehiculo->cliente_id; ?>"><?php echo htmlspecialchars($vehiculo->cliente_nombre); ?></a></td></tr>
                    <tr><th>Registro</th><td><?php echo date('d/m/Y', strtotime($vehiculo->created_at ?? 'now')); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-clipboard-list me-2"></i> Historial de Servicios</div>
            <div class="card-body">
                <?php if (count($ordenes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th># Orden</th><th>Fecha</th><th>Estado</th><th>Total</th><th>Acción</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $o): ?>
                            <tr>
                                <td><?php echo $o->id; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($o->fecha_recepcion)); ?></td>
                                <td>
                                    <?php
                                    $badges = ['pendiente'=>'warning','diagnostico'=>'info','reparado'=>'primary','entregado'=>'success','cancelado'=>'danger'];
                                    $b = $badges[$o->estado] ?? 'secondary';
                                    echo "<span class='badge bg-$b'>".ucfirst($o->estado)."</span>";
                                    ?>
                                </td>
                                <td>S/ <?php echo number_format($o->total, 2); ?></td>
                                <td><a href="/ordenes/detalle?id=<?php echo $o->id; ?>" class="btn btn-sm btn-info"><i class="fa-solid fa-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">Este vehículo no tiene servicios registrados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
