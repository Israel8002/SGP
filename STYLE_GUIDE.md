# 🎨 GUÍA DE ESTILOS - SGE v2

## 📋 PALETA DE COLORES ESTÁNDAR

### Colores Principales
```css
/* Fondo principal */
background: linear-gradient(135deg, #0a0f1c 0%, #1a2332 50%, #2a3441 100%);

/* Cards */
background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);

/* Bordes */
border: 1px solid #1e3a5f;

/* Texto principal */
color: #e2e8f0;

/* Texto secundario */
color: #cbd5e1;

/* Texto gris */
color: #94a3b8;

/* Azul principal */
color: #4fc3f7;
```

### Botones Estándar
```css
/* Botón Primario */
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

/* Botón Secundario */
.btn-secondary {
    background: transparent;
    color: #4fc3f7;
    border: 2px solid #4fc3f7;
}

.btn-secondary:hover {
    background: #4fc3f7;
    color: #0d1421;
}

/* Botón Peligro */
.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
}
```

### Cards Estándar
```css
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
```

### Menú Hamburguesa Estándar
```css
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

/* Menú Móvil */
.nav-menu {
    display: none;
    position: fixed;
    top: 70px;
    right: -280px;
    width: 260px;
    height: calc(100vh - 70px);
    background: linear-gradient(145deg, #0f1419 0%, #1a252f 100%);
    flex-direction: column;
    padding: 1.5rem;
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
```

## 🚀 REGLAS PARA NUEVAS PÁGINAS

### 1. Estructura HTML Obligatoria
```html
<!-- Overlay para cerrar menú móvil -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Header con navegación -->
<header class="header">
    <div class="navbar">
        <a href="dashboard.php" class="logo">SGE v2</a>
        
        <!-- Menú Desktop -->
        <ul class="nav-menu-desktop">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link">Usuarios</a></li>
            <li class="nav-item"><a href="shifts.php" class="nav-link">Turnos</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Reportes</a></li>
        </ul>
        
        <!-- Usuario Desktop -->
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
        
        <!-- Botón Hamburguesa -->
        <button class="hamburger" id="hamburgerBtn">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Menú Móvil -->
        <ul class="nav-menu" id="navMenu">
            <!-- Enlaces del menú -->
            <div class="nav-user">
                <!-- Usuario móvil -->
            </div>
        </ul>
    </div>
</header>
```

### 2. JavaScript Obligatorio
```javascript
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
    
    // Event listeners...
});
```

### 3. CSS Obligatorio
- Usar la paleta de colores estándar
- Incluir todos los estilos del menú hamburguesa
- Aplicar gradientes a cards y botones
- Usar sombras consistentes

## ⚠️ REGLAS IMPORTANTES

1. **NUNCA** cambiar la paleta de colores sin actualizar TODAS las páginas
2. **SIEMPRE** usar la estructura HTML estándar del header
3. **SIEMPRE** incluir el JavaScript del menú hamburguesa
4. **SIEMPRE** usar los estilos de botones estándar
5. **SIEMPRE** aplicar los gradientes a las cards

## 🔧 HERRAMIENTAS

- `standardize_colors.php` - Estandariza colores en todas las páginas
- `STYLE_GUIDE.md` - Esta guía de referencia
- Copiar estilos de `login.php` como base

## 📱 RESPONSIVE

- Desktop: Menú horizontal + usuario a la derecha
- Móvil: Botón hamburguesa + menú lateral deslizante
- Breakpoint: 768px

