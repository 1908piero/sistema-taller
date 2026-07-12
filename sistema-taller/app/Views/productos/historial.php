<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Kardex: <?php echo $producto->nombre; ?></h1>
        <p class="text-muted">Código: <?php echo $producto->codigo; ?> | Stock Actual: <strong><?php echo $producto->stock; ?></strong></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/productos" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inventario
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="datatable">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo Movimiento</th>
                        <th>Cantidad</th>
                        <th>Stock Resultante</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($movimientos)): ?>
                        <?php foreach($movimientos as $mov): ?>
                            <tr>
                                <td data-sort="<?php echo strtotime($mov->fecha); ?>">
                                    <?php echo date('d/m/Y H:i', strtotime($mov->fecha)); ?>
                                </td>
                                <td>
                                    <?php if($mov->tipo == 'entrada'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                            <i class="fa-solid fa-arrow-up"></i> ENTRADA
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                            <i class="fa-solid fa-arrow-down"></i> SALIDA
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?php echo $mov->cantidad; ?></td>
                                <td><?php echo $mov->stock_actual; ?></td>
                                <td><?php echo $mov->motivo; ?></td>
                                <td class="small text-muted"><?php echo $mov->usuario_nombre; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>