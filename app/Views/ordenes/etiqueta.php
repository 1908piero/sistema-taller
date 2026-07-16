<!DOCTYPE html>
<html>
<head>
    <style>
        body { margin: 0; padding: 5px; font-family: sans-serif; border: 2px dashed #ccc; height: 100%; box-sizing: border-box; }
        .container { text-align: center; }
        .title { font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .ticket { font-size: 16px; font-weight: bold; margin: 2px 0; }
        .client { font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .date { font-size: 8px; color: #555; }
        .qr { margin-top: 2px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="title"><?php echo substr($sistema->nombre_sistema, 0, 20); ?></div>
        <div class="ticket">ORD-<?php echo str_pad($orden->id, 4, '0', STR_PAD_LEFT); ?></div>
        
        <div class="qr">
            <?php 
                // Generamos URL dinámica basada en tu servidor
                $urlRastreo = "http://" . $_SERVER['HTTP_HOST'] . "/rastreo?ticket=ORD-" . str_pad($orden->id, 4, '0', STR_PAD_LEFT);
                // Usamos la API para generar el QR
            ?>
            <img src="http://bwipjs-api.metafloor.com/?bcid=qrcode&text=<?php echo urlencode($urlRastreo); ?>&scale=3" style="width: 50px; height: 50px;" alt="Código QR de rastreo">
        </div>

        <div class="client"><?php echo $orden->cliente_nombre; ?></div>
        <div class="date"><?php echo date('d/m/Y H:i', strtotime($orden->fecha_recepcion)); ?></div>
    </div>
</body>
</html>