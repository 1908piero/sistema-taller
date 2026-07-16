<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $sistema->nombre_sistema; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #2c3e50; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        
        .card { border: none; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .card-header { background: white; border-bottom: 1px solid #eee; padding: 15px; font-weight: bold; }
        
        /* Ajustes DataTables */
        .dt-buttons .btn { font-size: 0.8rem; padding: 0.25rem 0.5rem; }
        
        /* Ajustes Select2 */
        .select2-container--bootstrap-5 .select2-selection { border-color: #dee2e6; }

        /* RNF-05: Control de tamaño de fuente */
        .font-size-sm { font-size: 0.85rem; }
        .font-size-md { font-size: 1rem; }
        .font-size-lg { font-size: 1.15rem; }
        .font-size-controls { font-size: 0.8rem; }
        .font-size-controls .btn { padding: 0.15rem 0.4rem; font-size: 0.7rem; line-height: 1; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var size = localStorage.getItem('fontSize') || 'md';
            document.body.className = 'font-size-' + size;
        });
        function cambiarFontSize(t) {
            document.body.className = 'font-size-' + t;
            localStorage.setItem('fontSize', t);
        }
    </script>
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <?php if(!empty($sistema->logo)): ?>
                <?php if(strpos($sistema->logo, 'data:') === 0): ?>
                    <img src="<?php echo $sistema->logo; ?>" height="30" class="me-2" alt="Logo del taller">
                <?php elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/uploads/logo/' . $sistema->logo)): ?>
                    <img src="/uploads/logo/<?php echo $sistema->logo; ?>" height="30" class="me-2" alt="Logo del taller">
                <?php else: ?>
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                <?php endif; ?>
            <?php else: ?>
                <i class="fa-solid fa-screwdriver-wrench me-2"></i>
            <?php endif; ?>
            <span class="fs-5 fw-bold text-truncate"><?php echo substr($sistema->nombre_sistema, 0, 18); ?></span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="/" class="<?php echo ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '') ? 'active' : ''; ?>"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
            <li><a href="/ordenes" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/ordenes') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-list me-2"></i> Órdenes</a></li>
            <li><a href="/clientes" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/clientes') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-users me-2"></i> Clientes</a></li>
            <li><a href="/vehiculos" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/vehiculos') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-car me-2"></i> Vehículos</a></li>
            <li><a href="/vehiculos/historial" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/vehiculos/historial') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-clock-rotate-left me-2"></i> Historial Veh.</a></li>
            <li><a href="/servicios" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/servicios') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-list-check me-2"></i> Servicios</a></li>
            <li><a href="/productos" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/productos') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-box-open me-2"></i> Inventario</a></li>
            <li><a href="/ventas" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/ventas') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-cart-shopping me-2"></i> Ventas</a></li>
            
            <?php if(isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['Admin', 'Jefe'])): ?>
                <hr class="text-secondary">
                <div class="small text-muted text-uppercase mb-1 ms-2" style="font-size:0.7em;">Administración</div>
                <li><a href="/gastos" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/gastos') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-money-bill-wave me-2"></i> Gastos</a></li>
                <li><a href="/pagos" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/pagos') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-cash-register me-2"></i> Caja y Pagos</a></li>
                <li><a href="/reportes" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/reportes') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie me-2"></i> Reportes</a></li>
                <li><a href="/usuarios" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/usuarios') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-user-shield me-2"></i> Personal</a></li>
                <li><a href="/configuracion" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/configuracion') !== false) ? 'active' : ''; ?>"><i class="fa-solid fa-gear me-2"></i> Configuración</a></li>
            <?php endif; ?>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'Admin'); ?>&background=random" width="32" height="32" class="rounded-circle me-2" alt="Avatar de usuario">
                <strong><?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item disabled" href="#">Rol: <?php echo ucfirst($_SESSION['user_role'] ?? ''); ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/logout"><i class="fa-solid fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
        <div class="font-size-controls text-center mt-2">
            <span class="text-muted small">Tamaño: </span>
            <button class="btn btn-outline-light btn-sm" onclick="cambiarFontSize('sm')" title="Pequeño">A-</button>
            <button class="btn btn-outline-light btn-sm" onclick="cambiarFontSize('md')" title="Mediano">A</button>
            <button class="btn btn-outline-light btn-sm" onclick="cambiarFontSize('lg')" title="Grande">A+</button>
        </div>
    </div>

    <div class="container-fluid p-4" style="width: 100%;">