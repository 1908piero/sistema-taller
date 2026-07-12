<?php
namespace App\Controllers;

use App\Models\Usuario;

class AuthController extends BaseController {

    // Mostrar el formulario de Login
    public function login() {
        // Si ya está logueado, mandar al dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        
        // Cargamos la vista de login (sin usar el layout principal porque es una pantalla distinta)
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    // Procesar el formulario
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userModel = new Usuario();
            $usuario = $userModel->getByEmail($email);

            if ($usuario) {
                // Verificamos contraseña
                // Nota: password_verify es la forma segura. 
                // Si tu clave en BD es texto plano '123456', esto fallará la primera vez. 
                // Para arreglarlo rápido: si la clave coincide en texto plano, la actualizamos a Hash.
                
                $check = false;
                if (password_verify($password, $usuario->password)) {
                    $check = true;
                } elseif ($password === $usuario->password) {
                    // PARCHE DE SEGURIDAD: Si la clave no estaba encriptada pero coincide, la encriptamos ahora.
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$newHash, $usuario->id]);
                    $check = true;
                }

                if ($check) {
                    // LOGIN EXITOSO
                    $_SESSION['user_id'] = $usuario->id;
                    $_SESSION['user_name'] = $usuario->nombre;
                    $_SESSION['user_role'] = $usuario->rol;
                    
                    header('Location: /');
                    exit;
                }
            }

            // Si falla
            header('Location: /login?error=credenciales');
            exit;
        }
    }

    // Cerrar Sesión
    public function logout() {
        session_destroy();
        header('Location: /login');
        exit;
    }
}