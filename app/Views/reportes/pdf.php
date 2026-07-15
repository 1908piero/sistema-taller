<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Gerencial</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 5px; }
        h3 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 3px; }
        .fecha { text-align: center; color: #666; font-size: 9pt; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #2c3e50; color: white; padding: 6px; text-align: left; font-size: 9pt; }
        td { padding: 5px 6px; border-bottom: 1px solid #ddd; font-size: 9pt; }
        .total-row { font-weight: bold; background: #f8f9fa; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 8pt; border-radius: 3px; }
        .badge-danger { background: #dc3545; color: white; }
        .section { margin-bottom: 25px; }
        .resumen { display: flex; justify-content: space-around; margin: 15px 0; }
        .resumen-item { text-align: center; padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 30%; }
        .resumen-item h4 { margin: 0; font-size: 14pt; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h1><?php echo $sistema->nombre_sistema ?? 'Sistema Taller'; ?></h1>
    <div class="fecha">Reporte del <?php echo date('d/m/Y', strtotime($fechaInicio)); ?> al <?php echo date('d/m/Y', strtotime($fechaFin)); ?></div>

    <div class="resumen">
        <div class="resumen-item" style="border-color: #198754;">
            <div>Total Ingresos</div>
            <h4 style="color: #198754;"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($balance['ingresos_totales'], 2); ?></h4>
        </div>
        <div class="resumen-item" style="border-color: #dc3545;">
            <div>Total Gastos</div>
            <h4 style="color: #dc3545;"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($balance['gastos'], 2); ?></h4>
        </div>
        <div class="resumen-item" style="border-color: #0d6efd;">
            <div>Utilidad Neta</div>
            <h4 style="color: #0d6efd;"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($balance['utilidad'], 2); ?></h4>
        </div>
    </div>

    <?php if (!empty($reporteCompleto['ventas'])): ?>
    <div class="section">
        <h3>Ventas Realizadas</h3>
        <table>
            <tr><th># Venta</th><th>Cliente</th><th>Fecha</th><th class="text-end">Total</th></tr>
            <?php foreach($reporteCompleto['ventas'] as $v): ?>
            <tr>
                <td><?php echo $v->id; ?></td>
                <td><?php echo htmlspecialchars($v->cliente_nombre ?? 'General'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($v->fecha)); ?></td>
                <td class="text-end"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($v->total, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($reporteCompleto['ordenes'])): ?>
    <div class="section">
        <h3>Órdenes Entregadas</h3>
        <table>
            <tr><th># Orden</th><th>Cliente</th><th>Falla</th><th>Fecha Entrega</th><th class="text-end">Total</th></tr>
            <?php foreach($reporteCompleto['ordenes'] as $o): ?>
            <tr>
                <td><?php echo $o->id; ?></td>
                <td><?php echo htmlspecialchars($o->cliente_nombre); ?></td>
                <td><small><?php echo substr($o->falla_reportada, 0, 30); ?></small></td>
                <td><?php echo date('d/m/Y', strtotime($o->fecha_entrega ?? $o->fecha_recepcion)); ?></td>
                <td class="text-end"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($o->total, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($reporteCompleto['gastos'])): ?>
    <div class="section">
        <h3>Gastos Registrados</h3>
        <table>
            <tr><th>Descripción</th><th>Categoría</th><th>Fecha</th><th class="text-end">Monto</th></tr>
            <?php foreach($reporteCompleto['gastos'] as $g): ?>
            <tr>
                <td><?php echo htmlspecialchars($g->descripcion); ?></td>
                <td><?php echo ucfirst($g->categoria); ?></td>
                <td><?php echo date('d/m/Y', strtotime($g->fecha)); ?></td>
                <td class="text-end"><?php echo ($sistema->simbolo_moneda ?? 'S/') . ' ' . number_format($g->monto, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($reporteCompleto['stock_bajo'])): ?>
    <div class="section">
        <h3>Alertas de Stock Bajo</h3>
        <table>
            <tr><th>Producto</th><th>Código</th><th class="text-end">Stock Actual</th><th class="text-end">Stock Mínimo</th></tr>
            <?php foreach($reporteCompleto['stock_bajo'] as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p->nombre); ?></td>
                <td><?php echo $p->codigo; ?></td>
                <td class="text-end"><span class="badge badge-danger"><?php echo $p->stock; ?></span></td>
                <td class="text-end"><?php echo $p->stock_minimo ?? 5; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div style="text-align: center; color: #999; font-size: 8pt; margin-top: 30px;">
        Generado el <?php echo date('d/m/Y H:i:s'); ?> por <?php echo $_SESSION['user_name'] ?? 'Sistema'; ?>
    </div>
</body>
</html>
