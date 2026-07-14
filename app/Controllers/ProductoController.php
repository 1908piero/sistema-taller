<?php
namespace App\Controllers;

use App\Models\Producto;

class ProductoController extends BaseController {

    public function index() {
        $prodModel = new Producto();
        $productos = $prodModel->getAll();

        $this->view('productos/index', [
            'productos' => $productos,
            'titulo' => 'Inventario'
        ]);
    }

    // --- CORREGIDO: Ver Historial ---
    public function historial() {
        $id = $_GET['id'] ?? null;
        
        // 1. Validar ID
        if (!$id) { 
            header('Location: /productos'); 
            exit; 
        }

        $prodModel = new Producto();
        $producto = $prodModel->getById($id);

        // 2. Seguridad: Si el producto no existe, volver atrás
        if (!$producto) {
            header('Location: /productos?msg=error_producto');
            exit;
        }

        $movimientos = $prodModel->getKardex($id);

        $this->view('productos/historial', [
            'producto' => $producto,
            'movimientos' => $movimientos,
            'titulo' => 'Kardex: ' . $producto->nombre
        ]);
    }

    public function ajustar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $tipo = $_POST['tipo'];
            $cantidad = intval($_POST['cantidad']);
            $motivo = $_POST['motivo'];
            $usuarioId = $_SESSION['user_id'];

            if ($cantidad <= 0) {
                header('Location: /productos?msg=error_cantidad');
                exit;
            }

            $prodModel = new Producto();
            if ($prodModel->ajustarStock($id, $tipo, $cantidad, $motivo, $usuarioId)) {
                header('Location: /productos?msg=ajuste_ok');
            } else {
                header('Location: /productos?msg=error_stock');
            }
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagen = $this->procesarImagenSubida('imagen', '/../../public/uploads/productos/', 'prod');

            $data = [
                'codigo' => $_POST['codigo'],
                'nombre' => $_POST['nombre'],
                'categoria' => $_POST['categoria'],
                'stock' => $_POST['stock'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'imagen' => $imagen
            ];

            $prodModel = new Producto();
            if ($prodModel->create($data)) {
                header('Location: /productos?msg=guardado');
            } else {
                error_log("[PRODUCTO ERROR] create failed. codigo={$data['codigo']} nombre={$data['nombre']} imagen=" . (is_string($data['imagen']) ? substr($data['imagen'], 0, 50) : 'null'));
                header('Location: /productos?msg=error');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagen = $this->procesarImagenSubida('imagen', '/../../public/uploads/productos/', 'prod');

            $data = [
                'id' => $_POST['id'],
                'codigo' => $_POST['codigo'],
                'nombre' => $_POST['nombre'],
                'categoria' => $_POST['categoria'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'imagen' => $imagen
            ];

            $prodModel = new Producto();
            if ($prodModel->update($data)) {
                header('Location: /productos?msg=actualizado');
            } else {
                error_log("[PRODUCTO ERROR] update failed. id={$data['id']} codigo={$data['codigo']}");
                header('Location: /productos?msg=error');
            }
        }
    }

    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['nuevo_estado'];
            $prodModel = new Producto();
            if ($prodModel->updateStatus($id, $estado)) {
                header('Location: /productos?msg=estado_cambiado');
            }
        }
    }
}