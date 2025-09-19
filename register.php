<?php
/**
 * Página de Registro - SGE v2
 */

require_once 'classes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Si ya está autenticado, redirigir al dashboard
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// Obtener turnos disponibles
$turnos = [];
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT * FROM turnos WHERE status = 'activo' ORDER BY nombre_turno";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Error al cargar turnos: ' . $e->getMessage();
}

// Procesar registro
if ($_POST) {
    $data = [
        'num_empleado' => filter_input(INPUT_POST, 'num_empleado', FILTER_SANITIZE_STRING),
        'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING),
        'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'telefono' => filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING),
        'rol' => filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING),
        'birth_date' => filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING),
        'address' => filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING),
        'turno_id' => filter_input(INPUT_POST, 'turno_id', FILTER_SANITIZE_NUMBER_INT)
    ];
    
    // Validaciones básicas
    if (empty($data['num_empleado']) || empty($data['nombre']) || empty($data['email']) || 
        empty($data['password']) || empty($data['telefono']) || empty($data['rol'])) {
        $error = 'Por favor completa todos los campos requeridos';
    } elseif ($data['password'] !== $data['confirm_password']) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($data['password']) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } else {
        // Remover confirm_password del array
        unset($data['confirm_password']);
        
        // Forzar rol como 'user' para todos los registros
        $data['rol'] = 'user';
        
        // Si turno_id está vacío, asignar NULL
        $data['turno_id'] = empty($data['turno_id']) ? null : $data['turno_id'];
        
        $result = $auth->register($data);
        
        if ($result['success']) {
            $success = 'Usuario registrado exitosamente. Ya puedes iniciar sesión.';
            // Limpiar formulario
            $data = array_fill_keys(array_keys($data), '');
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SGE v2</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0a0f1c 0%, #1a2332 50%, #2a3441 100%);
            color: #e2e8f0;
            line-height: 1.6;
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .card {
            background: #1e293b;
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid #3b82f6;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-size: 1.8rem;
            color: #60a5fa;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .card-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        
        .col {
            flex: 1;
            padding: 0 15px;
        }
        
        .col-6 {
            flex: 0 0 50%;
        }
        
        .col-12 {
            flex: 0 0 100%;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #cbd5e1;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            background: #1e293b;
            color: #f8fafc;
            font-size: 1rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            background: #1e293b;
            color: #f8fafc;
            font-size: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #60a5fa;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: #60a5fa;
            border: 2px solid #60a5fa;
            margin-top: 1rem;
        }
        
        .btn-secondary:hover {
            background: #60a5fa;
            color: white;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #ef4444;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #10b981;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .w-100 {
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .col-6 {
                flex: 0 0 100%;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="card-title">Registro de Usuario</h1>
            <p class="card-subtitle">SGE v2 - Sistema de Gestión de Empleados</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="num_empleado" class="form-label">Número de Empleado *</label>
                                <input 
                                    type="text" 
                                    id="num_empleado" 
                                    name="num_empleado" 
                                    class="form-input" 
                                    placeholder="Ej: 1234"
                                    required
                                >
                        </div>
                    </div>
                    
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input 
                                    type="text" 
                                    id="nombre" 
                                    name="nombre" 
                                    class="form-input" 
                                    placeholder="Tu nombre completo"
                                    required
                                >
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Email *</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="tu@email.com"
                                    autocomplete="off"
                                    value=""
                                    required
                                >
                        </div>
                    </div>
                    
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="telefono" class="form-label">Teléfono *</label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="telefono" 
                                    class="form-input" 
                                    placeholder="6861234567"
                                    required
                                >
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password"
                                value=""
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Repite tu contraseña"
                                autocomplete="new-password"
                                value=""
                                required
                            >
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="rol" class="form-label">Rol *</label>
                            <select id="rol" name="rol" class="form-select" required>
                                <option value="user" selected>Usuario</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="turno_id" class="form-label">Turno Asignado</label>
                            <select id="turno_id" name="turno_id" class="form-select">
                                <option value="">Sin turno asignado</option>
                                <?php foreach ($turnos as $turno): ?>
                                    <option value="<?php echo $turno['id']; ?>">
                                        <?php echo htmlspecialchars($turno['nombre_turno']); ?> 
                                        (<?php echo substr($turno['start_time'], 0, 5); ?> - <?php echo substr($turno['end_time'], 0, 5); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                                <input 
                                    type="date" 
                                    id="birth_date" 
                                    name="birth_date" 
                                    class="form-input"
                                >
                        </div>
                    </div>
                    <div class="col col-6">
                        <div class="form-group">
                            <label for="address" class="form-label">Dirección</label>
                            <input 
                                type="text" 
                                id="address" 
                                name="address" 
                                class="form-input" 
                                placeholder="Tu dirección completa"
                            >
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Registrar Usuario
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p style="color: #94a3b8;">¿Ya tienes cuenta?</p>
                <a href="login.php" class="btn btn-secondary">
                    Iniciar Sesión
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Limpiar todos los campos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Limpiar campos de texto
            const textInputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="date"], textarea');
            textInputs.forEach(input => {
                input.value = '';
            });
            
            // Limpiar campos de contraseña
            const passwordInputs = document.querySelectorAll('input[type="password"]');
            passwordInputs.forEach(input => {
                input.value = '';
            });
            
            // Asegurar que el rol esté en "user"
            const rolSelect = document.getElementById('rol');
            if (rolSelect) {
                rolSelect.value = 'user';
            }
        });
    </script>
</body>
</html>