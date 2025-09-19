<?php
/**
 * Autorizaciones - SGE v2
 */

require_once 'classes/Auth.php';

$auth = new Auth();

// Verificar autenticación
if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorizaciones - SGE v2</title>
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
            color: #4fc3f7;
            margin-bottom: 2rem;
            text-align: center;
            text-shadow: 0 0 15px rgba(79, 195, 247, 0.3);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid #1e3a5f;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(30, 58, 95, 0.3);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(30, 58, 95, 0.4);
        }
        
        .card-title {
            font-size: 1.5rem;
            color: #4fc3f7;
            margin-bottom: 1rem;
            text-shadow: 0 0 10px rgba(79, 195, 247, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-icon {
            font-size: 1.8rem;
        }
        
        .card-description {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .development-badge {
            display: inline-block;
            background: linear-gradient(145deg, #1e3a5f 0%, #2a4a6b 100%);
            color: #4fc3f7;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid #1e3a5f;
            box-shadow: 0 2px 8px rgba(79, 195, 247, 0.2);
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
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
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
            <h1 class="page-title">Autorizaciones</h1>
            
            <div class="dashboard-grid">
                <!-- Dias Autorizados -->
                <div class="card">
                    <h2 class="card-title">
                        <span class="card-icon">📅</span>
                        Dias Autorizados
                    </h2>
                    <p class="card-description">
                        Gestiona los días autorizados para ausencias, permisos especiales y licencias de los empleados.
                    </p>
                    <div class="development-badge">En desarrollo</div>
                </div>
                
                <!-- Dias Economicos -->
                <div class="card">
                    <h2 class="card-title">
                        <span class="card-icon">📝</span>
                        Dias Economicos
                    </h2>
                    <p class="card-description">
                        Administra los días económicos por turnos.
                    </p>
                    <div class="development-badge">En desarrollo</div>
                </div>
                
                <!-- Txt -->
                <div class="card">
                    <h2 class="card-title">
                        <span class="card-icon">🏥</span>
                        Txt
                    </h2>
                    <p class="card-description">
                        Genera y gestiona autorizaciones de guardias turno por turno.
                    </p>
                    <div class="development-badge">En desarrollo</div>
                </div>
                
                <!-- Vacaciones -->
                <div class="card">
                    <h2 class="card-title">
                        <span class="card-icon">🏖️</span>
                        Vacaciones
                    </h2>
                    <p class="card-description">
                        Controla las solicitudes, aprobaciones y seguimiento de períodos vacacionales de los empleados.
                    </p>
                    <div class="development-badge">En desarrollo</div>
                </div>
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
