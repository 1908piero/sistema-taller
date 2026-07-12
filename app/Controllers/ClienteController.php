<?php
namespace App\Controllers;

use App\Models\Cliente;

class ClienteController extends BaseController {

    public function index() {
        $clienteModel = new Cliente();
        $clientes = $clienteModel->getAll();

        $this->view('clientes/index', [
            'titulo' => 'Gestión de Clientes',
            'clientes' => $clientes
        ]);
    }

    // --- NUEVO: Ver Perfil 360 del Cliente ---
    public function perfil() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /clientes'); exit; }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($id);

        if (!$cliente) { header('Location: /clientes'); exit; }

        // Obtenemos toda la data relacionada
        $ordenes = $clienteModel->getOrdenes($id);
        $ventas = $clienteModel->getVentas($id);
        $stats = $clienteModel->getEstadisticas($id);

        $this->view('clientes/perfil', [
            'titulo' => 'Perfil: ' . $cliente->nombre,
            'cliente' => $cliente,
            'ordenes' => $ordenes,
            'ventas' => $ventas,
            'stats' => $stats
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion']
            ];

            $clienteModel = new Cliente();
            if ($clienteModel->create($data)) {
                header('Location: /clientes?msg=guardado');
            } else {
                header('Location: /clientes?msg=error');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nombre' => $_POST['nombre'],
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion']
            ];

            $clienteModel = new Cliente();
            if ($clienteModel->update($data)) {
                header('Location: /clientes?msg=actualizado');
            } else {
                header('Location: /clientes?msg=error');
            }
        }
    }

    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $nuevoEstado = $_POST['nuevo_estado'];
            
            $clienteModel = new Cliente();
            $clienteModel->updateStatus($id, $nuevoEstado);
            
            // Volver a la página anterior (útil si estamos en el perfil)
            if(isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /clientes');
            }
        }
    }
}