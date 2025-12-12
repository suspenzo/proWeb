<?php
require 'conexion.php';
session_start();

$mensaje = "";

/* =========================================================
   1. GUARDAR (CREAR / EDITAR)
========================================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_rol      = $_POST["id_rol"] ?? "";
    $rol         = $_POST["rol"];
    $descripcion = $_POST["descripcion"];
    $estado      = isset($_POST["estado"]) ? 1 : 0;

    // Permisos marcados
    $permisosMarcados = $_POST["permisos"] ?? [];

    if ($id_rol == "") {

        /* ---------- CREAR ---------- */
        $sql = $conexion->prepare("
            INSERT INTO roles (rol, descripcion, estado)
            VALUES (:rol, :descripcion, :estado)
        ");

        $sql->execute([
            ":rol"         => $rol,
            ":descripcion" => $descripcion,
            ":estado"      => $estado
        ]);

        $id_rol = $conexion->lastInsertId();
        $mensaje = "Rol creado correctamente.";
    }
    else {

        /* ---------- EDITAR ---------- */
        $sql = $conexion->prepare("
            UPDATE roles
            SET rol = :rol,
                descripcion = :descripcion,
                estado = :estado
            WHERE id = :id
        ");

        $sql->execute([
            ":id"          => $id_rol,
            ":rol"         => $rol,
            ":descripcion" => $descripcion,
            ":estado"      => $estado
        ]);

        $mensaje = "Rol modificado correctamente.";
    }

    /* ---------- Actualizar permisos del rol ---------- */

    if ($id_rol != "") {

        // Borramos todos los permisos anteriores
        $del = $conexion->prepare("DELETE FROM rol_permiso WHERE id_rol = :id_rol");
        $del->execute([":id_rol" => $id_rol]);

        // Insertamos los nuevos
        $add = $conexion->prepare("
            INSERT INTO rol_permiso (id_rol, id_permiso)
            VALUES (:id_rol, :id_permiso)
        ");

        foreach ($permisosMarcados as $permisoID) {
            $add->execute([
                ":id_rol"     => $id_rol,
                ":id_permiso" => $permisoID
            ]);
        }
    }
}

/* =========================================================
   2. CARGAR ROL A EDITAR
========================================================= */
$rolEditar = null;
$permisosAsignados = [];

if (isset($_GET["editar"])) {

    $idEditar = (int)$_GET["editar"];

    // Traer rol
    $sql = $conexion->prepare("SELECT * FROM roles WHERE id = :id");
    $sql->execute([":id" => $idEditar]);
    $rolEditar = $sql->fetch(PDO::FETCH_ASSOC);

    // Permisos ya asignados
    $sqlPerm = $conexion->prepare("
        SELECT id_permiso
        FROM rol_permiso
        WHERE id_rol = :id
    ");

    $sqlPerm->execute([":id" => $idEditar]);

    while ($row = $sqlPerm->fetch(PDO::FETCH_ASSOC)) {
        $permisosAsignados[] = (int)$row["id_permiso"];
    }
}

/* =========================================================
   3. LISTA DE ROLES CON SUS PERMISOS
========================================================= */

$sqlRoles = $conexion->query("
    SELECT r.id,
           r.rol,
           r.descripcion,
           GROUP_CONCAT(p.permiso ORDER BY p.permiso SEPARATOR ', ') AS permisos
    FROM roles r
    LEFT JOIN rol_permiso rp ON r.id = rp.id_rol
    LEFT JOIN permisos p ON rp.id_permiso = p.id
    GROUP BY r.id, r.rol, r.descripcion
    ORDER BY r.id
");

$listaRoles = $sqlRoles->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   4. LISTA DE PERMISOS PARA LOS CHECKBOX
========================================================= */

$sqlP = $conexion->query("SELECT * FROM permisos ORDER BY permiso");
$permisos = $sqlP->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Roles</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
<?php include("header.php"); ?>
<div class="centrado">
<div class="caja">

    <a href="home.php" class="back-link">← Volver al inicio</a>

    <h1>Gestión de Roles</h1>

    <?php if ($mensaje != ""): ?>
        <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- LISTA DE ROLES -->
    <h2>Lista de Roles</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Rol</th>
            <th>Descripción</th>
            <th>Permisos</th>
            <th>Acciones</th>
        </tr>

        <?php if (count($listaRoles) > 0): ?>
            <?php foreach ($listaRoles as $rol): ?>
                <tr>
                    <td><?= $rol["id"] ?></td>
                    <td><?= htmlspecialchars($rol["rol"]) ?></td>
                    <td><?= htmlspecialchars($rol["descripcion"]) ?></td>
                    <td><?= $rol["permisos"] ?: "Sin permisos" ?></td>

                    <td>
                        <a class="btn" href="roles.php?editar=<?= $rol["id"] ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No hay roles registrados.</td></tr>
        <?php endif; ?>
    </table>

    <!-- FORMULARIO NUEVO / EDITAR -->
    <h2><?= $rolEditar ? "Editar Rol" : "Nuevo Rol" ?></h2>

    <form method="POST" action="roles.php">

        <?php if ($rolEditar): ?>
            <input type="hidden" name="id_rol" value="<?= $rolEditar["id"] ?>">
        <?php endif; ?>

        <div class="campo">
            <label>Rol:</label>
            <input type="text" name="rol" required
                   value="<?= $rolEditar ? htmlspecialchars($rolEditar["rol"]) : "" ?>">
        </div>

        <div class="campo">
            <label>Descripción:</label>
            <textarea name="descripcion"><?= $rolEditar ? htmlspecialchars($rolEditar["descripcion"]) : "" ?></textarea>
        </div>

        <div class="campo">
            <label>Activo:</label>
            <input type="checkbox" name="estado"
                   <?= (!$rolEditar || $rolEditar["estado"] == 1) ? "checked" : "" ?>>
        </div>

        <div class="campo">
            <label>Permisos:</label>
            <?php foreach ($permisos as $p): ?>
                <label>
                    <input type="checkbox" name="permisos[]" value="<?= $p["id"] ?>"
                        <?= in_array((int)$p["id"], $permisosAsignados) ? "checked" : "" ?>>
                    <?= htmlspecialchars($p["permiso"]) ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <div class="acciones">
            <button type="submit">
                <?= $rolEditar ? "Guardar cambios" : "Crear rol" ?>
            </button>

            <?php if ($rolEditar): ?>
                <a class="btn" href="roles.php">Cancelar</a>
            <?php endif; ?>
        </div>

    </form>

</div>
<div class="centrado">

</body>
</html>
