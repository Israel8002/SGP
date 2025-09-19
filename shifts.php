<?php
/**
 * Gestión de Turnos - SGE v2
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

// Obtener turnos
$shifts = [];
try {
    $sql = "SELECT * FROM turnos ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = "Error al cargar turnos: " . $e->getMessage();
}

// Procesar acciones
$action = $_GET['action'] ?? '';
$shift_id = $_GET['id'] ?? '';

if ($_POST && $auth->isAdmin()) {
    if ($action === 'create') {
        $nombre_turno = filter_input(INPUT_POST, 'nombre_turno', FILTER_SANITIZE_STRING);
        $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
        $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
        
        if ($nombre_turno && $start_time && $end_time) {
            try {
                $sql = "INSERT INTO turnos (nombre_turno, start_time, end_time) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$nombre_turno, $start_time, $end_time]);
                $success = "Turno creado exitosamente";
            } catch (Exception $e) {
                $error = "Error al crear turno: " . $e->getMessage();
            }
        }
    } elseif ($action === 'edit' && $shift_id) {
        $nombre_turno = filter_input(INPUT_POST, 'nombre_turno', FILTER_SANITIZE_STRING);
        $start_time = filter_input(INPUT_POST, 'start_time', FILTER_SANITIZE_STRING);
        $end_time = filter_input(INPUT_POST, 'end_time', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        try {
            $sql = "UPDATE turnos SET nombre_turno = ?, start_time = ?, end_time = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nombre_turno, $start_time, $end_time, $status, $shift_id]);
            $success = "Turno actualizado exitosamente";
        } catch (Exception $e) {
            $error = "Error al actualizar turno: " . $e->getMessage();
        }
    } elseif ($action === 'delete' && $shift_id) {
        try {
            $sql = "DELETE FROM turnos WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$shift_id]);
            $success = "Turno eliminado exitosamente";
        } catch (Exception $e) {
            $error = "Error al eliminar turno: " . $e->getMessage();
        }
    }
    
    // Recargar turnos
    $sql = "SELECT * FROM turnos ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener turno para editar
$edit_shift = null;
if ($action === 'edit' && $shift_id) {
    try {
        $sql = "SELECT * FROM turnos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$shift_id]);
        $edit_shift = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = "Error al cargar turno: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnos - SGE v2</title>
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
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
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
        
        .col-12 {
            flex: 0 0 100%;
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
        
        .d-none {
            display: none;
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
            
            .col-6 {
                flex: 0 0 100%;
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
            <h1 class="page-title">Gestión de Turnos</h1>
            <p class="page-subtitle">Administra los turnos de trabajo del sistema</p>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($auth->isAdmin()): ?>
            <!-- Formulario para crear/editar turno -->
            <div class="card">
                <h2 class="card-title">
                    <?php echo $edit_shift ? 'Editar Turno' : 'Crear Nuevo Turno'; ?>
                </h2>
                <form method="POST" action="shifts.php<?php echo $edit_shift ? '?action=edit&id=' . $edit_shift['id'] : '?action=create'; ?>">
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="nombre_turno" class="form-label">Nombre del Turno</label>
                                <input 
                                    type="text" 
                                    id="nombre_turno" 
                                    name="nombre_turno" 
                                    class="form-input" 
                                    placeholder="Ej: Turno Matutino"
                                    value="<?php echo htmlspecialchars($edit_shift['nombre_turno'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="status" class="form-label">Estado</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="activo" <?php echo ($edit_shift['status'] ?? 'activo') === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo ($edit_shift['status'] ?? '') === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="start_time" class="form-label">Hora de Inicio</label>
                                <input 
                                    type="time" 
                                    id="start_time" 
                                    name="start_time" 
                                    class="form-input"
                                    value="<?php echo htmlspecialchars($edit_shift['start_time'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="col col-6">
                            <div class="form-group">
                                <label for="end_time" class="form-label">Hora de Fin</label>
                                <input 
                                    type="time" 
                                    id="end_time" 
                                    name="end_time" 
                                    class="form-input"
                                    value="<?php echo htmlspecialchars($edit_shift['end_time'] ?? ''); ?>"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_shift ? 'Actualizar Turno' : 'Crear Turno'; ?>
                        </button>
                        <?php if ($edit_shift): ?>
                            <a href="shifts.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Lista de turnos -->
            <div class="card">
                <h2 class="card-title">Lista de Turnos</h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <?php if ($auth->isAdmin()): ?>
                                <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shifts as $shift): ?>
                            <tr>
                                <td><?php echo $shift['id']; ?></td>
                                <td><?php echo htmlspecialchars($shift['nombre_turno']); ?></td>
                                <td><?php echo date('H:i', strtotime($shift['start_time'])); ?></td>
                                <td><?php echo date('H:i', strtotime($shift['end_time'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $shift['status'] === 'activo' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($shift['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($shift['created_at'])); ?></td>
                                <?php if ($auth->isAdmin()): ?>
                                <td>
                                    <a href="shifts.php?action=edit&id=<?php echo $shift['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="shifts.php?action=delete&id=<?php echo $shift['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Estás seguro de eliminar este turno?')">Eliminar</a>
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
