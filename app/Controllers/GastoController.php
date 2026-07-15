<?php
namespace App\Controllers;

use App\Models\Gasto;

class GastoController extends BaseController {

    public function index() {
        $gastoModel = new Gasto();
        $gastos = $gastoModel->getAll();

        $this->view('gastos/index', [
            'titulo' => 'Control de Gastos',
            'gastos' => $gastos
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'descripcion' => $_POST['descripcion'],
                'categoria' => $_POST['categoria'],
                'monto' => $_POST['monto']
            ];

            $gastoModel = new Gasto();
            if ($gastoModel->create($data)) {
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('gastos', $id, 'crear', null, $data);
                header('Location: /gastos?msg=guardado');
            } else {
                header('Location: /gastos?msg=error');
            }
        }
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $gastoModel = new Gasto();
            if ($gastoModel->delete($id)) {
                $this->registrarAuditoria('gastos', $id, 'eliminar', null, null);
                header('Location: /gastos?msg=eliminado');
            } else {
                header('Location: /gastos?msg=error');
            }
        }
    }
}
