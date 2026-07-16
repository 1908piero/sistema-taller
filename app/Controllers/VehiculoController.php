<?php
namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\Cliente;
use App\Models\Orden;

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

    private function validarDatosVehiculo($data) {
        // Placa: exactamente 7 caracteres alfanuméricos
        if (!preg_match('/^[A-Za-z0-9\-]{7}$/', $data['placa'])) { return 'placa_invalida'; }
        // Marca: alfabético, máximo 50
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{1,50}$/', $data['marca'])) { return 'marca_invalida'; }
        // Modelo: alfanumérico, máximo 50
        if (!preg_match('/^[A-Za-z0-9áéíóúÁÉÍÓÚñÑ\s\-]{1,50}$/', $data['modelo'])) { return 'modelo_invalido'; }
        // Año: 4 dígitos entre 1900 y 2030
        if (!empty($data['año']) && (!preg_match('/^\d{4}$/', $data['año']) || $data['año'] < 1900 || $data['año'] > 2030)) { return 'anio_invalido'; }
        return null;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $vehiculoModel = new Vehiculo();

            // RN-02: Validar que el cliente exista
            $clienteModel = new \App\Models\Cliente();
            $cliente = $clienteModel->getById($_POST['cliente_id']);
            if (!$cliente) {
                header('Location: /vehiculos?msg=cliente_invalido');
                exit;
            }

            $placa = strtoupper(trim($_POST['placa']));

            // CU-02: Validar formato de campos
            $error = $this->validarDatosVehiculo($_POST);
            if ($error) {
                header("Location: /vehiculos?msg=$error");
                exit;
            }

            // RN-02: Validar placa única
            if ($vehiculoModel->getByPlaca($placa)) {
                header('Location: /vehiculos?msg=placa_duplicada');
                exit;
            }

            $data = [
                'cliente_id' => $_POST['cliente_id'],
                'placa' => $placa,
                'marca' => $_POST['marca'],
                'modelo' => $_POST['modelo'],
                'año' => $_POST['año'] ?? null,
                'color' => $_POST['color'] ?? null,
                'vin' => $_POST['vin'] ?? null,
                'tipo_motor' => $_POST['tipo_motor'] ?? null,
                'observaciones' => $_POST['observaciones'] ?? null,
            ];

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

            // RN-02: Validar que el cliente exista
            $clienteModel = new \App\Models\Cliente();
            $cliente = $clienteModel->getById($_POST['cliente_id']);
            if (!$cliente) {
                header('Location: /vehiculos?msg=cliente_invalido');
                exit;
            }

            $placa = strtoupper(trim($_POST['placa']));

            // CU-02: Validar formato de campos
            $error = $this->validarDatosVehiculo($_POST);
            if ($error) {
                header("Location: /vehiculos?msg=$error");
                exit;
            }

            // RN-02: Validar placa única (excluyendo este registro)
            if ($vehiculoModel->getByPlaca($placa, $id)) {
                header('Location: /vehiculos?msg=placa_duplicada');
                exit;
            }

            $anterior = $vehiculoModel->getById($id);

            $data = [
                'id' => $id,
                'cliente_id' => $_POST['cliente_id'],
                'placa' => $placa,
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
        try {
            $search = $_GET['search'] ?? '';
            $resultados = [];

            if ($search) {
                $vehiculoModel = new Vehiculo();
                $resultados = $vehiculoModel->searchByPlaca($search);
                $ordenModel = new Orden();
                foreach ($resultados as &$v) {
                    $v->ordenes = $vehiculoModel->getHistorialOrdenes($v->id);
                    // RF-11: Cargar servicios y repuestos por cada orden
                    foreach ($v->ordenes as &$o) {
                        $o->servicios = $ordenModel->getServicios($o->id);
                        $o->repuestos = $ordenModel->getRepuestos($o->id);
                    }
                }
            }

            $this->view('vehiculos/historial', [
                'titulo' => 'Historial de Vehículos',
                'search' => $search,
                'resultados' => $resultados
            ]);
        } catch (\Exception $e) {
            header('Location: /vehiculos/historial?msg=error');
            exit;
        }
    }
}
