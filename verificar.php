<?php
session_start();
include("conexion.php"); 

// Activar errores de PDO (si no lo tienes ya en tu conexión)
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// verificar que el token exista en la URL
if (!isset($_GET["token"]) || empty($_GET["token"])) {
    die("Token no proporcionado");
}

$token = $_GET["token"];

// buscar al usuario con ese token
try {
    $sql = $conexion->prepare("SELECT * FROM usuarios WHERE token = :t LIMIT 1");
    $sql->bindParam(":t", $token);
    $sql->execute();

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error SQL al buscar usuario: " . $e->getMessage());
}

// validar que exista
if (!$usuario) {
    die("Token inválido o expirado");
}

// actualizar usuario como verificado
try {
    $update = $conexion->prepare("
        UPDATE usuarios
        SET verificado = 1,
            token = NULL
        WHERE id = :id
    ");

    $update->bindParam(":id", $usuario["id"]);
    $update->execute();

} catch (Exception $e) {
    die("Error SQL al actualizar usuario: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cuenta verificada</title>
</head>
<body>

<h2>✅ Cuenta verificada</h2>
<p>Tu correo ha sido verificado correctamente. Ya puedes continuar.</p>

<a href="home.php" class="btn">Continuar</a>

</body>
</html>