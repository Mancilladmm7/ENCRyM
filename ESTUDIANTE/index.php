<?php
session_start();
include_once '../funciones.php';
include_once '../conexion.php';  // trae $conexion


/* ==========================
   1) NUEVO ESTUDIANTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "nuevo_estudiante") {

    $matricula          = trim($_POST["matricula"]);
    $nombres            = trim($_POST["nombres"]);
    $primer_apellido    = trim($_POST["primer_apellido"]);
    $segundo_apellido   = trim($_POST["segundo_apellido"] ?? '');
    $curp               = trim($_POST["curp"] ?? '');
    $correo_electronico = trim($_POST["correo_electronico"] ?? '');
    $estatus            = trim($_POST["estatus"] ?? '');

    if (!empty($matricula) && !empty($nombres) && !empty($primer_apellido)) {
        $sql = "INSERT INTO estudiante 
                (matricula, nombres, primer_apellido, segundo_apellido, curp, correo_electronico, estatus)
                VALUES ($1,$2,$3,$4,$5,$6,$7)";

        $params = array(
            $matricula, $nombres, $primer_apellido,
            $segundo_apellido, $curp, $correo_electronico, $estatus
        );

        $resultado = pg_query_params($conexion, $sql, $params);

        $mensaje_nuevo = $resultado
            ? "Estudiante guardado exitosamente."
            : "Error al guardar el estudiante.";
    } else {
        $mensaje_nuevo = "Faltan campos obligatorios (matrícula, nombres, primer apellido).";
    }
}

/* ==========================
   2) BUSCAR ESTUDIANTE (por matrícula o nombre)
   ========================== */
$estudiante_encontrado = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "buscar_estudiante") {

    $busca_matricula = trim($_POST['matricula_buscar'] ?? '');
    $busca_nombre    = trim($_POST['nombre_buscar'] ?? '');

    if ($busca_matricula === '' && $busca_nombre === '') {
        $mensaje_buscar = "Ingresa al menos matrícula o nombre para buscar.";
    } else {
        if ($busca_matricula !== '') {
            // Buscar por matrícula exacta
            $sql    = "SELECT * FROM estudiante WHERE matricula = $1";
            $params = array($busca_matricula);
        } else {
            // Buscar por nombre (coincidencia parcial)
            $sql    = "SELECT * FROM estudiante WHERE LOWER(nombres) LIKE LOWER($1)";
            $params = array('%' . $busca_nombre . '%');
        }

        $resultado = pg_query_params($conexion, $sql, $params);

        if ($resultado && pg_num_rows($resultado) > 0) {
            $estudiante_encontrado = pg_fetch_assoc($resultado);
        } else {
            $mensaje_buscar = "No se encontró ningún estudiante con esos datos.";
        }
    }
}

