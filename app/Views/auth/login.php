<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Taller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header i { font-size: 50px; color: #3498db; margin-bottom: 10px; }
        .form-control { border-radius: 20px; padding: 10px 20px; }
        .btn-login { border-radius: 20px; padding: 10px; font-weight: bold; width: 100%; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="fa-solid fa-screwdriver-wrench"></i>
        <h3>Sistema Taller</h3>
        <p class="text-muted">Ingresa tus credenciales</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <?php if($_GET['error'] === 'bloqueado'): ?>
            <div class="alert alert-danger text-center py-2">
                <small><i class="fa-solid fa-lock"></i> <strong>MSJ-31:</strong> Cuenta temporalmente bloqueada. Demasiados intentos fallidos. Espere 15 minutos.</small>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center py-2">
                <small><i class="fa-solid fa-triangle-exclamation"></i> <strong>MSJ-30:</strong> Usuario o contraseña incorrectos</small>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form action="/login/auth" method="POST">
        <div class="mb-3">
            <label class="form-label ms-2">Correo Electrónico</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="fa-solid fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control" placeholder="admin@taller.com" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label ms-2">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="fa-solid fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-login">INGRESAR AL SISTEMA</button>
        </div>
    </form>
    
    <div class="text-center mt-4 text-muted" style="font-size: 0.8em;">
        &copy; <?php echo date('Y'); ?> Taller Pro v1.0
    </div>
</div>

</body>
</html>
