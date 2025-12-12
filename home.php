<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos.css">
    <title>Sistema de Gestión de Usuarios</title>
</head>

<body>
<?php include("header.php"); ?>
<div class="centrado">
    <div class="menu-container">
        <h1>Sistema de Gestión de Usuarios</h1>

        <div class="menu-links">
            <a href="usuarios.php">Usuarios</a>
            <a href="roles.php">Roles</a>
            <a href="permisos.php">Permisos</a>
        </div>
    </div>
    </div>

</body>

</html>
