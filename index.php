<?php
/**
 * Página Principal - SGE v2
 * Redirige al login o dashboard según el estado de autenticación
 */

require_once 'autoload.php';

use App\Auth;

$auth = new Auth();

// Si ya está autenticado, redirigir al dashboard
if ($auth->isAuthenticated()) {
    header('Location: dashboard.php');
    exit;
} else {
    // Si no está autenticado, redirigir al login
    header('Location: login.php');
    exit;
}
?>

