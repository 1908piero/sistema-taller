<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Control de Gastos y Egresos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalGasto">
            <i class="fa-solid fa-minus-circle"></i> Registrar Gasto
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'guardado') echo "Gasto registrado correctamente.";
            elseif($_GET['msg'] == 'eliminado') echo "Registro eliminado.";
            else echo "Operación realizada.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card border-start border-4 border-danger shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted text-uppercase mb-1">Total Gastos (Histórico)</h6>
                    <?php 
                        $total = 0;
                        foreach($gastos as $g) $total += $g->monto;
                    ?>
                    <h3 class="fw-bold text-danger"><?php echo $sistema->simbolo_moneda . ' ' . number_format($total, 2); ?></h3>
                </div>
                <i class="fa-solid fa-money-bill-transfer fa-3x text-gray-300 opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Registrado Por</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($gastos as $g): ?>
                        <tr>
                            <td data-sort="<?php echo strtotime($g->fecha); ?>">
                                <?php echo date('d/m/Y H:i', strtotime($g->fecha)); ?>
                            </td>
                            <td><?php echo $g->descripcion; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $g->categoria; ?></span></td>
                            <td class="small text-muted"><?php echo $g->usuario_nombre; ?></td>
                            <td class="text-end fw-bold text-danger">
                                - <?php echo $sistema->simbolo_moneda . ' ' . number_format($g->monto, 2); ?>
                            </td>
                            <td class="text-end">
                                <form action="/gastos/eliminar" method="POST" onsubmit="return confirm('¿Eliminar este registro de gasto?');">
                                    <input type="hidden" name="id" value="<?php echo $g->id; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGasto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Registrar Nuevo Egreso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/gastos/guardar" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Descripción del Gasto *</label>
                        <input type="text" class="form-control" name="descripcion" placeholder="Ej: Pago de luz, Compra repuestos..." required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monto *</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo $sistema->simbolo_moneda; ?></span>
                                <input type="number" step="0.01" class="form-control" name="monto" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="categoria">
                                <option value="General">General</option>
                                <option value="Servicios">Servicios (Luz/Agua)</option>
                                <option value="Alquiler">Alquiler</option>
                                <option value="Repuestos">Compra Repuestos</option>
                                <option value="Personal">Personal</option>
                                <option value="Publicidad">Publicidad</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar Salida</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>