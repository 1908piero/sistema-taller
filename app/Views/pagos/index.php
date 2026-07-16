<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 'ok'): ?>
        <div class="alert alert-success alert-dismissible fade show"><strong>MSJ-13:</strong> Pago registrado correctamente.<a href="/pagos/comprobante?id=<?php echo $_GET['pago_id'] ?? 0; ?>" class="btn btn-sm btn-outline-primary ms-3" target="_blank"><i class="fa-solid fa-print"></i> Imprimir Comprobante</a><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'orden_invalida'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-14:</strong> La orden de trabajo no existe.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'monto_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>RF-08:</strong> El monto debe ser mayor a 0.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'error_comprobante'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-15:</strong> Error al generar el comprobante de pago.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'pago_duplicado'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-33:</strong> Esta orden ya tiene un pago registrado. No se permiten pagos duplicados.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>Error:</strong> No se pudo registrar el pago.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-cash-register me-2"></i> Caja y Pagos</h4>
    <div>
        <a href="/pagos/caja" class="btn btn-success me-2"><i class="fa-solid fa-hand-holding-dollar"></i> Registrar Pago</a>
    </div>
</div>

<!-- Resumen del día -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Total Cobrado</h5>
                <h3>S/ <?php echo number_format($resumen->total_monto, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Transacciones</h5>
                <h3><?php echo $resumen->total_pagos; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Cobros por Método</h5>
                <div class="row">
                    <?php foreach ($porMetodo as $pm): ?>
                    <div class="col-4">
                        <strong><?php echo ucfirst($pm->metodo_pago); ?>:</strong>
                        S/ <?php echo number_format($pm->total, 2); ?>
                        <small class="text-muted">(<?php echo $pm->cantidad; ?>)</small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($porMetodo) == 0): ?>
                        <p class="text-muted mb-0">Sin movimientos hoy</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtro por fecha -->
<form method="GET" class="row mb-3">
    <div class="col-auto">
        <input type="date" name="fecha" class="form-control" value="<?php echo $fecha; ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
    </div>
</form>

<!-- Tabla de pagos -->
<div class="card">
    <div class="card-header"><i class="fa-solid fa-list me-2"></i> Historial de Pagos</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaPagos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Orden</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pagos) > 0): ?>
                        <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?php echo $p->id; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($p->fecha)); ?></td>
                            <td><?php echo htmlspecialchars($p->cliente_nombre); ?></td>
                            <td>
                                <?php if ($p->orden_id): ?>
                                    <a href="/ordenes/detalle?id=<?php echo $p->orden_id; ?>">#<?php echo $p->orden_id; ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><strong>S/ <?php echo number_format($p->monto, 2); ?></strong></td>
                            <td>
                                <?php
                                $iconos = ['efectivo'=>'money-bill','tarjeta'=>'credit-card','transferencia'=>'building-columns','yape'=>'mobile-screen-button','plin'=>'mobile-screen'];
                                $i = $iconos[$p->metodo_pago] ?? 'money-bill';
                                echo "<i class='fa-solid fa-$i me-1'></i>" . ucfirst($p->metodo_pago);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($p->referencia ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($p->usuario_nombre); ?></td>
                            <td>
                                <a href="/pagos/comprobante?id=<?php echo $p->id; ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Comprobante PDF"><i class="fa-solid fa-print"></i></a>
                                <form method="POST" action="/pagos/eliminar" onsubmit="return confirm('¿Eliminar este pago?')" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo $p->id; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay pagos registrados para esta fecha</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (count($pagos) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new DataTable('#tablaPagos', {
        pageLength: 25,
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
