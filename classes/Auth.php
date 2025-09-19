<?php
/**
 * Sistema de Autenticación SGE v2
 * Clase para manejo de usuarios y sesiones
 */

require_once __DIR__ . '/../config/database.php';

class Auth {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->ensureSession();
    }
    
    /**
     * Asegurar que la sesión esté iniciada una sola vez
     */
    private function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function register($data) {
        try {
            // Validar datos
            $this->validateRegistrationData($data);
            
            // Verificar si el email ya existe
            if ($this->emailExists($data['email'])) {
                throw new Exception('El email ya está registrado');
            }
            
            // Verificar si el número de empleado ya existe
            if ($this->employeeNumberExists($data['num_empleado'])) {
                throw new Exception('El número de empleado ya está registrado');
            }
            
            // Hash de la contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insertar usuario
            $sql = "INSERT INTO users (num_empleado, nombre, email, password, telefono, rol, birth_date, address, turno_id) 
                    VALUES (:num_empleado, :nombre, :email, :password, :telefono, :rol, :birth_date, :address, :turno_id)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':num_empleado', $data['num_empleado']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':telefono', $data['telefono']);
            $stmt->bindParam(':rol', $data['rol']);
            $stmt->bindParam(':birth_date', $data['birth_date']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':turno_id', $data['turno_id']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'user_id' => $this->conn->lastInsertId()
                ];
            } else {
                throw new Exception('Error al registrar el usuario');
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        try {
            // Verificar intentos de login
            if ($this->isAccountLocked($email)) {
                throw new Exception('Cuenta bloqueada temporalmente. Intenta más tarde.');
            }
            
            // Buscar usuario
            $sql = "SELECT id, num_empleado, nombre, email, password, rol, status FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $this->recordFailedAttempt($email);
                throw new Exception('Credenciales inválidas');
            }
            
            // Verificar estado del usuario
            if ($user['status'] !== 'activo') {
                throw new Exception('Cuenta inactiva. Contacta al administrador.');
            }
            
            // Verificar contraseña
            if (!password_verify($password, $user['password'])) {
                $this->recordFailedAttempt($email);
                throw new Exception('Credenciales inválidas');
            }
            
            // Limpiar intentos fallidos
            $this->clearFailedAttempts($email);
            
            // Crear sesión
            $this->createSession($user);
            
            return [
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'user' => $user
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $this->ensureSession();
        session_destroy();
        return true;
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isAuthenticated() {
        $this->ensureSession();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'num_empleado' => $_SESSION['user_num_empleado'],
            'nombre' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'rol' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Verificar permisos de administrador
     */
    public function isAdmin() {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        // El usuario con ID 1 siempre es admin independientemente de su rol en la BD
        return $_SESSION['user_id'] == 1 || in_array($_SESSION['user_role'], ['superadmin', 'admin']);
    }
    
    /**
     * Verificar permisos de superadmin
     */
    public function isSuperAdmin() {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        // El usuario con ID 1 siempre es superadmin independientemente de su rol en la BD
        return $_SESSION['user_id'] == 1 || $_SESSION['user_role'] === 'superadmin';
    }
    
    /**
     * Validar datos de registro
     */
    private function validateRegistrationData($data) {
        $required = ['num_empleado', 'nombre', 'email', 'password', 'telefono', 'rol'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        
        // Validar contraseña
        if (strlen($data['password']) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres');
        }
        
        // Validar rol
        if (!in_array($data['rol'], ['admin', 'user'])) {
            throw new Exception('Rol inválido');
        }
    }
    
    /**
     * Verificar si el email existe
     */
    private function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Verificar si el número de empleado existe
     */
    private function employeeNumberExists($num_empleado) {
        $sql = "SELECT id FROM users WHERE num_empleado = :num_empleado";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':num_empleado', $num_empleado);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Crear sesión de usuario
     */
    private function createSession($user) {
        $this->ensureSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_num_empleado'] = $user['num_empleado'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Verificar si la cuenta está bloqueada
     */
    private function isAccountLocked($email) {
        $sql = "SELECT failed_attempts, last_attempt FROM login_attempts WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attempt) {
            return false;
        }
        
        // Verificar si ha pasado el tiempo de bloqueo (15 minutos)
        if (time() - $attempt['last_attempt'] > 900) {
            $this->clearFailedAttempts($email);
            return false;
        }
        
        return $attempt['failed_attempts'] >= 5;
    }
    
    /**
     * Registrar intento fallido
     */
    private function recordFailedAttempt($email) {
        $sql = "INSERT INTO login_attempts (email, failed_attempts, last_attempt) 
                VALUES (:email, 1, :time) 
                ON DUPLICATE KEY UPDATE 
                failed_attempts = failed_attempts + 1, 
                last_attempt = :time";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindValue(':time', time());
        $stmt->execute();
    }
    
    /**
     * Limpiar intentos fallidos
     */
    private function clearFailedAttempts($email) {
        $sql = "DELETE FROM login_attempts WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAllUsers() {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function getUserStats() {
        try {
            $stats = [];
            
            // Total de usuarios
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Usuarios activos
            $sql = "SELECT COUNT(*) as activos FROM users WHERE status = 'activo'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];
            
            // Usuarios inactivos
            $sql = "SELECT COUNT(*) as inactivos FROM users WHERE status = 'inactivo'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['inactivos'] = $stmt->fetch(PDO::FETCH_ASSOC)['inactivos'];
            
            // Administradores
            $sql = "SELECT COUNT(*) as admins FROM users WHERE rol IN ('superadmin', 'admin')";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $stats['admins'] = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];
            
            return $stats;
            
        } catch (Exception $e) {
            return [
                'total' => 0,
                'activos' => 0,
                'inactivos' => 0,
                'admins' => 0
            ];
        }
    }
}
?>
