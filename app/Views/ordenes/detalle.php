<?php require_once __DIR__ . '/../partials/header.php'; ?>

<?php if (isset($_GET['msg'])): ?>
    <?php if($_GET['msg'] == 'diagnostico_ok'): ?>
        <div class="alert alert-success alert-dismissible fade show"><strong>MSJ-26:</strong> Diagnóstico guardado correctamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php elseif($_GET['msg'] == 'diagnostico_error'): ?>
        <div class="alert alert-danger alert-dismissible fade show"><strong>MSJ-27:</strong> Error al guardar el diagnóstico.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/ordenes" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado
    </a>
    
    <div>
        <?php if(!empty($orden->cliente_telefono)): ?>
            <?php 
                $mensaje = "Hola " . $orden->cliente_nombre . ", te escribimos de " . $sistema->nombre_sistema . ". Estado de tu orden #" . str_pad($orden->id, 4, '0', STR_PAD_LEFT) . ": " . strtoupper($orden->estado) . ".";
                $linkWhatsapp = "https://wa.me/51" . preg_replace('/[^0-9]/', '', $orden->cliente_telefono) . "?text=" . urlencode($mensaje);
            ?>
            <a href="<?php echo $linkWhatsapp; ?>" target="_blank" class="btn btn-success text-white me-2">
                <i class="fa-brands fa-whatsapp"></i> Notificar
            </a>
        <?php endif; ?>

        <a href="/ordenes/etiqueta?id=<?php echo $orden->id; ?>" target="_blank" class="btn btn-warning me-2">
            <i class="fa-solid fa-tag"></i> Etiqueta
        </a>

        <?php if($orden->estado == 'reparado' || $orden->estado == 'entregado'): ?>
            <a href="/ordenes/garantia?id=<?php echo $orden->id; ?>" target="_blank" class="btn btn-info text-white me-2">
                <i class="fa-solid fa-certificate"></i> Garantía
            </a>
        <?php endif; ?>

        <a href="/ordenes/imprimir?id=<?php echo $orden->id; ?>" target="_blank" class="btn btn-danger">
            <i class="fa-solid fa-file-pdf me-2"></i> Imprimir Orden
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body text-center">
                <h5 class="text-muted mb-3">Orden #<?php echo str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></h5>
                
                <?php 
                    $badgeClass = 'bg-secondary';
                    if($orden->estado == 'pendiente') $badgeClass = 'bg-warning text-dark';
                    if($orden->estado == 'diagnostico') $badgeClass = 'bg-info text-dark';
                    if($orden->estado == 'reparado') $badgeClass = 'bg-primary';
                    if($orden->estado == 'entregado') $badgeClass = 'bg-success';
                    if($orden->estado == 'cancelado') $badgeClass = 'bg-danger';
                ?>
                <span class="badge <?php echo $badgeClass; ?> fs-5 mb-3 px-4 py-2"><?php echo strtoupper($orden->estado); ?></span>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Cambiar Estado</button>
                    <ul class="dropdown-menu w-100">
                        <li>
                            <form action="/ordenes/cambiar-estado" method="POST">
                                <input type="hidden" name="id" value="<?php echo $orden->id; ?>">
                                <input type="hidden" name="nuevo_estado" value="pendiente">
                                <button class="dropdown-item">Pendiente</button>
                            </form>
                        </li>
                        <li>
                            <form action="/ordenes/cambiar-estado" method="POST">
                                <input type="hidden" name="id" value="<?php echo $orden->id; ?>">
                                <input type="hidden" name="nuevo_estado" value="diagnostico">
                                <button class="dropdown-item">En Diagnóstico</button>
                            </form>
                        </li>
                        <li>
                            <form action="/ordenes/cambiar-estado" method="POST">
                                <input type="hidden" name="id" value="<?php echo $orden->id; ?>">
                                <input type="hidden" name="nuevo_estado" value="reparado">
                                <button class="dropdown-item">Reparado</button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="/ordenes/cambiar-estado" method="POST">
                                <input type="hidden" name="id" value="<?php echo $orden->id; ?>">
                                <input type="hidden" name="nuevo_estado" value="entregado">
                                <button class="dropdown-item text-success fw-bold">Entregar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-bold"><i class="fa-solid fa-laptop me-2"></i> Equipo</div>
            <div class="card-body">
                <p class="mb-1"><strong>Equipo:</strong> <?php echo $orden->equipo_tipo . ' ' . $orden->equipo_marca; ?></p>
                <p class="mb-1"><strong>Modelo:</strong> <?php echo $orden->equipo_modelo; ?></p>
                <p class="mb-1"><strong>Serie:</strong> <?php echo $orden->equipo_serie; ?></p>
                <hr>
                <p class="mb-1 text-danger"><strong>Falla Reportada:</strong></p>
                <p class="text-muted fst-italic">"<?php echo $orden->falla_reportada; ?>"</p>
            </div>
        </div>

        <div class="card shadow-sm mb-3 border-info">
            <div class="card-header bg-info text-white fw-bold"><i class="fa-solid fa-stethoscope me-2"></i> Informe Técnico</div>
            <div class="card-body">
                <form action="/ordenes/diagnostico" method="POST">
                    <input type="hidden" name="orden_id" value="<?php echo $orden->id; ?>">
                    <div class="mb-2">
                        <textarea class="form-control" name="diagnostico" rows="5" placeholder="Escriba aquí el diagnóstico detallado..."><?php echo $orden->observaciones_tecnicas ?: $orden->diagnostico; ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-sm btn-info text-white">Guardar Informe</button>
                    </div>
                    <small class="text-muted">MSJ-26: El diagnóstico quedará registrado en el historial.</small>
                </form>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white fw-bold"><i class="fa-solid fa-user me-2"></i> Cliente</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $orden->cliente_nombre; ?></h5>
                <p class="card-text mb-1"><i class="fa-solid fa-phone text-muted me-2"></i> <?php echo $orden->cliente_telefono; ?></p>
                <p class="card-text mb-1"><i class="fa-solid fa-envelope text-muted me-2"></i> <?php echo $orden->cliente_email; ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- RF-05: Servicios del Catálogo -->
        <div class="card shadow-sm mb-3 border-success">
            <div class="card-header bg-success text-white fw-bold">
                <i class="fa-solid fa-list me-2"></i> Servicios <small>(MSJ-29: Servicios del catálogo)</small>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>Servicio</th>
                            <th>Técnico</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">P. Unit</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($servicios)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-2">No se han agregado servicios.</td></tr>
                        <?php else: ?>
                            <?php foreach($servicios as $sv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sv->servicio_nombre); ?></td>
                                    <td><small><?php echo htmlspecialchars($sv->tecnico_asignado ?: '-'); ?></small></td>
                                    <td class="text-center"><?php echo $sv->cantidad; ?></td>
                                    <td class="text-end"><?php echo $sistema->simbolo_moneda . number_format($sv->precio_unitario, 2); ?></td>
                                    <td class="text-end"><?php echo $sistema->simbolo_moneda . number_format($sv->subtotal, 2); ?></td>
                                    <td class="text-end">
                                        <?php if($orden->estado != 'entregado'): ?>
                                        <form action="/ordenes/eliminar-servicio" method="POST" onsubmit="return confirm('¿Eliminar servicio?');">
                                            <input type="hidden" name="detalle_id" value="<?php echo $sv->id; ?>">
                                            <input type="hidden" name="orden_id" value="<?php echo $orden->id; ?>">
                                            <button type="submit" class="btn btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if($orden->estado != 'entregado'): ?>
                        <tr class="bg-light">
                            <form action="/ordenes/agregar-servicio" method="POST">
                                <input type="hidden" name="orden_id" value="<?php echo $orden->id; ?>">
                                <td>
                                    <select class="form-select form-select-sm" name="servicio_id" required>
                                        <option value="">+ Seleccionar Servicio</option>
                                        <?php foreach($serviciosCatalogo ?? [] as $sc): ?>
                                            <option value="<?php echo $sc->id; ?>"><?php echo htmlspecialchars($sc->nombre); ?> (<?php echo $sistema->simbolo_moneda . $sc->precio; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="tecnico_asignado" placeholder="Técnico">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="cantidad" value="1" min="1">
                                </td>
                                <td colspan="3">
                                    <button type="submit" class="btn btn-sm btn-success w-100"><i class="fa-solid fa-plus"></i> Agregar</button>
                                </td>
                            </form>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="fa-solid fa-wrench me-2"></i> Repuestos y Materiales</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">P. Unit</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($repuestos)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No se han agregado repuestos.</td></tr>
                        <?php else: ?>
                            <?php foreach($repuestos as $rep): ?>
                                <tr>
                                    <td><?php echo $rep->producto_nombre; ?></td>
                                    <td class="text-center"><?php echo $rep->cantidad; ?></td>
                                    <td class="text-end"><?php echo $sistema->simbolo_moneda . number_format($rep->precio_unitario, 2); ?></td>
                                    <td class="text-end"><?php echo $sistema->simbolo_moneda . number_format($rep->subtotal, 2); ?></td>
                                    <td class="text-end">
                                        <?php if($orden->estado != 'entregado'): ?>
                                        <form action="/ordenes/eliminar-repuesto" method="POST" onsubmit="return confirm('¿Devolver al inventario?');">
                                            <input type="hidden" name="detalle_id" value="<?php echo $rep->id; ?>">
                                            <button type="submit" class="btn btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if($orden->estado != 'entregado'): ?>
                        <tr class="bg-light">
                            <form action="/ordenes/agregar-repuesto" method="POST">
                                <input type="hidden" name="orden_id" value="<?php echo $orden->id; ?>">
                                <td colspan="2">
                                    <select class="form-select form-select-sm select2" name="producto_id" required style="width: 100%;">
                                        <option value="">+ Buscar Repuesto...</option>
                                        <?php foreach($productos as $prod): ?>
                                            <?php if($prod->stock > 0 && $prod->estado == 1): ?>
                                            <option value="<?php echo $prod->id; ?>">
                                                <?php echo $prod->nombre; ?> (Stock: <?php echo $prod->stock; ?>) - <?php echo $sistema->simbolo_moneda . $prod->precio_venta; ?>
                                            </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm" name="cantidad" value="1" min="1" placeholder="Cant">
                                </td>
                                <td colspan="2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-plus"></i> Agregar</button>
                                </td>
                            </form>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="text-end align-middle"><strong>Mano de Obra:</strong></td>
                            <td class="text-end">
                                <?php if($orden->estado != 'entregado'): ?>
                                    <form action="/ordenes/mano-obra" method="POST" class="d-flex justify-content-end">
                                        <input type="hidden" name="orden_id" value="<?php echo $orden->id; ?>">
                                        <div class="input-group input-group-sm" style="width: 120px;">
                                            <span class="input-group-text"><?php echo $sistema->simbolo_moneda; ?></span>
                                            <input type="number" step="0.01" name="costo_mano_obra" class="form-control text-end" value="<?php echo $orden->costo_mano_obra; ?>" onchange="this.form.submit()">
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <?php echo $sistema->simbolo_moneda . number_format($orden->costo_mano_obra, 2); ?>
                                <?php endif; ?>
                            </td>
                            <td></td>
                        </tr>
                        <tr class="table-dark fs-5">
                            <td colspan="3" class="text-end"><strong>TOTAL GENERAL:</strong></td>
                            <td class="text-end fw-bold"><?php echo $sistema->simbolo_moneda . number_format($orden->total, 2); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="fa-solid fa-clock-rotate-left me-2"></i> Bitácora
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <?php if(empty($historial)): ?>
                    <p class="text-muted text-center py-3">No hay actividad.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($historial as $h): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?php echo $h->accion; ?> <span class="badge bg-light text-dark border ms-2"><?php echo $h->usuario_nombre; ?></span></div>
                                    <small class="text-muted"><?php echo $h->detalle; ?></small>
                                </div>
                                <span class="badge bg-secondary rounded-pill"><?php echo date('d/m/Y H:i', strtotime($h->fecha)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>