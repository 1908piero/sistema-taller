<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Configuración de Empresa</h1>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php 
            if($_GET['msg'] == 'actualizado') echo "<i class='fa-solid fa-check-circle'></i> <strong>MSJ-33:</strong> Datos actualizados correctamente.";
            elseif($_GET['msg'] == 'error_backup') echo "<i class='fa-solid fa-triangle-exclamation'></i> <strong>MSJ-35:</strong> Error al generar la copia de seguridad.";
            else echo "Operación realizada.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="/configuracion/guardar" method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-building me-2"></i> Identidad del Negocio
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Sistema / Empresa *</label>
                        <input type="text" class="form-control" name="nombre_sistema" value="<?php echo $datos->nombre_sistema; ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Moneda (Código ISO)</label>
                            <input type="text" class="form-control" name="moneda" value="<?php echo $datos->moneda; ?>" placeholder="Ej: PEN, USD">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Símbolo de Moneda</label>
                            <input type="text" class="form-control" name="simbolo_moneda" value="<?php echo $datos->simbolo_moneda; ?>" placeholder="Ej: S/, $">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Impuesto (%)</label>
                        <input type="number" step="0.01" class="form-control" name="impuesto" value="<?php echo $datos->impuesto; ?>">
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-address-book me-2"></i> Datos de Contacto (Para PDF)
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono / WhatsApp</label>
                            <input type="text" class="form-control" name="telefono" value="<?php echo $datos->telefono; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" value="<?php echo $datos->email; ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección Fiscal</label>
                        <textarea class="form-control" name="direccion" rows="2"><?php echo $datos->direccion; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-file-contract me-2"></i> Textos en Documentos
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Términos y Condiciones (Orden de Servicio)</label>
                        <textarea class="form-control" name="terminos_orden" rows="4"><?php echo $datos->terminos_orden; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensaje de Despedida (Ticket Venta)</label>
                        <input type="text" class="form-control" name="mensaje_ticket" value="<?php echo $datos->mensaje_ticket; ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fa-solid fa-image me-2"></i> Logo
                </div>
                <div class="card-body">
                    <div class="mb-3 border rounded p-3 bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <?php if(!empty($datos->logo)): ?>
                            <?php if(strpos($datos->logo, 'data:') === 0): ?>
                                <img id="previewLogo" src="<?php echo $datos->logo; ?>" class="img-fluid" style="max-height: 180px;">
                            <?php elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/uploads/logo/' . $datos->logo)): ?>
                                <img id="previewLogo" src="/uploads/logo/<?php echo $datos->logo; ?>" class="img-fluid" style="max-height: 180px;">
                            <?php else: ?>
                                <img id="previewLogo" src="https://via.placeholder.com/200x100?text=Sin+Logo" class="img-fluid">
                            <?php endif; ?>
                        <?php else: ?>
                            <img id="previewLogo" src="https://via.placeholder.com/200x100?text=Sin+Logo" class="img-fluid">
                        <?php endif; ?>
                    </div>
                    
                    <label class="form-label fw-bold">Cambiar Logo</label>
                    <input type="file" class="form-control" name="logo" accept="image/*" onchange="previewImage(event)">
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Zona de Seguridad
                </div>
                <div class="card-body">
                    <p class="small text-muted">Descarga una copia completa de tu base de datos (Ventas, Clientes, Productos, etc.) para guardarla en un lugar seguro.</p>
                    <a href="/configuracion/backup" class="btn btn-outline-danger w-100">
                        <i class="fa-solid fa-download me-2"></i> MSJ-34: Descargar Respaldo (.sql)
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('previewLogo');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>