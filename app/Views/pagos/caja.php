<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 'ok'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <strong>Pago registrado correctamente.</strong>
            <a href="/pagos/comprobante?id=<?php echo $_GET['pago_id'] ?? 0; ?>" class="btn btn-sm btn-outline-primary ms-3" target="_blank"><i class="fa-solid fa-print"></i> Imprimir Comprobante</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['msg'] == 'orden_invalida'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-14:</strong> La orden de trabajo no existe.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'monto_invalido'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>RF-08:</strong> El monto debe ser mayor a 0. Ingrese un monto válido.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'error_comprobante'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-15:</strong> Error al generar el comprobante de pago.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'pago_duplicado'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-33:</strong> Esta orden ya tiene un pago registrado. No se permiten pagos duplicados.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif ($_GET['msg'] == 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>Error:</strong> No se pudo registrar el pago. Verifique los datos.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fa-solid fa-hand-holding-dollar me-2"></i> Registrar Pago</h4>
    <a href="/pagos" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Volver a Caja</a>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-chart-simple me-2"></i> Resumen del Día</div>
            <div class="card-body">
                <h5 class="text-primary">S/ <?php echo number_format($resumen->total_monto, 2); ?></h5>
                <p class="text-muted"><?php echo $resumen->total_pagos; ?> transacciones</p>
                <hr>
                <p class="small text-muted mb-0"><?php echo date('d/m/Y', strtotime($fecha)); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i> Órdenes Listas para Cobrar</div>
            <div class="card-body">
                <?php if (count($ordenes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Cliente</th>
                                <th>Equipo</th>
                                <th>Total</th>
                                <th>Cobrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $o): ?>
                            <tr>
                                <td><?php echo $o->id; ?></td>
                                <td><a href="/clientes/perfil?id=<?php echo $o->cliente_id; ?>"><?php echo htmlspecialchars($o->cliente_nombre ?? 'Cliente #'.$o->cliente_id); ?></a></td>
                                <td><?php echo htmlspecialchars($o->equipo_marca . ' ' . $o->equipo_modelo); ?></td>
                                <td><strong>S/ <?php echo number_format($o->total, 2); ?></strong></td>
                                <td>
                                    <button class="btn btn-success btn-sm cobrar-orden" 
                                        data-orden="<?php echo $o->id; ?>"
                                        data-cliente="<?php echo $o->cliente_id; ?>"
                                        data-cliente-nombre="<?php echo htmlspecialchars($o->cliente_nombre ?? ''); ?>"
                                        data-total="<?php echo $o->total; ?>">
                                        <i class="fa-solid fa-hand-holding-dollar"></i> Cobrar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No hay órdenes reparadas pendientes de cobro.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cobrar -->
<div class="modal fade" id="modalCobrar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/pagos/guardar">
                <input type="hidden" name="orden_id" id="p_orden_id">
                <input type="hidden" name="cliente_id" id="p_cliente_id">
                <input type="hidden" name="ref" value="/pagos/caja">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Cliente:</strong> <span id="p_cliente_nombre"></span></p>
                    <p><strong>Orden #</strong> <span id="p_orden_numero"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Monto a Cobrar</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" step="0.01" name="monto" id="p_monto" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="metodo_pago" class="form-select" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta de Débito/Crédito</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referencia (opcional)</label>
                        <input type="text" name="referencia" class="form-control" placeholder="Número de voucher, operación...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.cobrar-orden').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('p_orden_id').value = this.dataset.orden;
        document.getElementById('p_cliente_id').value = this.dataset.cliente;
        document.getElementById('p_cliente_nombre').textContent = this.dataset.clienteNombre;
        document.getElementById('p_orden_numero').textContent = this.dataset.orden;
        document.getElementById('p_monto').value = this.dataset.total;
        new bootstrap.Modal(document.getElementById('modalCobrar')).show();
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
