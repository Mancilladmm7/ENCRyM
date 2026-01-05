<?php
session_start();
include_once '../funciones.php';
include_once '../conexion.php';  



/* ==========================
   1) NUEVO DOCENTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "nuevo_docente") {

    $numero_empleado    = trim($_POST["numero_empleado"]);
    $nombres            = trim($_POST["nombres"]);
    $primer_apellido    = trim($_POST["primer_apellido"]);
    $segundo_apellido   = trim($_POST["segundo_apellido"] ?? '');
    $curp               = trim($_POST["curp"] ?? '');
    $rfc                = trim($_POST["rfc"] ?? '');
    $tipo_contratacion  = trim($_POST["tipo_contratacion"] ?? '');
    $asignacion_area    = trim($_POST["asignacion_area"] ?? '');
    $correo_electronico = trim($_POST["correo_electronico"] ?? '');
    $telefono           = trim($_POST["telefono"] ?? '');
    $estatus            = trim($_POST["estatus"] ?? '');

    if (!empty($numero_empleado) && !empty($nombres) && !empty($primer_apellido)) {
        $sql = "INSERT INTO docente 
                (numero_empleado, nombres, primer_apellido, segundo_apellido, curp, rfc, 
                 tipo_contratacion, asignacion_area, correo_electronico, telefono, estatus)
                VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11)";

        $params = array(
            $numero_empleado, $nombres, $primer_apellido, $segundo_apellido,
            $curp, $rfc, $tipo_contratacion, $asignacion_area,
            $correo_electronico, $telefono, $estatus
        );

        $resultado = pg_query_params($conexion, $sql, $params);

        $mensaje_nuevo = $resultado
            ? "Docente guardado exitosamente."
            : "Error al guardar el docente.";
    } else {
        $mensaje_nuevo = "Faltan campos obligatorios (número de empleado, nombres, primer apellido).";
    }
}

/* ==========================
   2) BUSCAR DOCENTE (por matrícula o nombre)
   ========================== */
$docente_encontrado = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "buscar_docente") {

    $busca_matricula = trim($_POST['numero_empleado_buscar'] ?? '');
    $busca_nombre    = trim($_POST['nombre_buscar'] ?? '');

    if ($busca_matricula === '' && $busca_nombre === '') {
        $mensaje_buscar = "Ingresa al menos matrícula o nombre para buscar.";
    } else {
        if ($busca_matricula !== '') {
            // Buscar por número de empleado
            $sql  = "SELECT * FROM docente WHERE numero_empleado = $1";
            $params = array($busca_matricula);
        } else {
            // Buscar por nombre (coincidencia parcial, sin importar mayúsculas)
            $sql  = "SELECT * FROM docente WHERE LOWER(nombres) LIKE LOWER($1)";
            $params = array('%' . $busca_nombre . '%');
        }

        $resultado = pg_query_params($conexion, $sql, $params);

        if ($resultado && pg_num_rows($resultado) > 0) {
            $docente_encontrado = pg_fetch_assoc($resultado);
        } else {
            $mensaje_buscar = "No se encontró ningún docente con esos datos.";
        }
    }
}

