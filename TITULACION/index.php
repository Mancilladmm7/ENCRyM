<?php
session_start();
include_once '../funciones.php';
include_once '../conexion.php';  // trae $conexion


/* ==========================
   1) NUEVA TITULACIÓN
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "nueva_titulacion") {

    $nombre_tesista      = trim($_POST['nombre_tesista']);
    $grado_academico     = trim($_POST['grado_academico']);
    $programa_academico  = trim($_POST['programa_academico']);
    $institucion         = trim($_POST['institucion']);
    $tipo_trabajo        = trim($_POST['tipo_trabajo']);
    $nombre_docente      = trim($_POST['nombre_docente']);
    $tipo_participacion  = trim($_POST['tipo_participacion']);
    $estatus             = trim($_POST['estatus']);

    if (!empty($nombre_tesista) && !empty($grado_academico) && !empty($programa_academico)) {

        $sql = "INSERT INTO titulacion
                (nombre_tesista, grado_academico, programa_academico, institucion,
                 tipo_trabajo, nombre_docente, tipo_participacion, estatus)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8)";

        $params = array(
            $nombre_tesista,
            $grado_academico,
            $programa_academico,
            $institucion,
            $tipo_trabajo,
            $nombre_docente,
            $tipo_participacion,
            $estatus
        );

        $result = pg_query_params($conexion, $sql, $params);

        $mensaje_nuevo = $result
            ? "Titulación guardada correctamente."
            : "Error al guardar la titulación.";
    } else {
        $mensaje_nuevo = "Faltan datos obligatorios (nombre del tesista, grado académico, programa académico).";
    }
}

/* ==========================
   2) BUSCAR TITULACIÓN (por ID o por nombre de tesista)
   ========================== */
$titulacion_encontrada = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "buscar_titulacion") {

    $id_buscar        = trim($_POST['id_buscar'] ?? '');
    $tesista_buscar   = trim($_POST['nombre_tesista_buscar'] ?? '');

    if ($id_buscar === '' && $tesista_buscar === '') {
        $mensaje_buscar = "Ingresa al menos un ID o el nombre del tesista para buscar.";
    } else {
        if ($id_buscar !== '') {
            $sql    = "SELECT * FROM titulacion WHERE id = $1";
            $params = array($id_buscar);
        } else {
            $sql    = "SELECT * FROM titulacion WHERE LOWER(nombre_tesista) LIKE LOWER($1)";
            $params = array('%' . $tesista_buscar . '%');
        }

        $resultado = pg_query_params($conexion, $sql, $params);

        if ($resultado && pg_num_rows($resultado) > 0) {
            $titulacion_encontrada = pg_fetch_assoc($resultado);
        } else {
            $mensaje_buscar = "No se encontró ninguna titulación con esos datos.";
        }
    }
}

