<?php
// --- LÓGICA LOGO BASE64 ---
$logoBase64 = null;
if (!empty($sistema->logo)) {
    if (strpos($sistema->logo, 'data:') === 0) {
        $logoBase64 = $sistema->logo;
    } else {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logo/' . $sistema->logo;
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden #<?php echo str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-section { float: left; width: 60%; }
        .logo-text { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .meta-section { float: right; width: 40%; text-align: right; }
        .section-title { background-color: #eee; padding: 5px; font-weight: bold; margin-top: 20px; border-bottom: 1px solid #ccc; clear: both; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border-bottom: 1px solid #ddd; padding: 8px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { float: right; width: 30%; margin-top: 20px; }
        .total-row { padding: 10px; background-color: #eee; font-size: 14px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-section">
            <?php if($logoBase64): ?>
                <img src="<?php echo $logoBase64; ?>" style="max-height: 60px;">
            <?php else: ?>
                <div class="logo-text"><?php echo $sistema->nombre_sistema; ?></div>
            <?php endif; ?>
            
            <div style="font-size: 11px; margin-top: 5px;">
                <strong><?php echo $sistema->nombre_sistema; ?></strong><br>
                <?php echo $sistema->direccion; ?><br>
                Tel: <?php echo $sistema->telefono; ?> | Email: <?php echo $sistema->email; ?>
            </div>
        </div>
        
        <div class="meta-section">
            <div style="font-size: 18px; font-weight: bold;">ORDEN DE SERVICIO</div>
            <div style="font-size: 14px; margin-top: 5px;">N°: <?php echo str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></div>
            <div>Fecha: <?php echo date('d/m/Y H:i', strtotime($orden->fecha_recepcion)); ?></div>
            
            <div style="margin-top: 10px;">
                <?php 
                    $urlRastreo = "http://" . $_SERVER['HTTP_HOST'] . "/rastreo?ticket=ORD-" . str_pad($orden->id, 4, '0', STR_PAD_LEFT);
                ?>
                <img src="http://bwipjs-api.metafloor.com/?bcid=qrcode&text=<?php echo urlencode($urlRastreo); ?>&scale=3" style="width: 60px; height: 60px;">
                <div style="font-size: 9px; color: #555;">Escanear para ver estado</div>
            </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table style="margin-bottom: 20px;">
        <tr>
            <td width="50%" valign="top" style="border: none;">
                <div class="section-title" style="margin-top:0;">DATOS DEL CLIENTE</div>
                <p>
                    <strong>Cliente:</strong> <?php echo $orden->cliente_nombre; ?><br>
                    <strong>Teléfono:</strong> <?php echo $orden->cliente_telefono; ?><br>
                    <strong>Email:</strong> <?php echo $orden->cliente_email; ?><br>
                    <strong>Dirección:</strong> <?php echo $orden->cliente_direccion; ?>
                </p>
            </td>
            <td width="50%" valign="top" style="border: none;">
                <div class="section-title" style="margin-top:0;">DATOS DEL EQUIPO</div>
                <p>
                    <strong>Equipo:</strong> <?php echo $orden->equipo_tipo . ' ' . $orden->equipo_marca; ?><br>
                    <strong>Modelo:</strong> <?php echo $orden->equipo_modelo; ?><br>
                    <strong>Serie/IMEI:</strong> <?php echo $orden->equipo_serie; ?><br>
                    <strong>Falla:</strong> <?php echo $orden->falla_reportada; ?>
                </p>
            </td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE SERVICIOS Y REPUESTOS</div>
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-center" width="10%">Cant.</th>
                <th class="text-right" width="20%">P. Unit</th>
                <th class="text-right" width="20%">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($repuestos as $rep): ?>
            <tr>
                <td><?php echo $rep->producto_nombre; ?></td>
                <td class="text-center"><?php echo $rep->cantidad; ?></td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . ' ' . number_format($rep->precio_unitario, 2); ?></td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . ' ' . number_format($rep->subtotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if($orden->costo_mano_obra > 0): ?>
            <tr>
                <td>Servicio Técnico / Mano de Obra</td>
                <td class="text-center">1</td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . ' ' . number_format($orden->costo_mano_obra, 2); ?></td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . ' ' . number_format($orden->costo_mano_obra, 2); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-box">
        <table width="100%">
            <tr class="total-row">
                <td class="text-right">TOTAL A PAGAR:</td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . ' ' . number_format($orden->total, 2); ?></td>
            </tr>
        </table>
    </div>

    <div style="clear: both;"></div>

    <div style="margin-top: 50px; font-size: 10px; color: #555;">
        <p><strong>Términos y condiciones:</strong></p>
        <?php if(!empty($sistema->terminos_orden)): ?>
            <div><?php echo nl2br($sistema->terminos_orden); ?></div>
        <?php else: ?>
            <ul>
                <li>La empresa no se responsabiliza por equipos abandonados por más de 30 días.</li>
                <li>Toda reparación tiene una garantía de 30 días sobre la misma falla.</li>
                <li>Para consultar estado de su equipo escanee el código QR.</li>
            </ul>
        <?php endif; ?>
    </div>

    <div class="footer">
        Generado por <?php echo $sistema->nombre_sistema; ?> - <?php echo date('d/m/Y H:i:s'); ?>
    </div>

</body>
</html>