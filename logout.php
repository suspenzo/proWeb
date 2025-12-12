<?php
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión completamente
session_destroy();

// Redirigir al login o página inicial
header("Location: index.php");
exit;