/* ==========================
   3) EDITAR TITULACIÓN
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "editar_titulacion") {

    $id                 = $_POST['id'];
    $nombre_tesista     = trim($_POST['nombre_tesista']);
    $grado_academico    = trim($_POST['grado_academico']);
    $programa_academico = trim($_POST['programa_academico']);
    $institucion        = trim($_POST['institucion']);
    $tipo_trabajo       = trim($_POST['tipo_trabajo']);
    $nombre_docente     = trim($_POST['nombre_docente']);
    $tipo_participacion = trim($_POST['tipo_participacion']);
    $estatus            = trim($_POST['estatus']);

    $sql = "UPDATE titulacion SET
                nombre_tesista     = $1,
                grado_academico    = $2,
                programa_academico = $3,
                institucion        = $4,
                tipo_trabajo       = $5,
                nombre_docente     = $6,
                tipo_participacion = $7,
                estatus            = $8
            WHERE id = $9";

    $params = array(
        $nombre_tesista,
        $grado_academico,
        $programa_academico,
        $institucion,
        $tipo_trabajo,
        $nombre_docente,
        $tipo_participacion,
        $estatus,
        $id
    );

    $result = pg_query_params($conexion, $sql, $params);

    $mensaje_buscar = $result
        ? "Titulación actualizada correctamente."
        : "Error al actualizar la titulación.";
}

/* ==========================
   4) ELIMINAR TITULACIÓN
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "eliminar_titulacion") {

    $id_titulacion = $_POST["id_titulacion"];

    $sql = "DELETE FROM titulacion WHERE id = $1";
    $resultado = pg_query_params($conexion, $sql, array($id_titulacion));

    $mensaje_buscar = ($resultado && pg_affected_rows($resultado) > 0)
        ? "Titulación eliminada correctamente."
        : "No se pudo eliminar la titulación (puede que no exista).";

    $titulacion_encontrada = null;
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
    <title>Gestión de Titulación</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            display: block;
        }

        .titulacion-page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 40px;
        }

        .titulacion-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .titulacion-header h1 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }

        .titulacion-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            font-size: 0.9rem;
        }

        .titulacion-header-right span {
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

        .titulacion-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .titulacion-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            width: 100%;
        }

        .titulacion-card h2 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.6rem;
            color: #333;
        }

        .titulacion-card p.subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }

        .titulacion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 12px 16px;
        }

        .titulacion-group {
            display: flex;
            flex-direction: column;
        }

        .titulacion-group label {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #555;
        }

        .titulacion-group input {
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

        .titulacion-actions {
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
    <div class="titulacion-page">

        <!-- Header superior -->
        <header class="titulacion-header">
            <div>
                <h1>Gestión de Titulación</h1>
            </div>
            <div class="titulacion-header-right">
                <span>Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="../menu.php" class="btn-link">Volver al menú</a>
            </div>
        </header>

        <div class="titulacion-cards">

            <!-- TARJETA: NUEVA TITULACIÓN -->
            <?php if (tienePermiso('nuevo')): ?>
            <div class="titulacion-card">
                <h2>Nueva titulación</h2>
                <p class="subtitle">Registra un nuevo proceso de titulación.</p>

                <?php if (!empty($mensaje_nuevo)): ?>
                    <div class="mensaje <?php echo (strpos($mensaje_nuevo, 'correctamente') !== false) ? 'ok' : 'error'; ?>">
                        <?php echo htmlspecialchars($mensaje_nuevo); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="accion" value="nueva_titulacion">

                    <div class="titulacion-grid">
                        <div class="titulacion-group">
                            <label for="nombre_tesista">Nombre del tesista</label>
                            <input type="text" id="nombre_tesista" name="nombre_tesista" required>
                        </div>

                        <div class="titulacion-group">
                            <label for="grado_academico">Grado académico</label>
                            <input type="text" id="grado_academico" name="grado_academico" required>
                        </div>

                        <div class="titulacion-group">
                            <label for="programa_academico">Programa académico</label>
                            <input type="text" id="programa_academico" name="programa_academico" required>
                        </div>

                        <div class="titulacion-group">
                            <label for="institucion">Institución</label>
                            <input type="text" id="institucion" name="institucion">
                        </div>

                        <div class="titulacion-group">
                            <label for="tipo_trabajo">Tipo de trabajo</label>
                            <input type="text" id="tipo_trabajo" name="tipo_trabajo">
                        </div>

                        <div class="titulacion-group">
                            <label for="nombre_docente">Nombre del docente</label>
                            <input type="text" id="nombre_docente" name="nombre_docente">
                        </div>

                        <div class="titulacion-group">
                            <label for="tipo_participacion">Tipo de participación</label>
                            <input type="text" id="tipo_participacion" name="tipo_participacion">
                        </div>

                        <div class="titulacion-group">
                            <label for="estatus">Estatus</label>
                            <input type="text" id="estatus" name="estatus">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Guardar titulación</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- TARJETA: CONSULTAR / EDITAR / ELIMINAR TITULACIÓN -->
            <?php if (tienePermiso('consultar')): ?>
            <div class="titulacion-card">
                <h2>Consultar titulación</h2>
                <p class="subtitle">Busca por ID o por nombre del tesista. Desde aquí puedes editar o eliminar.</p>

                <?php if (!empty($mensaje_buscar)): ?>
                    <div class="mensaje <?php echo (str_contains($mensaje_buscar, 'correctamente') ? 'ok' : 'error'); ?>">
                        <?php echo htmlspecialchars($mensaje_buscar); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de búsqueda -->
                <form method="POST" action="index.php" style="margin-bottom: 15px;">
                    <input type="hidden" name="accion" value="buscar_titulacion">

                    <div class="titulacion-grid">
                        <div class="titulacion-group">
                            <label for="id_buscar">ID de titulación</label>
                            <input type="number" id="id_buscar" name="id_buscar">
                        </div>

                        <div class="titulacion-group">
                            <label for="nombre_tesista_buscar">Nombre del tesista (o parte del nombre)</label>
                            <input type="text" id="nombre_tesista_buscar" name="nombre_tesista_buscar">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Buscar titulación</button>
                </form>

                <!-- Si se encontró una titulación -->
                <?php if ($titulacion_encontrada): ?>
                    <hr style="margin: 15px 0;">

                    <!-- Formulario para EDITAR -->
                    <form method="POST" action="index.php">
                        <input type="hidden" name="accion" value="editar_titulacion">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($titulacion_encontrada['id']); ?>">

                        <div class="titulacion-grid">
                            <div class="titulacion-group">
                                <label>ID</label>
                                <input type="text" value="<?php echo htmlspecialchars($titulacion_encontrada['id']); ?>" disabled>
                            </div>

                            <div class="titulacion-group">
                                <label>Nombre del tesista</label>
                                <input type="text" name="nombre_tesista" value="<?php echo htmlspecialchars($titulacion_encontrada['nombre_tesista']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Grado académico</label>
                                <input type="text" name="grado_academico" value="<?php echo htmlspecialchars($titulacion_encontrada['grado_academico']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Programa académico</label>
                                <input type="text" name="programa_academico" value="<?php echo htmlspecialchars($titulacion_encontrada['programa_academico']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Institución</label>
                                <input type="text" name="institucion" value="<?php echo htmlspecialchars($titulacion_encontrada['institucion']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Tipo de trabajo</label>
                                <input type="text" name="tipo_trabajo" value="<?php echo htmlspecialchars($titulacion_encontrada['tipo_trabajo']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Nombre del docente</label>
                                <input type="text" name="nombre_docente" value="<?php echo htmlspecialchars($titulacion_encontrada['nombre_docente']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Tipo de participación</label>
                                <input type="text" name="tipo_participacion" value="<?php echo htmlspecialchars($titulacion_encontrada['tipo_participacion']); ?>">
                            </div>

                            <div class="titulacion-group">
                                <label>Estatus</label>
                                <input type="text" name="estatus" value="<?php echo htmlspecialchars($titulacion_encontrada['estatus']); ?>">
                            </div>
                        </div>

                        <div class="titulacion-actions">
                            <button type="submit" class="btn-primary">Guardar cambios</button>
                        </div>
                    </form>

                    <!-- Formulario para ELIMINAR -->
                    <?php if (tienePermiso('eliminar')): ?>
                    <form method="POST" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta titulación?');">
                        <input type="hidden" name="accion" value="eliminar_titulacion">
                        <input type="hidden" name="id_titulacion" value="<?php echo htmlspecialchars($titulacion_encontrada['id']); ?>">

                        <div class="titulacion-actions">
                            <button type="submit" class="btn-danger">Eliminar titulación</button>
                        </div>
                    </form>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
            <?php endif; ?>

        </div><!-- .titulacion-cards -->

    </div><!-- .titulacion-page -->
</body>
</html>
