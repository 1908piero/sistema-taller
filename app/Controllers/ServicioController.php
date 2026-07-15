<?php
namespace App\Controllers;

use App\Models\Servicio;

class ServicioController extends BaseController {

    public function index() {
        $servicioModel = new Servicio();
        $servicios = $servicioModel->getAll();

        $this->view('servicios/index', [
            'titulo' => 'Catálogo de Servicios',
            'servicios' => $servicios
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'precio' => $_POST['precio'],
                'categoria' => $_POST['categoria'] ?? 'general',
                'duracion_estimada' => $_POST['duracion_estimada'] ?? null,
            ];

            $servicioModel = new Servicio();
            if ($servicioModel->create($data)) {
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('servicios', $id, 'crear', null, $data);
                header('Location: /servicios?msg=guardado');
            } else {
                header('Location: /servicios?msg=error');
            }
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $servicioModel = new Servicio();
            $anterior = $servicioModel->getById($id);

            $data = [
                'id' => $id,
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'precio' => $_POST['precio'],
                'categoria' => $_POST['categoria'] ?? 'general',
                'duracion_estimada' => $_POST['duracion_estimada'] ?? null,
            ];

            if ($servicioModel->update($data)) {
                $this->registrarAuditoria('servicios', $id, 'actualizar', $anterior, $data);
                header('Location: /servicios?msg=actualizado');
            } else {
                header('Location: /servicios?msg=error');
            }
        }
    }

    public function cambiarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['nuevo_estado'];
            $servicioModel = new Servicio();
            $servicioModel->updateStatus($id, $estado);
            $this->registrarAuditoria('servicios', $id, 'cambiar_estado', null, ['estado' => $estado]);
            header('Location: /servicios?msg=estado_cambiado');
        }
    }
}
