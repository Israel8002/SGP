<?php
/**
 * Configuración de Base de Datos SGE v2
 * Configurado para las limitaciones del hosting
 */

class Database {
    private $host = 'sql213.byethost7.com';
    private $db_name = 'b7_39929293_sge2';
    private $username = 'b7_39929293';
    private $password = 'Idmitnick2$';
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
        
        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Verificar conexión
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            $stmt = $conn->query("SELECT 1");
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
}

/**
 * Configuración de seguridad
 */
class SecurityConfig {
    // Configuración de sesiones
    const SESSION_TIMEOUT = 3600; // 1 hora
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_TIME = 900; // 15 minutos
    
    // Configuración de contraseñas
    const MIN_PASSWORD_LENGTH = 8;
    const REQUIRE_SPECIAL_CHARS = true;
    
    // Configuración de archivos
    const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    
    /**
     * Inicializar configuración de seguridad
     */
    public static function init() {
        // Configurar sesiones seguras
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.gc_maxlifetime', self::SESSION_TIMEOUT);
        
        // Configurar headers de seguridad
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
    }
}

// Inicializar configuración de seguridad
SecurityConfig::init();
?>

