<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Inventario de Productos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalCrear()">
            <i class="fa-solid fa-plus"></i> Nuevo Producto
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'guardado') echo "Producto guardado correctamente.";
            elseif($_GET['msg'] == 'actualizado') echo "Producto actualizado.";
            elseif($_GET['msg'] == 'ajuste_ok') echo "Stock ajustado y movimiento registrado en Kardex.";
            elseif($_GET['msg'] == 'error_stock') echo "Error: Stock insuficiente para realizar la salida.";
            else echo "Operación realizada.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($productos)): ?>
                        <?php foreach($productos as $prod): ?>
                            <tr class="<?php echo ($prod->estado == 0) ? 'table-secondary opacity-75' : ''; ?>">
                                <td>
                                    <?php if($prod->imagen): ?>
                                        <?php if(strpos($prod->imagen, 'data:') === 0): ?>
                                            <img src="<?php echo $prod->imagen; ?>" class="rounded" width="40" height="40" style="object-fit: cover;">
                                        <?php else: ?>
                                            <img src="/uploads/productos/<?php echo $prod->imagen; ?>" class="rounded" width="40" height="40" style="object-fit: cover;">
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="bg-secondary rounded text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $prod->codigo; ?></td>
                                <td>
                                    <strong><?php echo $prod->nombre; ?></strong>
                                    <?php if($prod->stock <= 5): ?>
                                        <span class="badge bg-danger ms-1" style="font-size: 0.6em;">Bajo</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info text-dark"><?php echo ucfirst($prod->categoria); ?></span></td>
                                <td class="fw-bold fs-5 <?php echo $prod->stock > 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $prod->stock; ?>
                                </td>
                                <td><?php echo $sistema->simbolo_moneda . ' ' . number_format($prod->precio_venta, 2); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick='editarProducto(<?php echo json_encode($prod); ?>)' title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <?php if($prod->estado == 1): ?>
                                        <button class="btn btn-sm btn-outline-warning text-dark" onclick='ajustarStock(<?php echo json_encode($prod); ?>)' title="Ajustar Stock (Entrada/Salida)">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                        <?php endif; ?>

                                        <a href="/productos/historial?id=<?php echo $prod->id; ?>" class="btn btn-sm btn-outline-secondary" title="Ver Movimientos">
                                            <i class="fa-solid fa-list-ul"></i>
                                        </a>
                                        
                                        <?php if($prod->estado == 1): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="cambiarEstado(<?php echo $prod->id; ?>, 0)" title="Desactivar">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="cambiarEstado(<?php echo $prod->id; ?>, 1)" title="Activar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tituloModal">Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProducto" action="/productos/guardar" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="prodId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Código *</label>
                                    <input type="text" class="form-control" name="codigo" id="codigo" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria" id="categoria">
                                        <option value="repuesto">Repuesto</option>
                                        <option value="accesorio">Accesorio</option>
                                        <option value="equipo">Equipo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stock Inicial</label>
                                    <input type="number" class="form-control" name="stock" id="stock" value="0" readonly>
                                    <div class="form-text">Para modificar stock use el botón "Ajustar".</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Precio Compra</label>
                                    <input type="number" step="0.01" class="form-control" name="precio_compra" id="precio_compra">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Precio Venta *</label>
                                    <input type="number" step="0.01" class="form-control" name="precio_venta" id="precio_venta" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="form-label">Imagen</label>
                            <div class="border rounded p-2 mb-2 bg-light">
                                <img id="previewImg" src="https://via.placeholder.com/150?text=Sin+Imagen" class="img-fluid" style="max-height: 150px;">
                            </div>
                            <input type="file" class="form-control form-control-sm" name="imagen" accept="image/*" onchange="previewFile()">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAjuste" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-dark"><i class="fa-solid fa-boxes-stacked"></i> Ajustar Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/productos/ajustar" method="POST">
                <input type="hidden" name="id" id="ajusteId">
                <div class="modal-body">
                    <h6 id="ajusteNombre" class="fw-bold mb-3"></h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Movimiento</label>
                            <select class="form-select" name="tipo">
                                <option value="entrada">🔵 ENTRADA (Compras)</option>
                                <option value="salida">🔴 SALIDA (Merma/Uso)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" name="cantidad" required min="1" placeholder="Ej: 5">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo / Detalle</label>
                        <textarea class="form-control" name="motivo" rows="2" required placeholder="Ej: Compra Factura F001 / Pantalla rota en taller"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="formEstado" action="/productos/cambiar-estado" method="POST">
    <input type="hidden" name="id" id="idEstado">
    <input type="hidden" name="nuevo_estado" id="nuevoEstado">
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
    const modalAjuste = new bootstrap.Modal(document.getElementById('modalAjuste'));

    function abrirModalCrear() {
        document.getElementById('formProducto').reset();
        document.getElementById('prodId').value = '';
        document.getElementById('stock').readOnly = false; // Stock inicial editable solo al crear
        document.getElementById('tituloModal').innerText = 'Nuevo Producto';
        document.getElementById('formProducto').action = '/productos/guardar';
        document.getElementById('previewImg').src = 'https://via.placeholder.com/150?text=Sin+Imagen';
        modal.show();
    }

    function editarProducto(prod) {
        document.getElementById('prodId').value = prod.id;
        document.getElementById('codigo').value = prod.codigo;
        document.getElementById('nombre').value = prod.nombre;
        document.getElementById('categoria').value = prod.categoria;
        document.getElementById('stock').value = prod.stock;
        document.getElementById('stock').readOnly = true; // No editar stock aquí, usar ajuste
        document.getElementById('precio_compra').value = prod.precio_compra;
        document.getElementById('precio_venta').value = prod.precio_venta;
        
        if(prod.imagen) {
            if(prod.imagen.startsWith('data:')) document.getElementById('previewImg').src = prod.imagen;
            else document.getElementById('previewImg').src = '/uploads/productos/' + prod.imagen;
        } else document.getElementById('previewImg').src = 'https://via.placeholder.com/150?text=Sin+Imagen';

        document.getElementById('tituloModal').innerText = 'Editar Producto';
        document.getElementById('formProducto').action = '/productos/actualizar';
        modal.show();
    }

    function ajustarStock(prod) {
        document.getElementById('ajusteId').value = prod.id;
        document.getElementById('ajusteNombre').innerText = prod.codigo + ' - ' + prod.nombre + ' (Stock Actual: ' + prod.stock + ')';
        modalAjuste.show();
    }

    function cambiarEstado(id, estado) {
        if(confirm('¿Confirmar cambio de estado?')) {
            document.getElementById('idEstado').value = id;
            document.getElementById('nuevoEstado').value = estado;
            document.getElementById('formEstado').submit();
        }
    }

    function previewFile() {
        const preview = document.getElementById('previewImg');
        const file = document.querySelector('input[type=file]').files[0];
        const reader = new FileReader();
        reader.onloadend = function () { preview.src = reader.result; }
        if (file) reader.readAsDataURL(file);
    }
</script>