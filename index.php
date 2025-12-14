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

        if ($usuario["rol_id"] == 1) {
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
    <?php include("header.php"); ?>

    <div class="centrado">
    <div class="index-card" id="index-card">

    <!-- PASO 1 -->
    <div id="step1" class="step active">
        <h2>¿Quieres saber qué lenguaje de programación necesitas aprender?</h2>
        <p>Te ayudamos a escoger el camino correcto según tus intereses.</p>
        <button onclick="goToStep(2)">Continuar</button>
    </div>

    <!-- PASO 2 -->
    <div id="step2" class="step">
        <h2>¿Qué área te interesa?</h2>

        <div class="options">
            <label><input type="radio" value="web" onchange="selectArea(this)"> Desarrollo Web</label>
            <label><input type="radio" value="mobile" onchange="selectArea(this)"> Aplicaciones Móviles</label>
            <label><input type="radio" value="ai" onchange="selectArea(this)"> Inteligencia Artificial & Ciencia de Datos</label>
            <label><input type="radio" value="games" onchange="selectArea(this)"> Videojuegos</label>
            <label><input type="radio" value="desktop" onchange="selectArea(this)"> Software de Escritorio y Empresarial</label>
            <label><input type="radio" value="embedded" onchange="selectArea(this)"> Sistemas Embebidos y Hardware</label>
            <label><input type="radio" value="automation" onchange="selectArea(this)"> Automatización y Scripting</label>
        </div>

        <button class="secondary" onclick="goToStep(1)">Volver</button>
    </div>

    <!-- PASO 3 -->
    <div id="step3" class="step">
        <h2>Lenguajes recomendados</h2>

        <div class="options" id="languageOptions"></div>

        <div class="actions">
            <button class="secondary" onclick="goToStep(2)">Volver</button>
            <button id="startBtn" disabled>Empezar a aprender</button>
        </div>
    </div>

</div>
</div>

<script src="script-index.js"></script>

</html>
