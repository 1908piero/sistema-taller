<?php
// public/index.php

// 1. BUFFER Y SESIÓN (CRÍTICO: Deben ser las primeras líneas)
ob_start();     // Inicia el buffer de salida (Evita errores de headers)
session_start(); // Inicia la sesión de usuario

// --- MODO DEBUG (Para ver errores si los hay) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Cargar Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Importar Clases
use App\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ClienteController;
use App\Controllers\ProductoController;
use App\Controllers\OrdenController;
use App\Controllers\VentaController;
use App\Controllers\GastoController;
use App\Controllers\ReporteController;
use App\Controllers\UsuarioController;
use App\Controllers\ConfiguracionController;
use App\Controllers\RastreoController;
use App\Controllers\VehiculoController;
use App\Controllers\PagoController;

$router = new Router();

// =============================================================
// RUTAS PÚBLICAS
// =============================================================
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login/auth', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/rastreo', [RastreoController::class, 'index']);

// =============================================================
// RUTAS PRIVADAS
// =============================================================

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Clientes
$router->get('/clientes', [ClienteController::class, 'index']);
$router->get('/clientes/perfil', [ClienteController::class, 'perfil']);
$router->post('/clientes/guardar', [ClienteController::class, 'store']);
$router->post('/clientes/actualizar', [ClienteController::class, 'update']);
$router->post('/clientes/cambiar-estado', [ClienteController::class, 'cambiarEstado']);

// Productos
$router->get('/productos', [ProductoController::class, 'index']);
$router->get('/productos/historial', [ProductoController::class, 'historial']);
$router->post('/productos/guardar', [ProductoController::class, 'store']);
$router->post('/productos/actualizar', [ProductoController::class, 'update']);
$router->post('/productos/cambiar-estado', [ProductoController::class, 'cambiarEstado']);
$router->post('/productos/ajustar', [ProductoController::class, 'ajustar']);

// Órdenes
$router->get('/ordenes', [OrdenController::class, 'index']);
$router->get('/ordenes/detalle', [OrdenController::class, 'detalle']);
$router->get('/ordenes/imprimir', [OrdenController::class, 'imprimir']);
$router->get('/ordenes/etiqueta', [OrdenController::class, 'etiqueta']);
$router->get('/ordenes/garantia', [OrdenController::class, 'garantia']);
$router->post('/ordenes/guardar', [OrdenController::class, 'store']);
$router->post('/ordenes/cambiar-estado', [OrdenController::class, 'cambiarEstado']);
$router->post('/ordenes/agregar-repuesto', [OrdenController::class, 'agregarRepuesto']);
$router->post('/ordenes/eliminar-repuesto', [OrdenController::class, 'eliminarRepuesto']);
$router->post('/ordenes/mano-obra', [OrdenController::class, 'actualizarManoObra']);
$router->post('/ordenes/diagnostico', [OrdenController::class, 'guardarDiagnostico']);

// Ventas
$router->get('/ventas', [VentaController::class, 'index']);
$router->get('/ventas/crear', [VentaController::class, 'crear']);
$router->get('/ventas/imprimir', [VentaController::class, 'imprimir']);
$router->post('/ventas/guardar', [VentaController::class, 'store']);

// Gastos
$router->get('/gastos', [GastoController::class, 'index']);
$router->post('/gastos/guardar', [GastoController::class, 'store']);
$router->post('/gastos/eliminar', [GastoController::class, 'eliminar']);

// Reportes
$router->get('/reportes', [ReporteController::class, 'index']);

// Usuarios
$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->post('/usuarios/guardar', [UsuarioController::class, 'store']);
$router->post('/usuarios/actualizar', [UsuarioController::class, 'update']);
$router->post('/usuarios/cambiar-estado', [UsuarioController::class, 'cambiarEstado']);

// Configuración
$router->get('/configuracion', [ConfiguracionController::class, 'index']);
$router->post('/configuracion/guardar', [ConfiguracionController::class, 'update']);
$router->get('/configuracion/backup', [ConfiguracionController::class, 'backup']);


// Vehiculos
$router->get('/vehiculos', [VehiculoController::class, 'index']);
$router->get('/vehiculos/perfil', [VehiculoController::class, 'perfil']);
$router->post('/vehiculos/guardar', [VehiculoController::class, 'store']);
$router->post('/vehiculos/actualizar', [VehiculoController::class, 'update']);
$router->post('/vehiculos/cambiar-estado', [VehiculoController::class, 'cambiarEstado']);

// Pagos / Caja
$router->get('/pagos', [PagoController::class, 'index']);
$router->get('/pagos/caja', [PagoController::class, 'caja']);
$router->post('/pagos/guardar', [PagoController::class, 'store']);
$router->post('/pagos/eliminar', [PagoController::class, 'eliminar']);

$router->run();