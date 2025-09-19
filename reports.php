<?php
/**
 * Reportes - SGE v2
 */

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

// Obtener estadísticas
$stats = [];
try {
    // Total usuarios
    $sql = "SELECT COUNT(*) as total FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Usuarios activos
    $sql = "SELECT COUNT(*) as activos FROM users WHERE status = 'activo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['usuarios_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];
    
    // Usuarios inactivos
    $sql = "SELECT COUNT(*) as inactivos FROM users WHERE status = 'inactivo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['usuarios_inactivos'] = $stmt->fetch(PDO::FETCH_ASSOC)['inactivos'];
    
    // Administradores
    $sql = "SELECT COUNT(*) as admins FROM users WHERE rol IN ('superadmin', 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['administradores'] = $stmt->fetch(PDO::FETCH_ASSOC)['admins'];
    
    // Total turnos
    $sql = "SELECT COUNT(*) as total FROM turnos";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['total_turnos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Turnos activos
    $sql = "SELECT COUNT(*) as activos FROM turnos WHERE status = 'activo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['turnos_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['activos'];
    
    // Usuarios por rol
    $sql = "SELECT rol, COUNT(*) as cantidad FROM users GROUP BY rol";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['usuarios_por_rol'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Usuarios recientes (últimos 30 días)
    $sql = "SELECT COUNT(*) as recientes FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stats['usuarios_recientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['recientes'];
    
} catch (Exception $e) {
    $error = "Error al cargar estadísticas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - SGE v2</title>
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
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
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
            <h1 class="page-title">Reportes del Sistema</h1>
            <p class="page-subtitle">Estadísticas y análisis del sistema SGE v2</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas principales -->
            <div class="dashboard-grid">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_users'] ?? 0; ?></div>
                    <div class="stats-label">Total Usuarios</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['usuarios_activos'] ?? 0; ?></div>
                    <div class="stats-label">Usuarios Activos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['usuarios_inactivos'] ?? 0; ?></div>
                    <div class="stats-label">Usuarios Inactivos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['administradores'] ?? 0; ?></div>
                    <div class="stats-label">Administradores</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_turnos'] ?? 0; ?></div>
                    <div class="stats-label">Total Turnos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['turnos_activos'] ?? 0; ?></div>
                    <div class="stats-label">Turnos Activos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['usuarios_recientes'] ?? 0; ?></div>
                    <div class="stats-label">Nuevos (30 días)</div>
                </div>
            </div>

            <!-- Distribución por roles -->
            <div class="card">
                <h2 class="card-title">Distribución de Usuarios por Rol</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Rol</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = $stats['total_users'] ?? 1;
                            foreach ($stats['usuarios_por_rol'] ?? [] as $rol): 
                                $porcentaje = round(($rol['cantidad'] / $total) * 100, 1);
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $rol['rol'] === 'superadmin' ? 'danger' : 
                                            ($rol['rol'] === 'admin' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($rol['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo $rol['cantidad']; ?></td>
                                <td><?php echo $porcentaje; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resumen del sistema -->
            <div class="card">
                <h2 class="card-title">Resumen del Sistema</h2>
                <div class="row">
                    <div class="col col-6">
                        <h3 style="color: #60a5fa; margin-bottom: 1rem;">Usuarios</h3>
                        <p><strong>Total:</strong> <?php echo $stats['total_users'] ?? 0; ?> usuarios registrados</p>
                        <p><strong>Activos:</strong> <?php echo $stats['usuarios_activos'] ?? 0; ?> usuarios activos</p>
                        <p><strong>Inactivos:</strong> <?php echo $stats['usuarios_inactivos'] ?? 0; ?> usuarios inactivos</p>
                        <p><strong>Administradores:</strong> <?php echo $stats['administradores'] ?? 0; ?> administradores</p>
                    </div>
                    <div class="col col-6">
                        <h3 style="color: #60a5fa; margin-bottom: 1rem;">Turnos</h3>
                        <p><strong>Total:</strong> <?php echo $stats['total_turnos'] ?? 0; ?> turnos configurados</p>
                        <p><strong>Activos:</strong> <?php echo $stats['turnos_activos'] ?? 0; ?> turnos activos</p>
                        <p><strong>Inactivos:</strong> <?php echo ($stats['total_turnos'] ?? 0) - ($stats['turnos_activos'] ?? 0); ?> turnos inactivos</p>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card text-center">
                <h2 class="card-title">Acciones</h2>
                <a href="users.php" class="btn btn-primary">Gestionar Usuarios</a>
                <a href="shifts.php" class="btn btn-secondary">Gestionar Turnos</a>
                <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
        </div>
    </main>
    
    <script>
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
