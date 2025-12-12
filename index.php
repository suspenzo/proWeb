<?php
session_start();
include("conexion.php"); // conexión PDO
include("enviar-email.php");


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];
    $mensaje = "";

    // Buscar usuario
    $sql = $conexion->prepare("SELECT * FROM usuarios WHERE username = :u");
    $sql->bindParam(":u", $username);
    $sql->execute();

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || $password != $usuario["password"]) {
        $mensaje = !$usuario ? "Usuario no encontrado" : "Contraseña incorrecta";
    } else {

        if ($usuario["verificado"] == 0) {

        // Generar token
        $token = bin2hex(random_bytes(32));

        // Guardarlo en la BD
        $update = $conexion->prepare("
            UPDATE usuarios SET token = :t 
            WHERE id = :id
        ");
        $update->bindParam(":t", $token);
        $update->bindParam(":id", $usuario["id"]);
        $update->execute();

        // Enviar correo
        enviarCorreoVerificacion($usuario["email"], $token);

        $mensaje =  "Se envió un enlace de verificación a tu correo.";
   
    } else {
    
        // Si está verificado → permitir acceso
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["username"]  = $usuario["username"];

        $mensaje = "Inicio de sesión correcto. Bienvenido.";

        if ($usuario["rol_id"] === 1) {
            header("Location: home.php");
        } else {
            header("Location: home2.php");
        }
        
        exit;
    }


    }

    // Si NO está verificado → enviar token
    

}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
    <title>ProWeb</title>
</head>


    
<body>
    <header class="header">
    <div class="header-left">
        <a href="index.php" class="logo">ProWeb</a>
    </div>
</header>
    <div class= "centrado">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>

        <!-- Mensaje PHP -->
        <div class="mensaje">
            <?php echo $mensaje ?>
        </div>

        <form action="" method="post">

            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Iniciar Sesión</button>

        </form>
    </div>
    </div>

</body>
</html>
