<?php
namespace App\Controllers;

use App\Models\Usuario;

class AuthController extends BaseController {

    private $maxIntentos = 3;
    private $tiempoBloqueo = 15; // minutos

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

            // RNF-02: Verificar bloqueo
            $bloqueado = $this->estaBloqueado($email, $ip);
            if ($bloqueado) {
                header('Location: /login?error=bloqueado');
                exit;
            }

            $userModel = new Usuario();
            $usuario = $userModel->getByEmail($email);

            if ($usuario) {
                $check = false;
                if (password_verify($password, $usuario->password)) {
                    $check = true;
                } elseif ($password === $usuario->password) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$newHash, $usuario->id]);
                    $check = true;
                }

                if ($check) {
                    // RNF-02: Limpiar intentos fallidos al loguear éxito
                    $this->limpiarIntentos($email);

                    $_SESSION['user_id'] = $usuario->id;
                    $_SESSION['user_name'] = $usuario->nombre;
                    $_SESSION['user_role'] = $usuario->rol;

                    // RN-08: Auditoría de login exitoso
                    $this->registrarAuditoria('usuarios', $usuario->id, 'login', null, "Inicio de sesión exitoso - IP: $ip");

                    header('Location: /');
                    exit;
                }
            }

            // RNF-02: Registrar intento fallido
            $this->registrarIntento($email, $ip);

            header('Location: /login?error=credenciales');
            exit;
        }
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->registrarAuditoria('usuarios', $_SESSION['user_id'], 'logout', null, "Cierre de sesión - IP: " . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'));
        }
        session_destroy();
        header('Location: /login');
        exit;
    }

    private function estaBloqueado($email, $ip) {
        try {
            $desde = date('Y-m-d H:i:s', strtotime("-{$this->tiempoBloqueo} minutes"));
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM login_attempts 
                                         WHERE (email = :email OR ip_address = :ip) 
                                         AND attempted_at >= :desde");
            $stmt->execute([':email' => $email, ':ip' => $ip, ':desde' => $desde]);
            $total = $stmt->fetch(\PDO::FETCH_OBJ)->total;
            return $total >= $this->maxIntentos;
        } catch (\Exception $e) {
            return false; // Si la tabla no existe, no bloquear
        }
    }

    private function registrarIntento($email, $ip) {
        try {
            $stmt = $this->db->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)");
            $stmt->execute([':email' => $email, ':ip' => $ip]);
        } catch (\Exception $e) {}
    }

    private function limpiarIntentos($email) {
        try {
            $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE email = :email");
            $stmt->execute([':email' => $email]);
        } catch (\Exception $e) {}
    }
}
