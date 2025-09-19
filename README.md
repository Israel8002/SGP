# SGE v2 - Sistema de Gestión de Personal

Sistema web de gestión de personal desarrollado en PHP con interfaz responsive y tema oscuro azulado.

## Características

- **Autenticación de usuarios** con roles (SuperAdmin, Admin, User)
- **Gestión de usuarios** con asignación de turnos
- **Gestión de turnos** (shifts)
- **Perfil de usuario** personalizable
- **Reportes del sistema**
- **Autorizaciones** (en desarrollo)
- **Interfaz responsive** con menú hamburguesa en móviles
- **Tema oscuro azulado** profesional

## Tecnologías

- **Backend**: PHP 7.4+
- **Base de datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Estilos**: CSS inline con diseño responsive
- **Autenticación**: Sistema de sesiones PHP

## Instalación

### Instalación Local

1. Clona el repositorio:
```bash
git clone https://github.com/Israel8002/SGP.git
cd SGP
```

2. Configura la base de datos:
   - Crea una base de datos MySQL
   - Importa el archivo `database/schema.sql`
   - Configura las credenciales en `config/database.php`

3. Configura el servidor web:
   - Asegúrate de que PHP 7.4+ esté instalado
   - Configura el directorio raíz del proyecto
   - Habilita las extensiones PHP necesarias (PDO, MySQL)

4. Accede al sistema:
   - Navega a `http://localhost/sge-v2`
   - Usa las credenciales del SuperAdmin

### Despliegue en Vercel

1. **Conecta tu repositorio** a Vercel desde GitHub
2. **Configuración automática**:
   - Framework Preset: **Other**
   - Root Directory: **.** (vacío)
   - Build Command: **vacío**
   - Output Directory: **.** (vacío)
   - Install Command: **vacío**

3. **Variables de entorno** (opcional):
   - Configura las credenciales de base de datos en Vercel
   - O usa una base de datos externa como PlanetScale o Railway

4. **Despliegue**:
   - Vercel detectará automáticamente los archivos PHP
   - El sistema estará disponible en `https://tu-proyecto.vercel.app`

## Estructura del Proyecto

```
sge-v2/
├── classes/
│   └── Auth.php              # Clase de autenticación
├── config/
│   └── database.php          # Configuración de base de datos
├── database/
│   └── schema.sql            # Esquema de base de datos
├── login.php                 # Página de inicio de sesión
├── dashboard.php             # Panel principal
├── users.php                 # Gestión de usuarios
├── profile.php               # Perfil de usuario
├── shifts.php                # Gestión de turnos
├── reports.php               # Reportes del sistema
├── authorizations.php        # Autorizaciones
└── STYLE_GUIDE.md           # Guía de estilos
```

## Uso

### SuperAdmin
- Acceso completo a todas las funcionalidades
- Gestión de usuarios y turnos
- Visualización de reportes

### Admin
- Gestión de usuarios (excepto SuperAdmin)
- Visualización de reportes

### User
- Acceso a perfil personal
- Visualización de información básica

## Desarrollo

### Guía de Estilos
Consulta `STYLE_GUIDE.md` para mantener consistencia en el diseño.

### Colores Principales
- **Fondo principal**: `#0f1419`
- **Fondo secundario**: `#1a252f`
- **Azul primario**: `#1e3a5f`
- **Azul secundario**: `#2a4a6b`
- **Texto**: `#e2e8f0`

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crea un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## Soporte

Para soporte técnico o reportar bugs, crea un issue en el repositorio de GitHub.
