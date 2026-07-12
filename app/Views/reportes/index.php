<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes Gerenciales</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
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