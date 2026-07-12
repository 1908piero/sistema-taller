<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2">Punto de Venta (POS)</h1>
    <a href="/ventas" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Historial</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label fw-bold">Buscar Producto (Escáner o Nombre)</label>
                <div class="d-flex gap-2">
                    <select id="selectProducto" class="form-select select2">
                        <option value="">Escriba para buscar...</option>
                        <?php foreach($productos as $p): ?>
                            <?php if($p->stock > 0 && $p->estado == 1): ?>
                                <option value="<?php echo $p->id; ?>" 
                                        data-nombre="<?php echo $p->nombre; ?>" 
                                        data-precio="<?php echo $p->precio_venta; ?>"
                                        data-stock="<?php echo $p->stock; ?>">
                                    <?php echo $p->codigo . ' - ' . $p->nombre; ?> (S/ <?php echo $p->precio_venta; ?>) | Stock: <?php echo $p->stock; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" onclick="agregarProducto()">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fa-solid fa-cart-shopping me-2"></i> Carrito de Compras
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" width="100">Precio</th>
                            <th class="text-center" width="100">Cant.</th>
                            <th class="text-end" width="100">Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductos">
                        <tr id="filaVacia">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-basket-shopping fa-3x mb-3 opacity-25"></i>
                                <p>El carrito está vacío.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white mb-3">
            <div class="card-body text-center">
                <h6 class="text-white-50 text-uppercase">Total a Pagar</h6>
                <h1 class="display-4 fw-bold" id="displayTotal">S/ 0.00</h1>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/ventas/guardar" method="POST" id="formVenta">
                    <input type="hidden" name="productos_json" id="inputProductos">
                    <input type="hidden" name="total_venta" id="inputTotal">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cliente</label>
                        <select class="form-select select2" name="cliente_id" required>
                            <option value="">Seleccionar Cliente...</option>
                            <?php foreach($clientes as $c): ?>
                                <?php if($c->estado == 1): ?>
                                    <option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            <a href="/clientes" target="_blank" class="text-decoration-none small"><i class="fa-solid fa-user-plus"></i> Registrar nuevo</a>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" onclick="procesarVenta()">
                            <i class="fa-solid fa-check-circle me-2"></i> Confirmar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    let carrito = [];

    function agregarProducto() {
        const select = $('#selectProducto'); // Usamos jQuery por Select2
        const id = select.val();
        
        if (!id) return alert("Seleccione un producto");

        // Obtener datos del option seleccionado
        const option = select.find(':selected');
        const nombre = option.data('nombre');
        const precio = parseFloat(option.data('precio'));
        const stock = parseInt(option.data('stock'));

        // Verificar si ya existe
        const existe = carrito.find(p => p.id == id);

        if (existe) {
            if (existe.cantidad >= stock) return alert("No hay más stock disponible.");
            existe.cantidad++;
            existe.subtotal = existe.cantidad * existe.precio;
        } else {
            carrito.push({
                id: id,
                nombre: nombre,
                precio: precio,
                cantidad: 1,
                subtotal: precio,
                stock: stock
            });
        }

        // Resetear select
        select.val(null).trigger('change');
        renderizarTabla();
    }

    function eliminarProducto(index) {
        carrito.splice(index, 1);
        renderizarTabla();
    }

    function renderizarTabla() {
        const tbody = document.getElementById('tablaProductos');
        const displayTotal = document.getElementById('displayTotal');
        const inputTotal = document.getElementById('inputTotal');
        const inputJson = document.getElementById('inputProductos');
        const filaVacia = document.getElementById('filaVacia');

        // Limpiar tabla (menos fila vacía si aplica)
        tbody.innerHTML = '';

        if (carrito.length === 0) {
            tbody.appendChild(filaVacia);
            displayTotal.innerText = "S/ 0.00";
            inputTotal.value = 0;
            inputJson.value = '';
            return;
        }

        let totalGeneral = 0;

        carrito.forEach((prod, index) => {
            totalGeneral += prod.subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${prod.nombre}</td>
                <td class="text-center">S/ ${prod.precio.toFixed(2)}</td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border px-3">${prod.cantidad}</span>
                </td>
                <td class="text-end fw-bold">S/ ${prod.subtotal.toFixed(2)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm text-danger" onclick="eliminarProducto(${index})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        displayTotal.innerText = "S/ " + totalGeneral.toFixed(2);
        inputTotal.value = totalGeneral;
        inputJson.value = JSON.stringify(carrito);
    }

    function procesarVenta() {
        if (carrito.length === 0) return alert("El carrito está vacío.");
        if (confirm("¿Confirmar venta por " + document.getElementById('displayTotal').innerText + "?")) {
            document.getElementById('formVenta').submit();
        }
    }
</script>