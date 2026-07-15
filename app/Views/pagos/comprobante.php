<?php
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
    <title>Comprobante #<?php echo $pago->id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 10px; color: #000; width: 80mm; padding: 5mm; }
        .center { text-align: center; }
        .header { margin-bottom: 8px; }
        .header h2 { font-size: 14px; margin-bottom: 2px; }
        .header p { font-size: 9px; color: #333; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        .label { font-weight: bold; }
        .total { font-size: 14px; font-weight: bold; text-align: center; margin: 6px 0; }
        .footer { text-align: center; font-size: 8px; margin-top: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; }
        th, td { padding: 2px 0; text-align: left; }
        .text-right { text-align: right; }
        img { max-height: 40px; }
    </style>
</head>
<body>

    <div class="center header">
        <?php if($logoBase64): ?>
            <img src="<?php echo $logoBase64; ?>"><br>
        <?php endif; ?>
        <h2><?php echo htmlspecialchars($sistema->nombre_sistema ?? 'COMPROBANTE DE PAGO'); ?></h2>
        <p><?php echo htmlspecialchars($sistema->direccion ?? ''); ?></p>
        <p><?php echo htmlspecialchars($sistema->telefono ?? ''); ?></p>
    </div>

    <div class="divider"></div>

    <div class="center" style="font-weight:bold; font-size:12px;">COMPROBANTE DE PAGO</div>

    <div class="divider"></div>

    <div class="row"><span class="label">N° Comprobante:</span><span><?php echo str_pad($pago->id, 6, '0', STR_PAD_LEFT); ?></span></div>
    <div class="row"><span class="label">Fecha:</span><span><?php echo date('d/m/Y H:i', strtotime($pago->fecha)); ?></span></div>
    <div class="row"><span class="label">Orden #:</span><span>ORD-<?php echo str_pad($pago->orden_id, 4, '0', STR_PAD_LEFT); ?></span></div>

    <div class="divider"></div>

    <div class="row"><span class="label">Cliente:</span><span><?php echo htmlspecialchars($pago->cliente_nombre ?? 'N/N'); ?></span></div>
    <div class="row"><span class="label">Atendido por:</span><span><?php echo htmlspecialchars($pago->usuario_nombre ?? ''); ?></span></div>

    <div class="divider"></div>

    <div class="row"><span class="label">Método de pago:</span><span><?php echo ucfirst($pago->metodo_pago); ?></span></div>
    <?php if($pago->referencia): ?>
    <div class="row"><span class="label">Referencia:</span><span><?php echo htmlspecialchars($pago->referencia); ?></span></div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="total">S/ <?php echo number_format($pago->monto, 2); ?></div>

    <div class="divider"></div>

    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        <p><?php echo htmlspecialchars($sistema->nombre_sistema ?? ''); ?></p>
    </div>

</body>
</html>
