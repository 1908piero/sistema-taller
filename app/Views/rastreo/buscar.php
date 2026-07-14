<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Estado - <?php echo $sistema->nombre_sistema; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .search-container { max-width: 600px; margin: 50px auto; }
        .timeline-steps { display: flex; justify-content: center; flex-wrap: wrap; }
        .timeline-steps .step { padding: 10px 20px; text-align: center; position: relative; opacity: 0.5; }
        .timeline-steps .step.active { opacity: 1; font-weight: bold; color: #0d6efd; }
        .timeline-steps .step.active .icon { background: #0d6efd; color: white; border-color: #0d6efd; }
        .timeline-steps .step .icon { 
            width: 40px; height: 40px; border: 2px solid #ccc; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;
            background: white; z-index: 2; position: relative;
        }
        /* Línea conectora simple */
        .step-connector { height: 2px; background: #e0e0e0; margin-top: -30px; margin-bottom: 30px; position: relative; z-index: 1; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm mb-4">
    <div class="container justify-content-center">
        <a class="navbar-brand d-flex align-items-center" href="/rastreo">
            <?php if(!empty($sistema->logo)): ?>
                <?php if(strpos($sistema->logo, 'data:') === 0): ?>
                    <img src="<?php echo $sistema->logo; ?>" height="40" class="me-2">
                <?php else: ?>
                    <img src="/uploads/logo/<?php echo $sistema->logo; ?>" height="40" class="me-2">
                <?php endif; ?>
            <?php else: ?>
                <i class="fa-solid fa-screwdriver-wrench fa-lg text-primary me-2"></i>
            <?php endif; ?>
            <span class="fw-bold"><?php echo $sistema->nombre_sistema; ?></span>
        </a>
    </div>
</nav>

<div class="container search-container">
    
    <div class="card shadow border-0 mb-5">
        <div class="card-body p-5 text-center">
            <h3 class="mb-3">¿Cómo va mi reparación?</h3>
            <p class="text-muted mb-4">Ingresa tu número de ticket para ver el estado en tiempo real.</p>
            
            <form action="/rastreo" method="GET" class="d-flex justify-content-center">
                <div class="input-group input-group-lg" style="max-width: 400px;">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-ticket"></i></span>
                    <input type="text" name="ticket" class="form-control" placeholder="Ej: 0001" required value="<?php echo $_GET['ticket'] ?? ''; ?>">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </form>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger mt-4"><i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(isset($resultado) && $resultado): ?>
        <div class="card shadow border-0 border-top border-4 border-primary">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Orden #<?php echo str_pad($resultado->id, 4, '0', STR_PAD_LEFT); ?></h4>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                        Fecha: <?php echo date('d/m/Y', strtotime($resultado->fecha_recepcion)); ?>
                    </span>
                </div>

                <?php 
                    // Definimos lógica de pasos
                    $estado = $resultado->estado;
                    $step1 = $step2 = $step3 = $step4 = '';
                    
                    if($estado == 'pendiente') { $step1 = 'active'; }
                    if($estado == 'diagnostico') { $step1 = 'active'; $step2 = 'active'; }
                    if($estado == 'reparado') { $step1 = 'active'; $step2 = 'active'; $step3 = 'active'; }
                    if($estado == 'entregado') { $step1 = 'active'; $step2 = 'active'; $step3 = 'active'; $step4 = 'active'; }
                ?>

                <div class="timeline-steps mt-5">
                    <div class="step <?php echo $step1; ?>">
                        <div class="icon"><i class="fa-solid fa-box-open"></i></div>
                        <div>Recibido</div>
                    </div>
                    <div class="step <?php echo $step2; ?>">
                        <div class="icon"><i class="fa-solid fa-stethoscope"></i></div>
                        <div>Diagnóstico</div>
                    </div>
                    <div class="step <?php echo $step3; ?>">
                        <div class="icon"><i class="fa-solid fa-wrench"></i></div>
                        <div>Reparado</div>
                    </div>
                    <div class="step <?php echo $step4; ?>">
                        <div class="icon"><i class="fa-solid fa-check-double"></i></div>
                        <div>Entregado</div>
                    </div>
                </div>
                <div class="step-connector"></div>

                <div class="row bg-light rounded p-3 mt-4 mx-1">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Equipo</small>
                        <strong><?php echo $resultado->equipo_tipo . ' ' . $resultado->equipo_marca; ?></strong>
                        <div class="small text-muted"><?php echo $resultado->equipo_modelo; ?></div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Cliente</small>
                        <strong><?php echo $resultado->cliente_nombre; ?></strong>
                    </div>
                    <div class="col-12 mt-2">
                        <small class="text-muted d-block">Estado Actual</small>
                        <?php if($estado == 'reparado'): ?>
                            <div class="alert alert-success py-2 mt-1 mb-0">
                                <i class="fa-solid fa-check-circle me-2"></i> <strong>¡Tu equipo está listo!</strong> Puedes pasar a recogerlo.
                            </div>
                        <?php else: ?>
                            <span class="fw-bold text-uppercase text-primary"><?php echo $estado; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="/rastreo" class="btn btn-outline-secondary btn-sm">Nueva Consulta</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5 text-muted small">
        &copy; <?php echo date('Y'); ?> <?php echo $sistema->nombre_sistema; ?> | <a href="/login" class="text-decoration-none text-muted">Acceso Interno</a>
    </div>

</div>

</body>
</html>