<?php

/**
 * Responsabilidad: Redirección de compatibilidad hacia las pantallas oficiales de inicio de sesión y registro.
 */

// Compatibilidad temporal con enlaces antiguos.
// Las pantallas oficiales son login.php y registro.php.
$modo = ($_GET['modo'] ?? '') === 'registro' ? 'registro' : 'login';
header('Location: ' . ($modo === 'registro' ? 'registro.php' : 'login.php'));
exit;