/* ==========================
   3) EDITAR DOCENTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "editar_docente") {

    $numero_empleado    = $_POST['numero_empleado'];
    $nombres            = trim($_POST["nombres"]);
    $primer_apellido    = trim($_POST["primer_apellido"]);
    $segundo_apellido   = trim($_POST["segundo_apellido"] ?? '');
    $curp               = trim($_POST["curp"] ?? '');
    $rfc                = trim($_POST["rfc"] ?? '');
    $tipo_contratacion  = trim($_POST["tipo_contratacion"] ?? '');
    $asignacion_area    = trim($_POST["asignacion_area"] ?? '');
    $correo_electronico = trim($_POST["correo_electronico"] ?? '');
    $telefono           = trim($_POST["telefono"] ?? '');
    $estatus            = trim($_POST["estatus"] ?? '');

    $sql = "UPDATE docente SET 
                nombres = $1,
                primer_apellido = $2,
                segundo_apellido = $3,
                curp = $4,
                rfc = $5,
                tipo_contratacion = $6,
                asignacion_area = $7,
                correo_electronico = $8,
                telefono = $9,
                estatus = $10
            WHERE numero_empleado = $11";

    $params = array(
        $nombres, $primer_apellido, $segundo_apellido,
        $curp, $rfc, $tipo_contratacion, $asignacion_area,
        $correo_electronico, $telefono, $estatus, $numero_empleado
    );

    $resultado = pg_query_params($conexion, $sql, $params);

    $mensaje_buscar = $resultado
        ? "Docente actualizado correctamente."
        : "Error al actualizar el docente.";
}

/* ==========================
   4) ELIMINAR DOCENTE
   ========================== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "eliminar_docente") {

    $numero_empleado = $_POST["numero_empleado"];

    $sql = "DELETE FROM docente WHERE numero_empleado = $1";
    $resultado = pg_query_params($conexion, $sql, array($numero_empleado));

    $mensaje_buscar = ($resultado && pg_affected_rows($resultado) > 0)
        ? "Docente eliminado correctamente."
        : "No se pudo eliminar el docente (puede que no exista).";

    // Después de eliminar, ya no hay docente para mostrar
    $docente_encontrado = null;
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
    <title>Gestión de Docentes</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Sobrescribir el body solo para esta página (quitamos el flex del login) */
        body {
            display: block;
        }

        /* Contenedor general centrado y ancho */
        .docente-page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 40px;
        }

        /* Header superior */
        .docente-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .docente-header h1 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }

        .docente-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
            font-size: 0.9rem;
        }

        .docente-header-right span {
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

        /* Contenedor de tarjetas (centradas, una debajo de otra) */
        .docente-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .docente-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            width: 100%; /* ocupa todo el ancho disponible (1100px máx) */
        }

        .docente-card h2 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.6rem;
            color: #333;
        }

        .docente-card p.subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }

        .docente-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 12px 16px;
        }

        .docente-group {
            display: flex;
            flex-direction: column;
        }

        .docente-group label {
            font-size: 0.9rem;
            margin-bottom: 4px;
            color: #555;
        }

        .docente-group input {
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

        .docente-actions {
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
    <div class="docente-page">

        <!-- Header superior -->
        <header class="docente-header">
            <div>
                <h1>Gestión de Docentes</h1>
            </div>
            <div class="docente-header-right">
                <span>Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="../menu.php" class="btn-link">Volver al menú</a>
            </div>
        </header>

        <!-- Tarjetas centradas -->
        <div class="docente-cards">

            <!-- TARJETA: NUEVO DOCENTE -->
            <?php if (tienePermiso('nuevo')): ?>
            <div class="docente-card">
                <h2>Nuevo docente</h2>
                <p class="subtitle">Registra un nuevo docente en el sistema.</p>

                <?php if (!empty($mensaje_nuevo)): ?>
                    <div class="mensaje <?php echo (strpos($mensaje_nuevo, 'exitosamente') !== false) ? 'ok' : 'error'; ?>">
                        <?php echo htmlspecialchars($mensaje_nuevo); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="hidden" name="accion" value="nuevo_docente">

                    <div class="docente-grid">
                        <div class="docente-group">
                            <label for="numero_empleado">Matrícula / Número de empleado</label>
                            <input type="text" id="numero_empleado" name="numero_empleado" required>
                        </div>

                        <div class="docente-group">
                            <label for="nombres">Nombres</label>
                            <input type="text" id="nombres" name="nombres" required>
                        </div>

                        <div class="docente-group">
                            <label for="primer_apellido">Primer apellido</label>
                            <input type="text" id="primer_apellido" name="primer_apellido" required>
                        </div>

                        <div class="docente-group">
                            <label for="segundo_apellido">Segundo apellido</label>
                            <input type="text" id="segundo_apellido" name="segundo_apellido">
                        </div>

                        <div class="docente-group">
                            <label for="curp">CURP</label>
                            <input type="text" id="curp" name="curp">
                        </div>

                        <div class="docente-group">
                            <label for="rfc">RFC</label>
                            <input type="text" id="rfc" name="rfc">
                        </div>

                        <div class="docente-group">
                            <label for="tipo_contratacion">Tipo de contratación</label>
                            <input type="text" id="tipo_contratacion" name="tipo_contratacion">
                        </div>

                        <div class="docente-group">
                            <label for="asignacion_area">Área asignada</label>
                            <input type="text" id="asignacion_area" name="asignacion_area">
                        </div>

                        <div class="docente-group">
                            <label for="correo_electronico">Correo electrónico</label>
                            <input type="email" id="correo_electronico" name="correo_electronico">
                        </div>

                        <div class="docente-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono">
                        </div>

                        <div class="docente-group">
                            <label for="estatus">Estatus</label>
                            <input type="text" id="estatus" name="estatus">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Guardar docente</button>
                </form>
            </div>
            <?php endif; ?>

            <!-- TARJETA: CONSULTAR / EDITAR / ELIMINAR DOCENTE -->
            <?php if (tienePermiso('consultar')): ?>
            <div class="docente-card">
                <h2>Consultar docente</h2>
                <p class="subtitle">Busca por matrícula o por nombre. Desde aquí puedes editar o eliminar.</p>

                <?php if (!empty($mensaje_buscar)): ?>
                    <div class="mensaje <?php echo (str_contains($mensaje_buscar, 'correctamente') ? 'ok' : 'error'); ?>">
                        <?php echo htmlspecialchars($mensaje_buscar); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de búsqueda -->
                <form method="POST" action="index.php" style="margin-bottom: 15px;">
                    <input type="hidden" name="accion" value="buscar_docente">

                    <div class="docente-grid">
                        <div class="docente-group">
                            <label for="numero_empleado_buscar">Matrícula / Número de empleado</label>
                            <input type="text" id="numero_empleado_buscar" name="numero_empleado_buscar">
                        </div>

                        <div class="docente-group">
                            <label for="nombre_buscar">Nombre (o parte del nombre)</label>
                            <input type="text" id="nombre_buscar" name="nombre_buscar">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Buscar docente</button>
                </form>

                <!-- Si se encontró un docente, mostrar datos + botones Editar / Eliminar -->
                <?php if ($docente_encontrado): ?>
                    <hr style="margin: 15px 0;">

                    <!-- Formulario para EDITAR -->
                    <form method="POST" action="index.php">
                        <input type="hidden" name="accion" value="editar_docente">
                        <input type="hidden" name="numero_empleado" value="<?php echo htmlspecialchars($docente_encontrado['numero_empleado']); ?>">

                        <div class="docente-grid">
                            <div class="docente-group">
                                <label>Matrícula / Número de empleado</label>
                                <input type="text" value="<?php echo htmlspecialchars($docente_encontrado['numero_empleado']); ?>" disabled>
                            </div>

                            <div class="docente-group">
                                <label>Nombres</label>
                                <input type="text" name="nombres" value="<?php echo htmlspecialchars($docente_encontrado['nombres']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Primer apellido</label>
                                <input type="text" name="primer_apellido" value="<?php echo htmlspecialchars($docente_encontrado['primer_apellido']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Segundo apellido</label>
                                <input type="text" name="segundo_apellido" value="<?php echo htmlspecialchars($docente_encontrado['segundo_apellido']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>CURP</label>
                                <input type="text" name="curp" value="<?php echo htmlspecialchars($docente_encontrado['curp']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>RFC</label>
                                <input type="text" name="rfc" value="<?php echo htmlspecialchars($docente_encontrado['rfc']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Tipo de contratación</label>
                                <input type="text" name="tipo_contratacion" value="<?php echo htmlspecialchars($docente_encontrado['tipo_contratacion']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Área asignada</label>
                                <input type="text" name="asignacion_area" value="<?php echo htmlspecialchars($docente_encontrado['asignacion_area']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Correo electrónico</label>
                                <input type="email" name="correo_electronico" value="<?php echo htmlspecialchars($docente_encontrado['correo_electronico']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Teléfono</label>
                                <input type="tel" name="telefono" value="<?php echo htmlspecialchars($docente_encontrado['telefono']); ?>">
                            </div>

                            <div class="docente-group">
                                <label>Estatus</label>
                                <input type="text" name="estatus" value="<?php echo htmlspecialchars($docente_encontrado['estatus']); ?>">
                            </div>
                        </div>

                        <div class="docente-actions">
                            <button type="submit" class="btn-primary">Guardar cambios</button>
                        </div>
                    </form>

                    <!-- Formulario separado para ELIMINAR -->
                    <?php if (tienePermiso('eliminar')): ?>
                    <form method="POST" action="index.php" onsubmit="return confirm('¿Seguro que deseas eliminar este docente?');">
                        <input type="hidden" name="accion" value="eliminar_docente">
                        <input type="hidden" name="numero_empleado" value="<?php echo htmlspecialchars($docente_encontrado['numero_empleado']); ?>">

                        <div class="docente-actions">
                            <button type="submit" class="btn-danger">Eliminar docente</button>
                        </div>
                    </form>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
            <?php endif; ?>

        </div><!-- .docente-cards -->

    </div><!-- .docente-page -->
</body>
</html>
