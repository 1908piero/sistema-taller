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

    public function perfil() {
        $id = $_GET['id'] ?? null;
        if (!$id) { header('Location: /clientes'); exit; }

        $clienteModel = new Cliente();
        $cliente = $clienteModel->getById($id);

        if (!$cliente) { header('Location: /clientes'); exit; }

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

    private function validarDatosCliente($data) {
        // CU-01: Validar campos requeridos
        if (empty(trim($data['nombre'] ?? ''))) { return 'nombre_requerido'; }
        if (empty(trim($data['codigo'] ?? ''))) { return 'codigo_requerido'; }
        if (empty(trim($data['dni'] ?? ''))) { return 'dni_requerido'; }
        if (empty(trim($data['telefono'] ?? ''))) { return 'telefono_requerido'; }

        // Validar nombre: solo letras y espacios
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $data['nombre'])) { return 'nombre_invalido'; }

        // CU-01: Validar código: alfanumérico, exactamente 10 caracteres
        if (!preg_match('/^[A-Za-z0-9]{10}$/', $data['codigo'])) { return 'codigo_invalido'; }

        // Validar DNI: exactamente 8 dígitos numéricos
        if (!preg_match('/^\d{8}$/', $data['dni'])) { return 'dni_invalido'; }

        // Validar teléfono: numérico, hasta 15 dígitos
        if (!preg_match('/^\d{7,15}$/', $data['telefono'])) { return 'telefono_invalido'; }

        return null;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dni = $_POST['dni'] ?? '';
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $clienteModel = new Cliente();

            // CU-01: Validar datos de entrada
            $error = $this->validarDatosCliente($_POST);
            if ($error) {
                header("Location: /clientes?msg=$error");
                exit;
            }

            // CU-01: Validar código único
            if ($clienteModel->getByCodigo($codigo)) {
                header('Location: /clientes?msg=codigo_duplicado');
                exit;
            }

            // RN-01: Validar DNI duplicado
            if (!empty($dni) && $clienteModel->getByDni($dni)) {
                header('Location: /clientes?msg=dni_duplicado');
                exit;
            }

            $data = [
                'codigo' => $codigo,
                'nombre' => $_POST['nombre'],
                'dni' => $dni,
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion']
            ];

            if ($clienteModel->create($data)) {
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('clientes', $id, 'crear', null, $data);
                header('Location: /clientes?msg=guardado');
            } else {
                header('Location: /clientes?msg=error');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $dni = $_POST['dni'] ?? '';
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $clienteModel = new Cliente();
            $anterior = $clienteModel->getById($id);

            // CU-01: Validar datos de entrada
            $error = $this->validarDatosCliente($_POST);
            if ($error) {
                header("Location: /clientes?msg=$error");
                exit;
            }

            // CU-01: Validar código único (excluyendo este registro)
            if ($clienteModel->getByCodigo($codigo, $id)) {
                header('Location: /clientes?msg=codigo_duplicado');
                exit;
            }

            // RN-01: Validar DNI duplicado (excluyendo este registro)
            if (!empty($dni) && $clienteModel->getByDni($dni, $id)) {
                header('Location: /clientes?msg=dni_duplicado');
                exit;
            }

            $data = [
                'id' => $id,
                'codigo' => $codigo,
                'nombre' => $_POST['nombre'],
                'dni' => $dni,
                'telefono' => $_POST['telefono'],
                'email' => $_POST['email'],
                'direccion' => $_POST['direccion']
            ];

            if ($clienteModel->update($data)) {
                $this->registrarAuditoria('clientes', $id, 'actualizar', $anterior, $data);
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
            $this->registrarAuditoria('clientes', $id, 'cambiar_estado', null, ['estado' => $nuevoEstado]);
            
            if(isset($_SERVER['HTTP_REFERER'])) {
                header("Location: " . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: /clientes');
            }
        }
    }
}
