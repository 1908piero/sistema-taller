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
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('vehiculos', $id, 'crear', null, $data);
                header('Location: /vehiculos?msg=guardado');
                exit;
            } else {
                header('Location: /vehiculos?msg=error');
                exit;
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $vehiculoModel = new Vehiculo();
            $anterior = $vehiculoModel->getById($id);

            $data = [
                'id' => $id,
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

            if ($vehiculoModel->update($data)) {
                $this->registrarAuditoria('vehiculos', $id, 'actualizar', $anterior, $data);
                header('Location: /vehiculos?msg=actualizado');
                exit;
            } else {
                header('Location: /vehiculos?msg=error');
                exit;
            }
        }
    }

    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $nuevoEstado = $_POST['nuevo_estado'];
            
            $vehiculoModel = new Vehiculo();
            $vehiculoModel->updateStatus($id, $nuevoEstado);
            $this->registrarAuditoria('vehiculos', $id, 'cambiar_estado', null, ['estado' => $nuevoEstado]);
            
            if (isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                header('Location: /vehiculos');
                exit;
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

    // RF-11: Historial de servicios por vehículo
    public function historial() {
        $search = $_GET['search'] ?? '';
        $resultados = [];

        if ($search) {
            $vehiculoModel = new Vehiculo();
            $resultados = $vehiculoModel->searchByPlaca($search);
            // Para cada resultado, cargar ordenes
            foreach ($resultados as &$v) {
                $v->ordenes = $vehiculoModel->getHistorialOrdenes($v->id);
            }
        }

        $this->view('vehiculos/historial', [
            'titulo' => 'Historial de Vehículos',
            'search' => $search,
            'resultados' => $resultados
        ]);
    }
}