/* ==========================
   3) EDITAR ESTUDIANTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "editar_estudiante") {

    $matricula          = $_POST['matricula'];
    $nombres            = trim($_POST["nombres"]);
    $primer_apellido    = trim($_POST["primer_apellido"]);
    $segundo_apellido   = trim($_POST["segundo_apellido"] ?? '');
    $curp               = trim($_POST["curp"] ?? '');
    $correo_electronico = trim($_POST["correo_electronico"] ?? '');
    $estatus            = trim($_POST["estatus"] ?? '');

    $sql = "UPDATE estudiante SET 
                nombres = $1,
                primer_apellido = $2,
                segundo_apellido = $3,
                curp = $4,
                correo_electronico = $5,
                estatus = $6
            WHERE matricula = $7";

    $params = array(
        $nombres, $primer_apellido, $segundo_apellido,
        $curp, $correo_electronico, $estatus, $matricula
    );

    $resultado = pg_query_params($conexion, $sql, $params);

    $mensaje_buscar = $resultado
        ? "Estudiante actualizado correctamente."
        : "Error al actualizar el estudiante.";
}

/* ==========================
   4) ELIMINAR ESTUDIANTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "eliminar_estudiante") {

    $matricula = $_POST["matricula"];

    $sql = "DELETE FROM estudiante WHERE matricula = $1";
    $resultado = pg_query_params($conexion, $sql, array($matricula));

    $mensaje_buscar = ($resultado && pg_affected_rows($resultado) > 0)
        ? "Estudiante eliminado correctamente."
        : "No se pudo eliminar el estudiante (puede que no exista).";

    $estudiante_encontrado = null;
}

// Cerrar conexión
if (isset($conexion) && $conexion) {
    pg_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Estudiantes</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Quitamos el flex del login para esta página */
        body {
            display: block;
        }

        .estudiante-page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 40px;
        }

        .estudiante-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .estudiante-header h1 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }

        .estudiante-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            font-size: 0.9rem;
        }

        .estudiante-header-right span {
            color: #333;
        }

        .btn-link {
            padding: 8px 16px;
            background-color: #e74c3c;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-link:hover {
            background-color: #c0392b;
        }

        .estudiante-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .estudiante-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            width: 100%;
        }

        .estudiante-card h2 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.6rem;
            color: #333;
        }

        .estudiante-card p.subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }

        .estudiante-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 12px 16px;
        }

        .estudiante-group {
            display: flex;
            flex-direction: column;
        }

        .estudiante-group label {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #555;
        }

        .estudiante-group input {
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
        }

        .btn-primary,
        .btn-danger {
            margin-top: 15px;
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            color: #fff;
        }

        .btn-primary {
            background-color: #3498db;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }

        .estudiante-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .mensaje {
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .mensaje.ok {
            color: #27ae60;
        }
        .mensaje.error {
            color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="estudiante-page">

        <!-- Header superior -->
        <header class="estudiante-header">
            <div>
                <h1>Gestión de Estudiantes</h1>
            </div>
            <div class="estudiante-header-right">
                <span>Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="../menu.php" class="btn-link">Volver al menú</a>
            </div>
        </header>

        <div class="estudiante-cards">

            <!-- TARJETA: NUEVO ESTUDIANTE -->
            <?php if (tienePermiso('nuevo')): ?>
            <div class="estudiante-card">
                <h2>Nuevo estudiante</h2>
                <p class="subtitle">Registra un nuevo estudiante en el sistema.</p>

                <?php if (!empty($mensaje_nuevo)): ?>
                    <div class="mensaje <?php echo (strpos($mensaje_nuevo, 'exitosamente') !== false) ? 'ok' : 'error'; ?>">
                        <?php echo htmlspecialchars($mensaje_nuevo); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="accion" value="nuevo_estudiante">

                    <div class="estudiante-grid">
                        <div class="estudiante-group">
                            <label for="matricula">Matrícula</label>
                            <input type="text" id="matricula" name="matricula" required>
                        </div>

                        <div class="estudiante-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" required>
                        </div>

                        <div class="estudiante-group">
                            <label for="primer_apellido">Primer apellido</label>
                            <input type="text" id="primer_apellido" name="primer_apellido" required>
                        </div>

                        <div class="estudiante-group">
                            <label for="segundo_apellido">Segundo apellido</label>
                            <input type="text" id="segundo_apellido" name="segundo_apellido">
                        </div>

                        <div class="estudiante-group">
                            <label for="curp">CURP</label>
                            <input type="text" id="curp" name="curp">
                        </div>

                        <div class="estudiante-group">
                            <label for="correo_electronico">Correo electrónico</label>
                            <input type="email" id="correo_electronico" name="correo_electronico">
                        </div>

                        <div class="estudiante-group">
                            <label for="estatus">Estatus</label>
                            <input type="text" id="estatus" name="estatus">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Guardar estudiante</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- TARJETA: CONSULTAR / EDITAR / ELIMINAR ESTUDIANTE -->
            <?php if (tienePermiso('consultar')): ?>
            <div class="estudiante-card">
                <h2>Consultar estudiante</h2>
                <p class="subtitle">Busca por matrícula o por nombre. Desde aquí puedes editar o eliminar.</p>

                <?php if (!empty($mensaje_buscar)): ?>
                    <div class="mensaje <?php echo (str_contains($mensaje_buscar, 'correctamente') ? 'ok' : 'error'); ?>">
                        <?php echo htmlspecialchars($mensaje_buscar); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de búsqueda -->
                <form method="POST" action="index.php" style="margin-bottom: 15px;">
                    <input type="hidden" name="accion" value="buscar_estudiante">

                    <div class="estudiante-grid">
                        <div class="estudiante-group">
                            <label for="matricula_buscar">Matrícula</label>
                            <input type="text" id="matricula_buscar" name="matricula_buscar">
                        </div>

                        <div class="estudiante-group">
                            <label for="nombre_buscar">Nombre (o parte del nombre)</label>
                            <input type="text" id="nombre_buscar" name="nombre_buscar">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Buscar estudiante</button>
                </form>

                <!-- Si se encontró un estudiante -->
                <?php if ($estudiante_encontrado): ?>
                    <hr style="margin: 15px 0;">

                    <!-- Formulario para EDITAR -->
                    <form method="POST" action="index.php">
                        <input type="hidden" name="accion" value="editar_estudiante">
                        <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($estudiante_encontrado['matricula']); ?>">

                        <div class="estudiante-grid">
                            <div class="estudiante-group">
                                <label>Matrícula</label>
                                <input type="text" value="<?php echo htmlspecialchars($estudiante_encontrado['matricula']); ?>" disabled>
                            </div>

                            <div class="estudiante-group">
                                <label>Nombres</label>
                                <input type="text" name="nombres" value="<?php echo htmlspecialchars($estudiante_encontrado['nombres']); ?>">
                            </div>

                            <div class="estudiante-group">
                                <label>Primer apellido</label>
                                <input type="text" name="primer_apellido" value="<?php echo htmlspecialchars($estudiante_encontrado['primer_apellido']); ?>">
                            </div>

                            <div class="estudiante-group">
                                <label>Segundo apellido</label>
                                <input type="text" name="segundo_apellido" value="<?php echo htmlspecialchars($estudiante_encontrado['segundo_apellido']); ?>">
                            </div>

                            <div class="estudiante-group">
                                <label>CURP</label>
                                <input type="text" name="curp" value="<?php echo htmlspecialchars($estudiante_encontrado['curp']); ?>">
                            </div>

                            <div class="estudiante-group">
                                <label>Correo electrónico</label>
                                <input type="email" name="correo_electronico" value="<?php echo htmlspecialchars($estudiante_encontrado['correo_electronico']); ?>">
                            </div>

                            <div class="estudiante-group">
                                <label>Estatus</label>
                                <input type="text" name="estatus" value="<?php echo htmlspecialchars($estudiante_encontrado['estatus']); ?>">
                            </div>
                        </div>

                        <div class="estudiante-actions">
                            <button type="submit" class="btn-primary">Guardar cambios</button>
                        </div>
                    </form>

                    <!-- Formulario para ELIMINAR -->
                    <?php if (tienePermiso('eliminar')): ?>
                    <form method="POST" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar este estudiante?');">
                        <input type="hidden" name="accion" value="eliminar_estudiante">
                        <input type="hidden" name="matricula" value="<?php echo htmlspecialchars($estudiante_encontrado['matricula']); ?>">

                        <div class="estudiante-actions">
                            <button type="submit" class="btn-danger">Eliminar estudiante</button>
                        </div>
                    </form>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
            <?php endif; ?>

        </div><!-- .estudiante-cards -->

    </div><!-- .estudiante-page -->
</body>
</html>
