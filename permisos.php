<?php
session_start();
require 'conexion.php'; // conexión en PDO

$mensaje = "";

/* =====================================================
   1. GUARDAR (CREAR / EDITAR)
===================================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_permiso  = $_POST["id"] ?? "";
    $permiso     = $_POST["permiso"];
    $descripcion = $_POST["descripcion"];
    $estado      = isset($_POST["estado"]) ? 1 : 0;

    if ($id_permiso == "") {

        // CREAR PERMISO
        $sql = $conexion->prepare("
            INSERT INTO permisos (permiso, descripcion, estado)
            VALUES (:permiso, :descripcion, :estado)
        ");

        $sql->execute([
            ":permiso" => $permiso,
            ":descripcion" => $descripcion,
            ":estado" => $estado
        ]);

        $mensaje = "Permiso creado correctamente.";
    }
    else {

        // EDITAR PERMISO
        $sql = $conexion->prepare("
            UPDATE permisos
            SET permiso = :permiso,
                descripcion = :descripcion,
                estado = :estado
            WHERE id = :id
        ");

        $sql->execute([
            ":id" => $id_permiso,
            ":permiso" => $permiso,
            ":descripcion" => $descripcion,
            ":estado" => $estado
        ]);

        $mensaje = "Permiso actualizado correctamente.";
    }
}

/* =====================================================
   2. CARGAR PERMISO A EDITAR
===================================================== */
$permisoEditar = null;

if (isset($_GET["editar"])) {

    $idEditar = (int)$_GET["editar"];

    $sql = $conexion->prepare("SELECT * FROM permisos WHERE id = :id");
    $sql->execute([":id" => $idEditar]);

    $permisoEditar = $sql->fetch(PDO::FETCH_ASSOC);
}

/* =====================================================
   3. LISTA DE PERMISOS
===================================================== */
$sqlPermisos = $conexion->query("SELECT * FROM permisos ORDER BY id");
$permisos = $sqlPermisos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Permisos</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<?php include("header.php"); ?>

<div class="centrado">
<div class="caja">

    <a href="home.php" class="back-link">← Volver al inicio</a>

    <h1>Gestión de Permisos</h1>

    <?php if ($mensaje != ""): ?>
        <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- LISTA DE PERMISOS -->
    <h2>Lista de Permisos</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Permiso</th>
            <th>Descripción</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>

        <?php if (!empty($permisos)): ?>
            <?php foreach ($permisos as $p): ?>
                <tr>
                    <td><?= $p["id"]; ?></td>
                    <td><?= htmlspecialchars($p["permiso"]); ?></td>
                    <td><?= htmlspecialchars($p["descripcion"]); ?></td>
                    <td><?= $p["estado"] ? "Sí" : "No"; ?></td>
                    <td>
                        <a class="btn" href="permisos.php?editar=<?= $p["id"]; ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php else: ?>
            <tr><td colspan="5">No hay permisos registrados.</td></tr>
        <?php endif; ?>

    </table>

    <!-- FORMULARIO NUEVO / EDITAR -->
    <h2><?= $permisoEditar ? "Editar Permiso" : "Nuevo Permiso"; ?></h2>

    <form method="POST" action="permisos.php">

        <?php if ($permisoEditar): ?>
            <input type="hidden" name="id" value="<?= $permisoEditar["id"]; ?>">
        <?php endif; ?>

        <div class="campo">
            <label>Permiso:</label>
            <input type="text" name="permiso" required
                   value="<?= $permisoEditar ? htmlspecialchars($permisoEditar["permiso"]) : ""; ?>">
        </div>

        <div class="campo">
            <label>Descripción:</label>
            <textarea name="descripcion"><?= $permisoEditar ? htmlspecialchars($permisoEditar["descripcion"]) : ""; ?></textarea>
        </div>

        <div class="campo">
            <label>Activo:</label>
            <input type="checkbox" name="estado"
                   <?= (!$permisoEditar || $permisoEditar["estado"] == 1) ? "checked" : ""; ?>>
        </div>

        <div class="acciones">
            <button type="submit">
                <?= $permisoEditar ? "Guardar cambios" : "Crear permiso"; ?>
            </button>

            <?php if ($permisoEditar): ?>
                <a class="btn" href="permisos.php">Cancelar</a>
            <?php endif; ?>
        </div>

    </form>

</div>
</div>

</body>
</html>
