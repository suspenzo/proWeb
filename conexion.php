<?php
$servidor = "localhost";
$usuario = "xzutzvwx_proweb";
$clave = "R9CHRMAE2Y4Q3APWEpke";
$bd = "xzutzvwx_proweb";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$bd;charset=utf8", $usuario, $clave);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
