<?php
/**
 * Gestión de Usuarios - SGE v2
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'classes/Auth.php';
    $auth = new Auth();

    // Verificar autenticación
    if (!$auth->isAuthenticated()) {
        header('Location: login.php');
        exit;
    }

    $currentUser = $auth->getCurrentUser();

    // Conectar a la base de datos
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();

    $error = '';
    $success = '';
    $allUsers = [];
    $userStats = ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'admins' => 0];

    /**
     * Función para cargar todos los datos necesarios
     */
    function loadSystemData($conn) {
        $data = [
            'users' => [],
            'stats' => ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'admins' => 0],
            'turnos' => [],
            'error' => ''
        ];
        
        try {
            // Cargar usuarios con turnos
            $sql = "SELECT u.*, t.nombre_turno FROM users u 
                    LEFT JOIN turnos t ON u.turno_id = t.id 
                    ORDER BY u.created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cargar estadísticas
            $stats_queries = [
                'total' => "SELECT COUNT(*) as count FROM users",
                'activos' => "SELECT COUNT(*) as count FROM users WHERE status = 'activo'",
                'inactivos' => "SELECT COUNT(*) as count FROM users WHERE status = 'inactivo'",
                'admins' => "SELECT COUNT(*) as count FROM users WHERE rol IN ('superadmin', 'admin')"
            ];
            
            foreach ($stats_queries as $key => $query) {
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $data['stats'][$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
            
            // Cargar turnos activos
            $sql = "SELECT * FROM turnos WHERE status = 'activo' ORDER BY nombre_turno";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $data['turnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $data['error'] = 'Error al cargar datos: ' . $e->getMessage();
        }
        
        return $data;
    }
    
    // Cargar datos iniciales
    $systemData = loadSystemData($conn);
    $allUsers = $systemData['users'];
    $userStats = $systemData['stats'];
    $turnos = $systemData['turnos'];
    if (!empty($systemData['error'])) {
        $error = $systemData['error'];
    }

    // Procesar acciones
    $action = $_GET['action'] ?? '';
    $user_id = $_GET['id'] ?? '';

    // Procesar eliminación por GET (para los enlaces de eliminar)
    if ($action === 'delete' && $user_id && $user_id != 1 && $auth->isAdmin()) {
        try {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            $success = 'Usuario eliminado exitosamente';
            
            // Recargar todos los datos
            $systemData = loadSystemData($conn);
            $allUsers = $systemData['users'];
            $userStats = $systemData['stats'];
            $turnos = $systemData['turnos'];
            
        } catch (Exception $e) {
            $error = 'Error al eliminar usuario: ' . $e->getMessage();
        }
    }

    // Procesar edición por POST
    if ($_POST && $auth->isAdmin()) {
        if ($action === 'edit' && $user_id) {
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
            $rol = filter_input(INPUT_POST, 'rol', FILTER_SANITIZE_STRING);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            $birth_date = filter_input(INPUT_POST, 'birth_date', FILTER_SANITIZE_STRING);
            $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
            $turno_id = filter_input(INPUT_POST, 'turno_id', FILTER_SANITIZE_NUMBER_INT);
            
            // Si turno_id está vacío, asignar NULL
            $turno_id = empty($turno_id) ? null : $turno_id;
            
            try {
                $sql = "UPDATE users SET nombre = ?, email = ?, telefono = ?, rol = ?, status = ?, birth_date = ?, address = ?, turno_id = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre, $email, $telefono, $rol, $status, $birth_date, $address, $turno_id, $user_id]);
                $success = 'Usuario actualizado exitosamente';
                
                // Recargar todos los datos
                $systemData = loadSystemData($conn);
                $allUsers = $systemData['users'];
                $userStats = $systemData['stats'];
                $turnos = $systemData['turnos'];
                
            } catch (Exception $e) {
                $error = 'Error al actualizar usuario: ' . $e->getMessage();
            }
        }
    }

    // Obtener usuario para editar
    $edit_user = null;
    if ($action === 'edit' && $user_id) {
        try {
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $error = 'Error al cargar usuario: ' . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $error = 'Error general: ' . $e->getMessage();
    $currentUser = ['nombre' => 'Error', 'rol' => 'error'];
    $allUsers = [];
    $userStats = ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'admins' => 0];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - SGE v2</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .header {
            background: #0f172a;
            border-bottom: 1px solid #3b82f6;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #60a5fa;
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-item a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-item a:hover {
            color: #60a5fa;
            background: rgba(59, 130, 246, 0.1);
        }
        
        .main-content {
            margin-top: 80px;
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 2.5rem;
            color: #60a5fa;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: #1e293b;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            border: 1px solid #3b82f6;
            position: relative;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #60a5fa;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #60a5fa;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #cbd5e1;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card {
            background: #1e293b;
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid #3b82f6;
            margin-bottom: 2rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            color: #60a5fa;
            margin-bottom: 1rem;
        }
        
        .table-container {
            background: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #3b82f6;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #1e3a8a;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #3b82f6;
            color: #cbd5e1;
        }
        
        .table tr:hover {
            background: rgba(59, 130, 246, 0.05);
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
        }
        
        .btn-primary:hover {
            background: #60a5fa;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: transparent;
            color: #60a5fa;
            border: 2px solid #60a5fa;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #10b981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #ef4444;
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
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .badge-secondary {
            background: rgba(148, 163, 184, 0.2);
            color: #94a3b8;
        }
        
        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }
        
        .badge-warning {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .badge-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        
        .nav-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .user-name {
            font-weight: 600;
            color: #f8fafc;
        }
        
        .user-role {
            font-size: 0.875rem;
            color: #94a3b8;
            text-transform: uppercase;
        }
        
        .user-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 2px;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .action-btn-edit {
            background: #f59e0b;
            color: white;
        }
        
        .action-btn-edit:hover {
            background: #d97706;
        }
        
        .action-btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .action-btn-delete:hover {
            background: #dc2626;
        }
        
        .icon {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        .actions-cell {
            text-align: center;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .nav-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .main-content {
                margin-top: 120px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
        
        /* Estilos del menú hamburguesa */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
            background: none;
            border: none;
            z-index: 1001;
        }
        
        .hamburger span {
            width: 25px;
            height: 3px;
            background: #4fc3f7;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }
        
        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }
        
        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        .mobile-overlay.active {
            display: block;
        }
        
        .nav-menu-desktop {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-menu-desktop .nav-item a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .nav-menu-desktop .nav-item a:hover {
            background: #1e3a5f;
            color: #4fc3f7;
        }
        
        .desktop-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .desktop-user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .desktop-user-name {
            font-weight: 600;
            color: #e2e8f0;
        }
        
        .desktop-user-role {
            font-size: 0.875rem;
            color: #94a3b8;
            text-transform: uppercase;
        }
        
        .desktop-user-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .nav-menu {
            display: none;
        }
        
        .nav-user {
            display: none;
        }
        
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }
            
            .nav-menu-desktop {
                display: none;
            }
            
            .desktop-user {
                display: none;
            }
            
            .nav-menu {
                display: none;
                position: fixed;
                top: 70px;
                right: -280px;
                width: 260px;
                height: calc(100vh - 70px);
                background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
                flex-direction: column;
                padding: 1rem;
                transition: right 0.3s ease;
                z-index: 1000;
                border-left: 1px solid #1e3a5f;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.3);
                overflow-y: auto;
            }
            
            .nav-menu.active {
                display: flex;
                right: 0;
            }
            
            .nav-menu .nav-item {
                margin: 0.2rem 0;
                width: 100%;
            }
            
            .nav-menu .nav-link {
                display: block;
                padding: 0.8rem;
                background: linear-gradient(145deg, #1e3a5f 0%, #2a4a6b 100%);
                border-radius: 8px;
                text-align: center;
                border: 1px solid #1e3a5f;
                transition: all 0.3s ease;
                font-size: 0.9rem;
                color: #e2e8f0;
                text-decoration: none;
            }
            
            .nav-menu .nav-link:hover {
                background: linear-gradient(145deg, #2a4a6b 0%, #3a5a7b 100%);
                box-shadow: 0 4px 15px rgba(79, 195, 247, 0.2);
                transform: translateY(-2px);
                border-color: #4fc3f7;
            }
            
            .nav-user {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                padding: 1.5rem 0;
                border-top: 1px solid #1e3a5f;
                margin-top: 1rem;
            }
            
            .user-name {
                font-size: 1rem;
                font-weight: 600;
                color: #4fc3f7;
            }
            
            .user-role {
                font-size: 0.9rem;
                color: #94a3b8;
            }
            
            .user-actions .btn {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Overlay para cerrar menú móvil -->
    <div class="mobile-overlay" id="mobileOverlay"></div>
    
    <!-- Header con navegación -->
    <header class="header">
        <div class="navbar">
            <a href="dashboard.php" class="logo">SGE v2</a>
            
            <!-- Menú Desktop (navegación horizontal) -->
            <ul class="nav-menu-desktop">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a href="shifts.php" class="nav-link">Turnos</a>
                </li>
                <li class="nav-item">
                    <a href="authorizations.php" class="nav-link">Autorizaciones</a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">Reportes</a>
                </li>
            </ul>
            
            <!-- Usuario Desktop (de vuelta a la derecha) -->
            <div class="desktop-user">
                <div class="desktop-user-info">
                    <span class="desktop-user-name"><?php echo htmlspecialchars($currentUser['nombre']); ?></span>
                    <span class="desktop-user-role"><?php echo ucfirst($currentUser['rol']); ?></span>
                </div>
                <div class="desktop-user-actions">
                    <a href="profile.php" class="btn btn-secondary btn-sm">Perfil</a>
                    <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
                </div>
            </div>
            
            <!-- Botón Hamburguesa (solo móvil) -->
            <button class="hamburger" id="hamburgerBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a href="shifts.php" class="nav-link">Turnos</a>
                </li>
                <li class="nav-item">
                    <a href="authorizations.php" class="nav-link">Autorizaciones</a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link">Reportes</a>
                </li>
                
                <!-- Usuario en menú móvil -->
                <div class="nav-user">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['nombre']); ?></span>
                        <span class="user-role"><?php echo ucfirst($currentUser['rol']); ?></span>
                    </div>
                    <div class="user-actions">
                        <a href="profile.php" class="btn btn-secondary btn-sm">Perfil</a>
                        <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
                    </div>
                </div>
            </ul>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Gestión de Usuarios</h1>
            <p class="page-subtitle">Administra los usuarios del sistema</p>
            

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

            <!-- Estadísticas de usuarios -->
            <div class="dashboard-grid">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['total']; ?></div>
                    <div class="stats-label">Total Usuarios</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['activos']; ?></div>
                    <div class="stats-label">Activos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['inactivos']; ?></div>
                    <div class="stats-label">Inactivos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['admins']; ?></div>
                    <div class="stats-label">Administradores</div>
                </div>
            </div>

            <?php if ($edit_user && isset($auth) && $auth->isAdmin()): ?>
            <!-- Formulario para editar usuario -->
            <div class="card">
                <h2 class="card-title">Editar Usuario</h2>
                <form method="POST" action="users.php?action=edit&id=<?php echo $edit_user['id']; ?>">
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input 
                                    type="text" 
                                    id="nombre" 
                                    name="nombre" 
                                    class="form-input" 
                                    placeholder="Tu nombre completo"
                                    value="<?php echo htmlspecialchars($edit_user['nombre'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="email" class="form-label">Email *</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="tu@email.com"
                                    value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono *</label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="telefono" 
                                    class="form-input" 
                                    placeholder="6861234567"
                                    value="<?php echo htmlspecialchars($edit_user['telefono'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="rol" class="form-label">Rol *</label>
                                <select id="rol" name="rol" class="form-select" required>
                                    <option value="user" <?php echo (($edit_user['rol'] ?? '') === 'user') ? 'selected' : ''; ?>>Usuario</option>
                                    <option value="admin" <?php echo (($edit_user['rol'] ?? '') === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="status" class="form-label">Estado *</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="activo" <?php echo (($edit_user['status'] ?? 'activo') === 'activo') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo (($edit_user['status'] ?? '') === 'inactivo' ? 'selected' : ''); ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="turno_id" class="form-label">Turno Asignado</label>
                                <select id="turno_id" name="turno_id" class="form-select">
                                    <option value="">Sin turno asignado</option>
                                    <?php foreach ($turnos as $turno): ?>
                                        <option value="<?php echo $turno['id']; ?>" 
                                                <?php echo (($edit_user['turno_id'] ?? '') == $turno['id']) ? 'selected' : ''; ?>>
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
                                    value="<?php echo htmlspecialchars($edit_user['birth_date'] ?? ''); ?>"
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
                                    value="<?php echo htmlspecialchars($edit_user['address'] ?? ''); ?>"
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                        <a href="users.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Tabla de usuarios -->
            <div class="card">
                <h2 class="card-title">Lista de Usuarios</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Turno</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <?php if ($auth->isAdmin()): ?>
                                <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['num_empleado']); ?></td>
                                <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefono']); ?></td>
                                <td>
                                    <?php if ($user['nombre_turno']): ?>
                                        <span class="badge badge-info">
                                            <?php echo htmlspecialchars($user['nombre_turno']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Sin turno</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['rol'] === 'superadmin' ? 'danger' : ($user['rol'] === 'admin' ? 'warning' : 'info'); ?>">
                                        <?php echo ucfirst($user['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <?php if ($auth->isAdmin()): ?>
                                <td class="actions-cell">
                                    <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                       class="action-btn action-btn-edit" 
                                       title="Editar usuario">
                                        <svg class="icon" viewBox="0 0 24 24">
                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                    </a>
                                    <?php if ($user['id'] != 1): // No permitir eliminar superadmin ?>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                       class="action-btn action-btn-delete" 
                                       title="Eliminar usuario"
                                       onclick="return confirmarEliminacion('<?php echo htmlspecialchars($user['nombre']); ?>')">
                                        <svg class="icon" viewBox="0 0 24 24">
                                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                                        </svg>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function confirmarEliminacion(nombreUsuario) {
            return confirm('¿Estás seguro de eliminar al usuario "' + nombreUsuario + '"?\n\nEsta acción no se puede deshacer.');
        }
        
        // Mostrar/ocultar alertas automáticamente después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
        
        // Menú hamburguesa
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const navMenu = document.getElementById('navMenu');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            if (!hamburgerBtn || !navMenu || !mobileOverlay) {
                console.log('Elementos del menú hamburguesa no encontrados');
                return;
            }
            
            function toggleMenu() {
                const isActive = navMenu.classList.contains('active');
                if (isActive) {
                    closeMenu();
                } else {
                    openMenu();
                }
            }
            
            function openMenu() {
                hamburgerBtn.classList.add('active');
                navMenu.classList.add('active');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            function closeMenu() {
                hamburgerBtn.classList.remove('active');
                navMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            if (hamburgerBtn) {
                hamburgerBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }
            
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', closeMenu);
            }
            
            if (navMenu) {
                const navLinks = navMenu.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 768) {
                            setTimeout(closeMenu, 150);
                        }
                    });
                });
            }
            
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    closeMenu();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                    closeMenu();
                }
            });
            
            document.addEventListener('touchstart', function(e) {
                if (navMenu && navMenu.classList.contains('active')) {
                    if (!navMenu.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                        closeMenu();
                    }
                }
            });
            
            if (navMenu) {
                navMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>
</html>