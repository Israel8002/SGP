<?php
/**
 * Página de Login - SGE v2
 */

require_once 'autoload.php';

use App\Auth;

$auth = new Auth();
$error = '';
$success = '';

// Si ya está autenticado, redirigir al dashboard
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
}

// Procesar login
if ($_POST) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
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
    <title>Login - SGE v2</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .card {
            background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid #1e3a5f;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(30, 58, 95, 0.3);
        }
        
        .card-title {
            font-size: 1.8rem;
            color: #4fc3f7;
            margin-bottom: 0.5rem;
            text-align: center;
            text-shadow: 0 0 10px rgba(79, 195, 247, 0.3);
        }
        
        .card-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 2rem;
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
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            background: #0d1421;
            color: #e2e8f0;
            font-size: 1rem;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4fc3f7;
            box-shadow: 0 0 0 3px rgba(79, 195, 247, 0.2), 0 0 15px rgba(79, 195, 247, 0.1);
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
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(145deg, #1e3a5f 0%, #2a4a6b 100%);
            color: #e2e8f0;
            border: 1px solid #4fc3f7;
            box-shadow: 0 2px 8px rgba(79, 195, 247, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(145deg, #2a4a6b 0%, #3a5a7b 100%);
            box-shadow: 0 4px 15px rgba(79, 195, 247, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: #4fc3f7;
            border: 2px solid #4fc3f7;
            margin-top: 1rem;
        }
        
        .btn-secondary:hover {
            background: #4fc3f7;
            color: #0d1421;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="card-title">SGE v2</h1>
            <p class="card-subtitle">Sistema de Gestión de Empleados</p>
            
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
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="tu@email.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Tu contraseña"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p style="color: #94a3b8;">¿No tienes cuenta?</p>
                <a href="register.php" class="btn btn-secondary">
                    Registrarse
                </a>
            </div>
        </div>
    </div>
</body>
</html>