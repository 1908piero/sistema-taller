<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Historial de Ventas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/ventas/crear" class="btn btn-sm btn-success">
            <i class="fa-solid fa-cart-plus"></i> Nueva Venta (POS)
        </a>
    </div>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'guardado'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fa-solid fa-check-circle"></i> Venta registrada correctamente. 
        <?php if(isset($_GET['id'])): ?>
            <a href="/ventas/imprimir?id=<?php echo $_GET['id']; ?>" target="_blank" class="fw-bold text-success ms-2">
                <i class="fa-solid fa-print"></i> Imprimir Ticket
            </a>
        <?php endif; ?>
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
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($ventas)): ?>
                        <?php foreach($ventas as $v): ?>
                            <tr>
                                <td><strong><?php echo str_pad($v->id, 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td data-sort="<?php echo strtotime($v->fecha); ?>">
                                    <?php echo date('d/m/Y H:i', strtotime($v->fecha)); ?>
                                </td>
                                <td><?php echo $v->cliente_nombre ?: 'Público General'; ?></td>
                                <td class="fw-bold text-success">
                                    <?php echo $sistema->simbolo_moneda . ' ' . number_format($v->total, 2); ?>
                                </td>
                                <td class="text-end">
                                    <a href="/ventas/imprimir?id=<?php echo $v->id; ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Ver Ticket PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>