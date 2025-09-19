<?php
/**
 * Clase de Seguridad SGE v2
 * Implementa medidas de seguridad basadas en las limitaciones del hosting
 */

class Security {
    
    /**
     * Sanitizar entrada de datos
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar contraseña
     */
    public static function validatePassword($password) {
        if (strlen($password) < SecurityConfig::MIN_PASSWORD_LENGTH) {
            return false;
        }
        
        if (SecurityConfig::REQUIRE_SPECIAL_CHARS) {
            return preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
        }
        
        return true;
    }
    
    /**
     * Generar token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
    
    /**
     * Verificar token CSRF
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Limpiar token CSRF
     */
    public static function clearCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * Validar archivo subido
     */
    public static function validateUploadedFile($file) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return false;
        }
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Verificar tamaño
        if ($file['size'] > SecurityConfig::MAX_FILE_SIZE) {
            return false;
        }
        
        // Verificar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, SecurityConfig::ALLOWED_EXTENSIONS)) {
            return false;
        }
        
        // Verificar tipo MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generar nombre seguro para archivo
     */
    public static function generateSafeFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Limpiar nombre
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
        $name = substr($name, 0, 50); // Limitar longitud
        
        // Agregar timestamp para unicidad
        $timestamp = time();
        
        return $name . '_' . $timestamp . '.' . $extension;
    }
    
    /**
     * Verificar límites de memoria y tiempo
     */
    public static function checkResourceLimits() {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = self::convertToBytes($memoryLimit);
        
        // Si el uso de memoria supera el 80% del límite
        if ($memoryUsage > ($memoryLimitBytes * 0.8)) {
            return false;
        }
        
        // Verificar tiempo de ejecución
        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime > 0) {
            $executionTime = time() - $_SERVER['REQUEST_TIME'];
            if ($executionTime > ($maxExecutionTime * 0.8)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Convertir límite de memoria a bytes
     */
    private static function convertToBytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Registrar actividad de seguridad
     */
    public static function logSecurityEvent($event, $details = []) {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        // Crear directorio si no existe
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verificar IP permitida (si es necesario)
     */
    public static function isIPAllowed($ip) {
        // Lista de IPs permitidas (configurar según necesidades)
        $allowedIPs = [
            '127.0.0.1',
            '::1'
        ];
        
        // Si no hay restricciones, permitir todas
        if (empty($allowedIPs)) {
            return true;
        }
        
        return in_array($ip, $allowedIPs);
    }
    
    /**
     * Generar hash seguro para archivos
     */
    public static function generateFileHash($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        return hash_file('sha256', $filePath);
    }
    
    /**
     * Verificar integridad de archivo
     */
    public static function verifyFileIntegrity($filePath, $expectedHash) {
        $actualHash = self::generateFileHash($filePath);
        return $actualHash && hash_equals($expectedHash, $actualHash);
    }
    
    /**
     * Limpiar datos sensibles de logs
     */
    public static function sanitizeLogData($data) {
        $sensitiveFields = ['password', 'token', 'secret', 'key'];
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $data[$key] = '[REDACTED]';
                } else {
                    $data[$key] = self::sanitizeLogData($value);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * Verificar headers de seguridad
     */
    public static function setSecurityHeaders() {
        // Prevenir clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevenir MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy básico
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
    }
    
    /**
     * Verificar si la sesión es válida
     */
    public static function isSessionValid() {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        
        // Verificar timeout de sesión
        if (isset($_SESSION['login_time'])) {
            $sessionAge = time() - $_SESSION['login_time'];
            if ($sessionAge > SecurityConfig::SESSION_TIMEOUT) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Regenerar ID de sesión periódicamente
     */
    public static function regenerateSessionId() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
?>

