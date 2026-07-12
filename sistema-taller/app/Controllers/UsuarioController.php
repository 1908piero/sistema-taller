<?php
namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController extends BaseController {

    // Método para verificar que sea Admin
    private function verificarPermiso() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            // Si no es admin, lo mandamos al dashboard con error
            header('Location: /?msg=no_autorizado');
            exit;
        }
    }

    public function index() {
        $this->verificarPermiso(); // Seguridad primero

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
            // Validar que el email no exista sería ideal, pero por brevedad vamos directo
            if ($userModel->create($data)) {
                header('Location: /usuarios?msg=guardado');
            } else {
                header('Location: /usuarios?msg=error');
            }
        }
    }

    public function update() {
        $this->verificarPermiso();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id' => $_POST['id'],
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'], // Si está vacío, el modelo lo ignora
                'rol' => $_POST['rol']
            ];

            $userModel = new Usuario();
            if ($userModel->update($data)) {
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
            
            // Evitar desactivarse a uno mismo
            if ($id == $_SESSION['user_id']) {
                header('Location: /usuarios?msg=error_propio');
                exit;
            }

            $userModel = new Usuario();
            if ($userModel->updateStatus($id, $estado)) {
                header('Location: /usuarios?msg=estado_cambiado');
            } else {
                header('Location: /usuarios?msg=error');
            }
        }
    }
}