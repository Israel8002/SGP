<?php
/**
 * Cerrar Sesión - SGE v2
 */

require_once 'classes/Auth.php';

$auth = new Auth();

// Cerrar sesión
$auth->logout();

// Redirigir al login
header('Location: login.php?message=logout_success');
exit;
?>

