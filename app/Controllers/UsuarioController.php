<?php
namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController extends BaseController {

    private function verificarPermiso() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /?msg=no_autorizado');
            exit;
        }
    }

    public function index() {
        $this->verificarPermiso();

        $userModel = new Usuario();
        $usuarios = $userModel->getAll();

        $this->view('usuarios/index', [
            'titulo' => 'Gestión de Personal',
            'usuarios' => $usuarios
        ]);
    }

    public function store() {
        $this->verificarPermiso();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol']
            ];

            $userModel = new Usuario();
            if ($userModel->create($data)) {
                $id = $this->db->lastInsertId();
                $this->registrarAuditoria('usuarios', $id, 'crear', null, ['nombre' => $data['nombre'], 'email' => $data['email'], 'rol' => $data['rol']]);
                header('Location: /usuarios?msg=guardado');
            } else {
                header('Location: /usuarios?msg=error');
            }
        }
    }

    public function update() {
        $this->verificarPermiso();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $userModel = new Usuario();
            $anterior = $userModel->getById($id);

            $data = [
                'id' => $id,
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol']
            ];

            if ($userModel->update($data)) {
                $this->registrarAuditoria('usuarios', $id, 'actualizar', $anterior, ['nombre' => $data['nombre'], 'email' => $data['email'], 'rol' => $data['rol']]);
                header('Location: /usuarios?msg=actualizado');
            } else {
                header('Location: /usuarios?msg=error');
            }
        }
    }

    public function cambiarEstado() {
        $this->verificarPermiso();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $estado = $_POST['nuevo_estado'];
            
            if ($id == $_SESSION['user_id']) {
                header('Location: /usuarios?msg=error_propio');
                exit;
            }

            $userModel = new Usuario();
            if ($userModel->updateStatus($id, $estado)) {
                $this->registrarAuditoria('usuarios', $id, 'cambiar_estado', null, ['estado' => $estado]);
                header('Location: /usuarios?msg=estado_cambiado');
            } else {
                header('Location: /usuarios?msg=error');
            }
        }
    }
}
