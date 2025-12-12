<?php
include("conexion.php");  // conexión en PDO
session_start();

$mensaje = "";
$usuarioEditar = null;

/* =====================================================
   1. REGISTRO / ACTUALIZACIÓN
===================================================== */
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $id_usuario = $_POST["id"] ?? "";
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $rol_id = $_POST["rol_id"];
    $estado = isset($_POST["estado"]) ? 1 : 0;

    if($id_usuario == "")
    {
        /* ----------------------------------------------
           CREAR NUEVO USUARIO
        ---------------------------------------------- */
        $sql = $conexion->prepare("
            INSERT INTO usuarios (username, password, email, estado, rol_id)
            VALUES (:username, :password, :email, :estado, :rol_id)
        ");

        $sql->execute([
            ":username" => $username,
            ":password" => $password,
            ":email" => $email,
            ":estado" => $estado,
            ":rol_id" => $rol_id
        ]);

        $mensaje = "Usuario creado correctamente";
    }
    else
    {
        /* ----------------------------------------------
           EDITAR USUARIO
        ---------------------------------------------- */
        $sql = $conexion->prepare("
            UPDATE usuarios
            SET username = :username,
                password = :password,
                email = :email,
                estado = :estado,
                rol_id = :rol_id
            WHERE id = :id
        ");

        $sql->execute([
            ":id" => $id_usuario,
            ":username" => $username,
            ":password" => $password,
            ":email" => $email,
            ":estado" => $estado,
            ":rol_id" => $rol_id
        ]);

        $mensaje = "Usuario modificado correctamente";
    }
}

/* =====================================================
   2. CARGAR USUARIO A EDITAR
===================================================== */
if(isset($_GET["editar"]))
{
    $idEditar = (int)$_GET["editar"];

    $sql = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id");
    $sql->execute([":id" => $idEditar]);

    $usuarioEditar = $sql->fetch(PDO::FETCH_ASSOC);
}

/* =====================================================
   3. LISTA DE ROLES
===================================================== */
$sqlRoles = $conexion->query("SELECT * FROM roles ORDER BY rol");
$roles = $sqlRoles->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   4. LISTA DE USUARIOS
===================================================== */
$sqlUsuarios = $conexion->query("
    SELECT u.id, u.username, u.email, u.estado, r.rol AS rol
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    ORDER BY u.id
");
$usuarios = $sqlUsuarios->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
    <?php include("header.php"); ?>
    <div class="centrado">
    <div class="page-container">

        <a href="home.php" class="back-link">← Volver al inicio</a>
        <h1>Gestión de Usuarios</h1>

        <!-- Mensaje -->
        <?php if($mensaje != ""): ?>
            <div class="mensaje"><?php echo $mensaje ?></div>
        <?php endif; ?>

        <!-- LISTA DE USUARIOS -->
        <h2>Lista de Usuarios</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Nick</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>

            <?php if(!empty($usuarios)): ?>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario["id"] ?></td>
                        <td><?php echo $usuario["username"] ?></td>
                        <td><?php echo $usuario["email"] ?></td>
                        <td><?php echo $usuario["rol"] ?></td>
                        <td><?php echo $usuario["estado"] == 1 ? "Sí" : "No" ?></td>
                        <td>
                            <a class="btn" href="usuarios.php?editar=<?php echo $usuario["id"] ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- FORMULARIO: CREAR / EDITAR -->
        <h2><?php echo $usuarioEditar ? "Editar Usuario" : "Nuevo Usuario"; ?></h2>

        <form method="POST" action="usuarios.php">

            <?php if($usuarioEditar): ?>
                <input type="hidden" name="id" value="<?php echo $usuarioEditar['id']; ?>">
            <?php endif; ?>

            <div class="campo">
                <label>Nick</label>
                <input type="text" name="username" required
                       value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['username']) : ''; ?>">
            </div>

            <div class="campo">
                <label>Email</label>
                <input type="email" name="email" required
                       value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['email']) : ''; ?>">
            </div>

            <div class="campo">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>

            <div class="campo">
                <label>Rol</label>
                <select name="rol_id" required>
                    <?php foreach($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>"
                            <?php echo ($usuarioEditar && $usuarioEditar['rol_id'] == $rol['id']) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($rol['rol']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label>Activo</label>
                <input type="checkbox" name="estado"
                    <?php echo (!$usuarioEditar || $usuarioEditar['estado'] == 1) ? "checked" : ""; ?>>
            </div>

            <div class="acciones">
                <button type="submit">
                    <?php echo $usuarioEditar ? "Guardar Cambios" : "Crear Usuario"; ?>
                </button>

                <?php if($usuarioEditar): ?>
                    <a class="btn" href="usuarios.php">Cancelar</a>
                <?php endif; ?>
            </div>

        </form>

    </div>
    </div>

</body>

</html>
