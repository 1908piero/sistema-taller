<?php
namespace App\Controllers;

use App\Models\Gasto;

class GastoController extends BaseController {

    public function index() {
        // Verificar permiso (Solo Admin debería ver finanzas, pero dejaremos a todos por ahora)
        // if ($_SESSION['user_role'] !== 'admin') { header('Location: /'); exit; }

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
                header('Location: /gastos?msg=eliminado');
            } else {
                header('Location: /gastos?msg=error');
            }
        }
    }
}