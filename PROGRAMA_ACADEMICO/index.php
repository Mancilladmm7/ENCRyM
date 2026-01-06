<?php
session_start();
include_once '../funciones.php';
include_once '../conexion.php';  


// =========================
// GUARDAR NUEVO PROGRAMA
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === "nuevo_programa") {
    $sql = "INSERT INTO programa_academico (nombre, responsable, estatus)
            VALUES ($1, $2, $3)";
    
    $params = array(
        $_POST['nombre'],
        $_POST['responsable'],
        $_POST['estatus']
    );

    $result = pg_query_params($conexion, $sql, $params);

    echo $result 
        ? "<script>alert('Programa registrado correctamente'); window.location='index.php';</script>"
        : "<script>alert('Error al registrar el programa');</script>";
}


// =========================
// BUSCAR PROGRAMA
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === "buscar_programa") {
    $nombre = "%" . $_POST['nombre_buscar'] . "%";
    $sql = "SELECT * FROM programa_academico WHERE nombre ILIKE $1";
    $resultado = pg_query_params($conexion, $sql, array($nombre));
    $programa_encontrado = pg_fetch_assoc($resultado);
}


// =========================
// EDITAR PROGRAMA
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === "editar_programa") {
    $sql = "UPDATE programa_academico 
            SET nombre = $1, responsable = $2, estatus = $3
            WHERE id = $4";

    $params = array(
        $_POST['nombre'],
        $_POST['responsable'],
        $_POST['estatus'],
        $_POST['id']
    );

    $result = pg_query_params($conexion, $sql, $params);

    echo $result
        ? "<script>alert('Programa actualizado correctamente'); window.location='index.php';</script>"
        : "<script>alert('Error al actualizar');</script>";
}


// =========================
// ELIMINAR PROGRAMA
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $_POST['accion'] === "eliminar_programa") {
    $id = $_POST['id_programa'];
    $sql = "DELETE FROM programa_academico WHERE id = $1";

    $result = pg_query_params($conexion, $sql, array($id));

    echo $result
        ? "<script>alert('Programa eliminado'); window.location='index.php';</script>"
        : "<script>alert('No se pudo eliminar');</script>";
}

pg_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programa Académico</title>
    <link rel="stylesheet" href="../styles.css">

    <style>
        .page-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .card h2 {
            text-align: center;
            margin-bottom: 15px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        .danger {
            background: #e74c3c !important;
        }
    </style>
</head>
<body>

<div class="programa_academico-page page-container">

    <header class="programa_academico-header">
            <div>
                <h1>Gestión de Programas Académicos</h1>
            </div>
            <div class="programa_academico-right">
                <span>Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="../menu.php" class="btn-link">Volver al menú</a>
            </div>
        </header>


    <!-- ================= NUEVO PROGRAMA ================= -->
    <div class="card">
        <h2>Registrar nuevo programa</h2>

        <form method="POST">
            <input type="hidden" name="accion" value="nuevo_programa">

            <div class="grid-2">
                <div>
                    <label>Nombre del programa</label>
                    <input type="text" name="nombre" required>
                </div>

                <div>
                    <label>Responsable</label>
                    <input type="text" name="responsable" required>
                </div>
            </div>

            <label>Estatus</label>
            <select name="estatus">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
            </select>

            <button type="submit" style="margin-top:15px">Guardar Programa</button>
        </form>
    </div>


    <!-- ================= BUSCAR PROGRAMA ================= -->
    <div class="card">
        <h2>Consultar programa académico</h2>

        <form method="POST">
            <input type="hidden" name="accion" value="buscar_programa">

            <label>Nombre (o parte del nombre)</label>
            <input type="text" name="nombre_buscar" required>

            <button type="submit" style="margin-top:10px">Buscar</button>
        </form>

        <?php if (!empty($programa_encontrado)): ?>
            <hr><br>

            <form method="POST">
                <input type="hidden" name="accion" value="editar_programa">
                <input type="hidden" name="id" value="<?php echo $programa_encontrado['id']; ?>">

                <div class="grid-2">
                    <div>
                        <label>Nombre del programa</label>
                        <input type="text" name="nombre" value="<?php echo $programa_encontrado['nombre']; ?>">
                    </div>

                    <div>
                        <label>Responsable</label>
                        <input type="text" name="responsable" value="<?php echo $programa_encontrado['responsable']; ?>">
                    </div>
                </div>

                <label>Estatus</label>
                <select name="estatus">
                    <option <?php if ($programa_encontrado['estatus'] === 'Activo') echo 'selected'; ?>>Activo</option>
                    <option <?php if ($programa_encontrado['estatus'] === 'Inactivo') echo 'selected'; ?>>Inactivo</option>
                </select>

                <button type="submit" style="margin-top:10px">Guardar cambios</button>
            </form>

            <form method="POST" style="margin-top:10px">
                <input type="hidden" name="accion" value="eliminar_programa">
                <input type="hidden" name="id_programa" value="<?php echo $programa_encontrado['id']; ?>">

                <button type="submit" class="danger">Eliminar Programa</button>
            </form>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
