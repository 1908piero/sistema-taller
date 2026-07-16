<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php elseif (isset($hayDatos) && $hayDatos): ?>
    <div class="alert alert-success alert-dismissible fade show"><strong>MSJ-19:</strong> Reporte generado correctamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes Gerenciales</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/reportes/exportar-excel?desde=<?php echo $fechaInicio; ?>&hasta=<?php echo $fechaFin; ?>" class="btn btn-sm btn-success me-2" target="_blank">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a href="/reportes/exportar-pdf?desde=<?php echo $fechaInicio; ?>&hasta=<?php echo $fechaFin; ?>" class="btn btn-sm btn-danger me-2" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
    </div>
</div>

<div class="card shadow-sm mb-4 bg-light border-0">
    <div class="card-body py-3">
        <form action="/reportes" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-bold small">Desde:</label>
                <input type="date" class="form-control form-control-sm" name="desde" value="<?php echo $fechaInicio; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small">Hasta:</label>
                <input type="date" class="form-control form-control-sm" name="hasta" value="<?php echo $fechaFin; ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- RF-10: Selector de tipo de reporte -->
<ul class="nav nav-pills mb-3" id="reporteTabs">
    <li class="nav-item"><a class="nav-link active" href="#" data-target="todo">Todo</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-target="resumen">Resumen</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-target="clientes">Clientes</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-target="pagos">Pagos</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-target="inventario">Inventario</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-target="servicios">Servicios</a></li>
</ul>

<div class="reporte-section resumen">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-white border-start border-4 border-success shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase">Total Ingresos</h6>
                    <h3 class="text-success"><?php echo $sistema->simbolo_moneda . ' ' . number_format($balance['ingresos_totales'], 2); ?></h3>
                    <small class="text-muted"><i class="fa-solid fa-arrow-up"></i> Ventas + Servicios</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-white border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase">Total Gastos</h6>
                    <h3 class="text-danger"><?php echo $sistema->simbolo_moneda . ' ' . number_format($balance['gastos'], 2); ?></h3>
                    <small class="text-muted"><i class="fa-solid fa-arrow-down"></i> Operativos</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-primary text-white shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-white-50 small text-uppercase">UTILIDAD NETA (Ganancia Real)</h6>
                    <h1 class="display-5 fw-bold"><?php echo $sistema->simbolo_moneda . ' ' . number_format($balance['utilidad'], 2); ?></h1>
                    <small class="text-white-50">Ingresos - Gastos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Estado de Órdenes (Global)</div>
                <div class="card-body">
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Top 5 Productos Más Vendidos</div>
                <div class="card-body">
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartProductos"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Tendencia Financiera (Últimos 6 Meses)</div>
                <div class="card-body">
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chartHistorial"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($hayDatos) && !$hayDatos): ?>
    <div class="alert alert-info"><strong>MSJ-20:</strong> No existen datos para el rango seleccionado.</div>
<?php endif; ?>

<!-- RF-10: Tablas detalladas -->
<div class="reporte-section clientes">
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-users me-2"></i> Clientes con Servicios</div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>#</th><th>Cliente</th><th>Total</th></tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($reporteCompleto['ordenes'])): ?>
                                    <?php foreach($reporteCompleto['ordenes'] as $o): ?>
                                    <tr><td>ORD-<?php echo str_pad($o->id, 4, '0', STR_PAD_LEFT); ?></td><td><?php echo htmlspecialchars($o->cliente_nombre); ?></td><td class="text-end"><?php echo $sistema->simbolo_moneda . ' ' . number_format($o->total, 2); ?></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted"><strong>MSJ-20:</strong> No existen datos para el rango seleccionado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="reporte-section pagos">
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-cash-register me-2"></i> Pagos Registrados</div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>#</th><th>Orden</th><th>Monto</th><th>Método</th></tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($reporteCompleto['ventas'])): ?>
                                    <?php foreach($reporteCompleto['ventas'] as $v): ?>
                                    <tr><td><?php echo $v->id; ?></td><td><?php echo htmlspecialchars($v->cliente_nombre ?? 'General'); ?></td><td class="text-end"><?php echo $sistema->simbolo_moneda . ' ' . number_format($v->total, 2); ?></td><td>Venta</td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted"><strong>MSJ-20:</strong> No existen datos para el rango seleccionado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="reporte-section inventario">
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i> Alertas Stock Bajo</div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" id="tablaReporteStock">
                            <thead class="table-light sticky-top">
                                <tr><th>Producto</th><th>Stock</th><th>Mínimo</th></tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($reporteCompleto['stock_bajo'])): ?>
                                    <?php foreach($reporteCompleto['stock_bajo'] as $p): ?>
                                    <tr><td><?php echo htmlspecialchars($p->nombre); ?></td><td><span class="badge bg-danger"><?php echo $p->stock; ?></span></td><td><?php echo $p->stock_minimo ?? 5; ?></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-success">Todo en niveles óptimos.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="reporte-section servicios">
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between">
                    <span><i class="fa-solid fa-receipt me-2"></i> Detalle de Servicios</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr><th>#</th><th>Cliente</th><th>Fecha</th><th class="text-end">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($reporteCompleto['ordenes'])): ?>
                                    <?php foreach($reporteCompleto['ordenes'] as $o): ?>
                                    <tr><td>ORD-<?php echo str_pad($o->id, 4, '0', STR_PAD_LEFT); ?></td><td><?php echo htmlspecialchars($o->cliente_nombre); ?></td><td><?php echo date('d/m/Y', strtotime($o->fecha_entrega ?? $o->fecha_recepcion)); ?></td><td class="text-end"><?php echo $sistema->simbolo_moneda . ' ' . number_format($o->total, 2); ?></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted"><strong>MSJ-20:</strong> No existen datos para el rango seleccionado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// RF-10: Selector de tipo de reporte
document.querySelectorAll('#reporteTabs .nav-link').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#reporteTabs .nav-link').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const target = this.dataset.target;
        document.querySelectorAll('.reporte-section').forEach(s => {
            s.style.display = (target === 'todo' || s.classList.contains(target)) ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const commonOptions = { responsive: true, maintainAspectRatio: false };

    // 1. Estados
    const dEstado = <?php echo $chartEstados; ?>;
    new Chart(document.getElementById('chartEstados'), {
        type: 'doughnut',
        data: {
            labels: dEstado.labels,
            datasets: [{ data: dEstado.data, backgroundColor: dEstado.colors, borderWidth: 1 }]
        },
        options: { ...commonOptions, plugins: { legend: { position: 'right' } } }
    });

    // 2. Productos
    const dProd = <?php echo $chartProductos; ?>;
    new Chart(document.getElementById('chartProductos'), {
        type: 'bar',
        data: {
            labels: dProd.labels,
            datasets: [{ label: 'Und. Vendidas', data: dProd.data, backgroundColor: '#20c997', borderRadius: 4 }]
        },
        options: { ...commonOptions, scales: { y: { beginAtZero: true } } }
    });

    // 3. Historial (Doble Línea: Ingreso vs Gasto)
    const dHist = <?php echo $chartHistorial; ?>;
    new Chart(document.getElementById('chartHistorial'), {
        type: 'line',
        data: {
            labels: dHist.labels,
            datasets: [
                {
                    label: 'Ingresos',
                    data: dHist.ingreso,
                    borderColor: '#198754', // Verde
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Gastos',
                    data: dHist.gasto,
                    borderColor: '#dc3545', // Rojo
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: { ...commonOptions, scales: { y: { beginAtZero: true } } }
    });
</script>