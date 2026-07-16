<?php
namespace App\Controllers;

use App\Models\Usuario;

class UsuarioController extends BaseController {

    private $rolesPermitidos = ['Jefe', 'Admin', 'Recepcionista', 'Técnico'];

    private function verificarPermiso() {
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['Admin', 'Jefe'])) {
            header('Location: /?msg=no_autorizado');
            exit;
        }
    }

    // RF-13: Validar que el rol sea uno de los permitidos
    private function validarRol($rol) {
        if (!in_array($rol, $this->rolesPermitidos)) {
            header('Location: /usuarios?msg=rol_invalido');
            exit;
        }
    }

    // CU-07: Validar nombre solo letras
    private function validarDatosUsuario($data) {
        if (empty(trim($data['nombre'] ?? ''))) { return 'nombre_requerido'; }
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{1,100}$/', $data['nombre'])) { return 'nombre_invalido'; }
        return null;
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
            // CU-07: Validar datos de entrada
            $error = $this->validarDatosUsuario($_POST);
            if ($error) { header("Location: /usuarios?msg=$error"); exit; }

            // RF-13: Validar rol permitido
            $this->validarRol($_POST['rol']);

            $data = [
                'nombre' => $_POST['nombre'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'rol' => $_POST['rol']
            ];

            $userModel = new Usuario();

            // RF-12: Validar email único
            if ($userModel->emailExiste($data['email'])) {
                header('Location: /usuarios?msg=email_duplicado');
                exit;
            }

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
            // CU-07: Validar datos de entrada
            $error = $this->validarDatosUsuario($_POST);
            if ($error) { header("Location: /usuarios?msg=$error"); exit; }

            // RF-13: Validar rol permitido
            $this->validarRol($_POST['rol']);

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

            // RF-12: Validar email único (excluyendo este usuario)
            if ($userModel->emailExiste($data['email'], $id)) {
                header('Location: /usuarios?msg=email_duplicado');
                exit;
            }

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
