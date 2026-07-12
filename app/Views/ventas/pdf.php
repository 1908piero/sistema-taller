<?php
// --- LÓGICA PARA INCRUSTAR LOGO EN TICKET ---
$logoBase64 = null;
if (!empty($sistema->logo)) {
    $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logo/' . $sistema->logo;
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: monospace; font-size: 10px; text-align: center; margin: 0; padding: 5px; }
        .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 5px; }
        table { width: 100%; font-size: 10px; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .total { font-weight: bold; font-size: 12px; border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .barcode { margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <?php if($logoBase64): ?>
            <img src="<?php echo $logoBase64; ?>" style="max-height: 40px;"><br>
        <?php endif; ?>
        
        <strong style="font-size: 12px;"><?php echo $sistema->nombre_sistema; ?></strong><br>
        <?php echo $sistema->direccion; ?><br>
        Tel: <?php echo $sistema->telefono; ?><br><br>
        
        TICKET VENTA #<?php echo str_pad($venta->id, 6, '0', STR_PAD_LEFT); ?><br>
        Fecha: <?php echo date('d/m/Y H:i', strtotime($venta->fecha)); ?>
    </div>
    
    <div style="text-align: left; margin-bottom: 10px;">
        Cliente: <?php echo $venta->cliente_nombre ?: 'Público General'; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left">Prod</th>
                <th>Cant</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($detalles as $d): ?>
            <tr>
                <td class="text-left"><?php echo substr($d->producto_nombre, 0, 15); ?></td>
                <td><?php echo $d->cantidad; ?></td>
                <td class="text-right"><?php echo $sistema->simbolo_moneda . number_format($d->subtotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        TOTAL: <?php echo $sistema->simbolo_moneda . ' ' . number_format($venta->total, 2); ?>
    </div>
    
    <div class="barcode">
        <?php $codigo = str_pad($venta->id, 8, '0', STR_PAD_LEFT); ?>
        <img src="http://bwipjs-api.metafloor.com/?bcid=code128&text=<?php echo $codigo; ?>&scale=2&height=8&incltext=N" style="max-height: 30px;">
        <br>
        <?php echo $codigo; ?>
    </div>

    <div style="margin-top: 10px; font-size: 9px;">
        <?php echo !empty($sistema->mensaje_ticket) ? $sistema->mensaje_ticket : '¡Gracias por su preferencia!'; ?>
    </div>
</body>
</html>