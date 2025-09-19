<?php
/**
 * Dashboard Principal - SGE v2
 */

require_once 'classes/Auth.php';

$auth = new Auth();

// Verificar autenticación
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$userStats = $auth->getUserStats();
$allUsers = $auth->getAllUsers();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SGE v2</title>
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
        }
        
        .header {
            background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
            border-bottom: 1px solid #1e3a5f;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4fc3f7;
            text-decoration: none;
            text-shadow: 0 0 10px rgba(79, 195, 247, 0.3);
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
        
        .welcome-section {
            margin-bottom: 2rem;
        }
        
        .welcome-title {
            font-size: 2.5rem;
            color: #60a5fa;
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            color: #94a3b8;
            font-size: 1.1rem;
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
            background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid #1e3a5f;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(30, 58, 95, 0.3);
            margin-bottom: 2rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            color: #4fc3f7;
            margin-bottom: 1rem;
            text-shadow: 0 0 10px rgba(79, 195, 247, 0.3);
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
        }
        
        .btn-secondary:hover {
            background: #4fc3f7;
            color: #0d1421;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-4 {
            margin-bottom: 2rem;
        }
        
        .mb-5 {
            margin-bottom: 3rem;
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
        
        .col-12 {
            flex: 0 0 100%;
        }
        
        .col-6 {
            flex: 0 0 50%;
        }
        
        .col-3 {
            flex: 0 0 25%;
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
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        /* Botón Hamburguesa */
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
        
        /* Overlay para cerrar menú */
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
        
        /* Menú Desktop */
        .nav-menu-desktop {
            display: flex;
            list-style: none;
            gap: 2rem;
            margin: 0;
            padding: 0;
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
        
        /* Usuario desktop */
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
        
        /* Menú móvil oculto por defecto */
        .nav-menu {
            display: none;
        }
        
        /* Usuario móvil oculto por defecto */
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
                gap: 0.8rem;
                padding: 1rem 0;
                border-top: 1px solid #1e3a5f;
                margin-top: 0.8rem;
            }
            
            .user-info {
                align-items: center;
                text-align: center;
            }
            
            .user-name {
                font-size: 0.9rem;
            }
            
            .user-role {
                font-size: 0.8rem;
            }
            
            .user-actions {
                flex-direction: column;
                width: 100%;
                gap: 0.4rem;
            }
            
            .user-actions .btn {
                width: 100%;
                text-align: center;
                padding: 0.6rem;
                font-size: 0.85rem;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .main-content {
                margin-top: 80px;
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
            <!-- Bienvenida -->
            <div class="welcome-section">
                <h1 class="welcome-title">
                    ¡Bienvenido, <?php echo htmlspecialchars($currentUser['nombre']); ?>!
                </h1>
                <p class="welcome-subtitle">
                    Sistema de Gestión de Empleados - Panel de Control
                </p>
            </div>

            <!-- Estadísticas principales -->
            <div class="dashboard-grid">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['total']; ?></div>
                    <div class="stats-label">Total Usuarios</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['activos']; ?></div>
                    <div class="stats-label">Usuarios Activos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['inactivos']; ?></div>
                    <div class="stats-label">Usuarios Inactivos</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-number"><?php echo $userStats['admins']; ?></div>
                    <div class="stats-label">Administradores</div>
                </div>
            </div>

            <!-- Lista de usuarios recientes -->
            <div class="card">
                <h2 class="card-title">Usuarios Recientes</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($allUsers, 0, 10) as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['num_empleado']); ?></td>
                                <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
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
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Menú Hamburguesa - Versión Robusta
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const navMenu = document.getElementById('navMenu');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            // Verificar que existen los elementos
            if (!hamburgerBtn || !navMenu || !mobileOverlay) {
                console.log('Elementos del menú hamburguesa no encontrados');
                return;
            }
            
            // Toggle menú
            function toggleMenu() {
                const isActive = navMenu.classList.contains('active');
                
                if (isActive) {
                    closeMenu();
                } else {
                    openMenu();
                }
            }
            
            // Abrir menú
            function openMenu() {
                hamburgerBtn.classList.add('active');
                navMenu.classList.add('active');
                mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            // Cerrar menú
            function closeMenu() {
                hamburgerBtn.classList.remove('active');
                navMenu.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Event listeners con verificación
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
            
            // Cerrar menú al hacer clic en enlaces
            if (navMenu) {
                const navLinks = navMenu.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 768) {
                            setTimeout(closeMenu, 150); // Pequeño delay para mejor UX
                        }
                    });
                });
            }
            
            // Cerrar menú al redimensionar ventana
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    closeMenu();
                }
            });
            
            // Cerrar menú con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                    closeMenu();
                }
            });
            
            // Manejar toque para cerrar menú en dispositivos táctiles
            document.addEventListener('touchstart', function(e) {
                if (navMenu && navMenu.classList.contains('active')) {
                    if (!navMenu.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                        closeMenu();
                    }
                }
            });
            
            // Prevenir que se cierre al hacer clic dentro del menú
            if (navMenu) {
                navMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
</body>
</html>