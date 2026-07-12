<?php
namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\Cliente;

class VehiculoController extends BaseController {

    public function index() {
        $vehiculoModel = new Vehiculo();
        $clienteModel = new Cliente();
        
        $search = $_GET['search'] ?? '';
        if ($search) {
            $vehiculos = $vehiculoModel->searchByPlaca($search);
        } else {
            $vehiculos = $vehiculoModel->getAll();
        }
        
        $clientes = $clienteModel->getAll();

        $this->view('vehiculos/index', [
            'titulo' => 'Gestión de Vehículos',
            'vehiculos' => $vehiculos,
            'clientes' => $clientes,
            'search' => $search
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente_id' => $_POST['cliente_id'],
                'placa' => $_POST['placa'],
                'marca' => $_POST['marca'],
                'modelo' => $_POST['modelo'],
                'año' => $_POST['año'] ?? null,
                'color' => $_POST['color'] ?? null,
                'vin' => $_POST['vin'] ?? null,
                'tipo_motor' => $_POST['tipo_motor'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
            ];

            $vehiculoModel = new Vehiculo();
            if ($vehiculoModel->create($data)) {
                header('Location: /vehiculos?msg=guardado');
            } else {
                header('Location: /vehiculos?msg=error');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'cliente_id' => $_POST['cliente_id'],
                'placa' => $_POST['placa'],
                'marca' => $_POST['marca'],
                'modelo' => $_POST['modelo'],
                'año' => $_POST['año'] ?? null,
                'color' => $_POST['color'] ?? null,
                'vin' => $_POST['vin'] ?? null,
                'tipo_motor' => $_POST['tipo_motor'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
            ];

            $vehiculoModel = new Vehiculo();
            if ($vehiculoModel->update($data)) {
                header('Location: /vehiculos?msg=actualizado');
            } else {
                header('Location: /vehiculos?msg=error');
            }
        }
    }

    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $nuevoEstado = $_POST['nuevo_estado'];
            
            $vehiculoModel = new Vehiculo();
            $vehiculoModel->updateStatus($id, $nuevoEstado);
            
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /vehiculos');
            }
        }
    }

    public function perfil() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /vehiculos'); exit; }

        $vehiculoModel = new Vehiculo();
        $vehiculo = $vehiculoModel->getById($id);

        if (!$vehiculo) { header('Location: /vehiculos'); exit; }

        $ordenes = $vehiculoModel->getHistorialOrdenes($id);

        $this->view('vehiculos/perfil', [
            'titulo' => 'Vehículo: ' . $vehiculo->placa,
            'vehiculo' => $vehiculo,
            'ordenes' => $ordenes
        ]);
    }
}
