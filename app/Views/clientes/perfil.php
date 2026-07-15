<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 24px;">
            <?php echo strtoupper(substr($cliente->nombre, 0, 1)); ?>
        </div>
        <div>
            <h2 class="mb-0"><?php echo $cliente->nombre; ?></h2>
            <div class="text-muted small">
                <i class="fa-solid fa-id-card me-1"></i> <?php echo $cliente->dni ?: 'Sin DNI'; ?> | 
                <i class="fa-solid fa-envelope me-1"></i> <?php echo $cliente->email ?: 'Sin email'; ?> | 
                <i class="fa-solid fa-phone me-1"></i> <?php echo $cliente->telefono ?: 'Sin teléfono'; ?>
            </div>
        </div>
    </div>
    <a href="/clientes" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-4 border-success">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Valor del Cliente (LTV)</h6>
                <h3 class="fw-bold text-success mb-0"><?php echo $sistema->simbolo_moneda . ' ' . number_format($stats['total_gastado'], 2); ?></h3>
                <small class="text-muted">Total histórico invertido</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-4 border-primary">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Servicios Técnicos</h6>
                <h3 class="fw-bold text-primary mb-0"><?php echo $stats['servicios_count']; ?></h3>
                <small class="text-muted">Equipos reparados</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-start border-4 border-info">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small">Compras Realizadas</h6>
                <h3 class="fw-bold text-info mb-0"><?php echo $stats['ventas_count']; ?></h3>
                <small class="text-muted">Ventas de mostrador</small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="ordenes-tab" data-bs-toggle="tab" data-bs-target="#ordenes" type="button">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i> Historial de Servicios
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="ventas-tab" data-bs-toggle="tab" data-bs-target="#ventas" type="button">
                    <i class="fa-solid fa-cart-shopping me-2"></i> Historial de Compras
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                    <i class="fa-solid fa-user-gear me-2"></i> Datos Personales
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="ordenes">
                <?php if(empty($ordenes)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                        <p>Este cliente aún no ha traído equipos para reparar.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Equipo / Falla</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-end">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ordenes as $o): ?>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if($o->estado == 'pendiente') $badge = 'bg-warning text-dark';
                                        if($o->estado == 'reparado') $badge = 'bg-primary';
                                        if($o->estado == 'entregado') $badge = 'bg-success';
                                    ?>
                                    <tr>
                                        <td><strong>ORD-<?php echo str_pad($o->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <strong><?php echo $o->equipo_tipo . ' ' . $o->equipo_marca; ?></strong><br>
                                            <small class="text-muted"><?php echo $o->falla_reportada; ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($o->fecha_recepcion)); ?></td>
                                        <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($o->estado); ?></span></td>
                                        <td class="text-end fw-bold"><?php echo $sistema->simbolo_moneda . number_format($o->total, 2); ?></td>
                                        <td class="text-end">
                                            <a href="/ordenes/detalle?id=<?php echo $o->id; ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="ventas">
                <?php if(empty($ventas)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-25"></i>
                        <p>No ha realizado compras directas en tienda.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket Venta</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ventas as $v): ?>
                                    <tr>
                                        <td><strong>TICKET #<?php echo str_pad($v->id, 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($v->fecha)); ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo $sistema->simbolo_moneda . number_format($v->total, 2); ?></td>
                                        <td class="text-end">
                                            <a href="/ventas/imprimir?id=<?php echo $v->id; ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf"></i> PDF</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="info">
                <form action="/clientes/actualizar" method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo $cliente->id; ?>">
                    <div class="col-md-6">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" value="<?php echo $cliente->nombre; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" name="telefono" value="<?php echo $cliente->telefono; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $cliente->email; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" value="<?php echo $cliente->direccion; ?>">
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i> Actualizar Datos</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>