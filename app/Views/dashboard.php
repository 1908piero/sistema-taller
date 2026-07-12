<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Panel de Control</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/ordenes" class="btn btn-sm btn-outline-secondary">Ver Órdenes</a>
            <a href="/ventas" class="btn btn-sm btn-outline-secondary">Ver Ventas</a>
        </div>
        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-plus"></i> Nuevo
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/ordenes">Nueva Orden</a></li>
            <li><a class="dropdown-item" href="/ventas/crear">Nueva Venta</a></li>
            <li><a class="dropdown-item" href="/clientes">Nuevo Cliente</a></li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-start border-4 border-warning shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Equipos en Taller (Pendientes)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pendientes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-screwdriver-wrench fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-start border-4 border-success shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Listos para Entregar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $listos; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-check-circle fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-start border-4 border-primary shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Ingresos del Mes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $sistema->simbolo_moneda . ' ' . number_format($ingresos, 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-sack-dollar fa-2x text-gray-300 opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i> Últimas 5 Órdenes Recibidas</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket</th>
                                <th>Cliente</th>
                                <th>Equipo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-end">Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">No hay actividad reciente.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recientes as $orden): ?>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($orden->estado == 'pendiente') $badge = 'bg-warning text-dark';
                                        if($orden->estado == 'reparado') $badge = 'bg-primary';
                                        if($orden->estado == 'entregado') $badge = 'bg-success';
                                    ?>
                                    <tr>
                                        <td><strong><?php echo 'ORD-' . str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo $orden->cliente; ?></td>
                                        <td><?php echo $orden->equipo_modelo; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($orden->fecha_recepcion)); ?></td>
                                        <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($orden->estado); ?></span></td>
                                        <td class="text-end">
                                            <a href="/ordenes/detalle?id=<?php echo $orden->id; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-2">
                    <a href="/ordenes" class="text-decoration-none small">Ver todas las órdenes &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>