<?php
session_start();
include_once '../funciones.php';
include_once '../conexion.php';  


// ---------------- FUNCIONES AUXILIARES ----------------
function obtenerPlanYEspacios($conexion, $planId) {
    $plan_consultado = null;
    $espacios_plan = [];

    // Info del plan
    $sqlPlan = "SELECT pe.*, pa.nombre AS programa_nombre
                FROM plan_estudios pe
                JOIN programa_academico pa ON pa.id = pe.programa_id
                WHERE pe.id = $1";
    $resPlan = pg_query_params($conexion, $sqlPlan, array($planId));
    if ($resPlan && pg_num_rows($resPlan) > 0) {
        $plan_consultado = pg_fetch_assoc($resPlan);
    }

    // Espacios curriculares
    $sqlEspacios = "SELECT * FROM espacio_curricular
                    WHERE plan_id = $1
                    ORDER BY semestre, nombre";
    $resEspacios = pg_query_params($conexion, $sqlEspacios, array($planId));
    if ($resEspacios) {
        $tmp = pg_fetch_all($resEspacios);
        if ($tmp) {
            $espacios_plan = $tmp;
        }
    }

    return array($plan_consultado, $espacios_plan);
}

// Variables para controlar edición
$plan_consultado = null;
$espacios_plan   = [];
$espacio_editando = null;

// ---------------- MANEJO DE FORMULARIOS ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // 1) NUEVO PLAN
    if ($accion === 'nuevo_plan') {
        $programa_id  = $_POST['programa_id'] ?? null;
        $nombre_plan  = $_POST['nombre_plan'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? null;
        $fecha_fin    = $_POST['fecha_fin'] ?? null;
        $estatus      = $_POST['estatus'] ?? 'Vigente';

        if (!empty($programa_id) && !empty($nombre_plan)) {
            $sql = "INSERT INTO plan_estudios (programa_id, nombre, fecha_inicio, fecha_fin, estatus)
                    VALUES ($1, $2, $3, $4, $5)";
            $params = array($programa_id, $nombre_plan, $fecha_inicio ?: null, $fecha_fin ?: null, $estatus);
            $res = pg_query_params($conexion, $sql, $params);

            if ($res) {
                echo "<script>alert('Plan de estudios registrado correctamente'); window.location.href='index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error al registrar el plan de estudios');</script>";
            }
        } else {
            echo "<script>alert('Faltan datos obligatorios (programa y nombre del plan)');</script>";
        }
    }

    // 2) NUEVO ESPACIO CURRICULAR
    if ($accion === 'nuevo_espacio') {
        $plan_id         = $_POST['plan_id'] ?? null;
        $nombre_espacio  = $_POST['nombre_espacio'] ?? '';
        $tipo            = $_POST['tipo'] ?? '';
        $horas_teoricas  = $_POST['horas_teoricas'] ?? 0;
        $horas_practicas = $_POST['horas_practicas'] ?? 0;
        $creditos        = $_POST['creditos'] ?? 0;
        $semestre        = $_POST['semestre'] ?? null;
        $responsable     = $_POST['responsable'] ?? '';

        if (!empty($plan_id) && !empty($nombre_espacio)) {
            $sql = "INSERT INTO espacio_curricular
                    (plan_id, nombre, tipo, horas_teoricas, horas_practicas, creditos, semestre, responsable)
                    VALUES ($1,$2,$3,$4,$5,$6,$7,$8)";
            $params = array(
                $plan_id, $nombre_espacio, $tipo,
                (int)$horas_teoricas, (int)$horas_practicas,
                (int)$creditos,
                !empty($semestre) ? (int)$semestre : null,
                $responsable
            );
            $res = pg_query_params($conexion, $sql, $params);

            if ($res) {
                echo "<script>alert('Espacio curricular registrado correctamente'); window.location.href='index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error al registrar el espacio curricular');</script>";
            }
        } else {
            echo "<script>alert('Selecciona un plan y escribe el nombre del espacio curricular');</script>";
        }
    }

    // 3) CARGAR PLAN PARA CONSULTA (vista tabla + malla)
    if ($accion === 'consultar_plan') {
        $plan_consulta_id = $_POST['plan_consulta_id'] ?? null;
        if (!empty($plan_consulta_id)) {
            list($plan_consultado, $espacios_plan) = obtenerPlanYEspacios($conexion, $plan_consulta_id);
        } else {
            echo "<script>alert('Selecciona un plan para consultar');</script>";
        }
    }

    // 4) CARGAR ESPACIO PARA EDICIÓN
    if ($accion === 'editar_espacio_cargar') {
        $espacio_id = $_POST['espacio_id'] ?? null;
        $plan_id    = $_POST['plan_id'] ?? null;

        if (!empty($espacio_id)) {
            $sql = "SELECT * FROM espacio_curricular WHERE id = $1";
            $res = pg_query_params($conexion, $sql, array($espacio_id));
            if ($res && pg_num_rows($res) > 0) {
                $espacio_editando = pg_fetch_assoc($res);

                // Para que abajo en la página se vea también el plan y la tabla:
                if (!$plan_id) {
                    $plan_id = $espacio_editando['plan_id'];
                }
                list($plan_consultado, $espacios_plan) = obtenerPlanYEspacios($conexion, $plan_id);
            }
        }
    }

    // 5) GUARDAR ESPACIO EDITADO
    if ($accion === 'editar_espacio_guardar') {
        $espacio_id      = $_POST['espacio_id'] ?? null;
        $plan_id         = $_POST['plan_id'] ?? null;
        $nombre_espacio  = $_POST['nombre_espacio'] ?? '';
        $tipo            = $_POST['tipo'] ?? '';
        $horas_teoricas  = $_POST['horas_teoricas'] ?? 0;
        $horas_practicas = $_POST['horas_practicas'] ?? 0;
        $creditos        = $_POST['creditos'] ?? 0;
        $semestre        = $_POST['semestre'] ?? null;
        $responsable     = $_POST['responsable'] ?? '';

        if (!empty($espacio_id) && !empty($plan_id) && !empty($nombre_espacio)) {
            $sql = "UPDATE espacio_curricular
                    SET plan_id = $1,
                        nombre = $2,
                        tipo = $3,
                        horas_teoricas = $4,
                        horas_practicas = $5,
                        creditos = $6,
                        semestre = $7,
                        responsable = $8
                    WHERE id = $9";
            $params = array(
                $plan_id, $nombre_espacio, $tipo,
                (int)$horas_teoricas, (int)$horas_practicas,
                (int)$creditos,
                !empty($semestre) ? (int)$semestre : null,
                $responsable,
                $espacio_id
            );
            $res = pg_query_params($conexion, $sql, $params);

            if ($res) {
                echo "<script>alert('Espacio curricular actualizado'); window.location.href='index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error al actualizar el espacio curricular');</script>";
            }
        } else {
            echo "<script>alert('Faltan datos para actualizar el espacio curricular');</script>";
        }
    }

    // 6) ELIMINAR ESPACIO CURRICULAR
    if ($accion === 'eliminar_espacio') {
        $espacio_id = $_POST['espacio_id'] ?? null;
        if (!empty($espacio_id)) {
            $sql = "DELETE FROM espacio_curricular WHERE id = $1";
            $res = pg_query_params($conexion, $sql, array($espacio_id));
            if ($res) {
                echo "<script>alert('Espacio curricular eliminado'); window.location.href='index.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error al eliminar el espacio curricular');</script>";
            }
        }
    }
}

// --------- CONSULTAS BÁSICAS PARA SELECTS ---------
$programas = pg_query($conexion, "SELECT id, nombre FROM programa_academico WHERE estatus = 'Activo' ORDER BY nombre");
$planes = pg_query($conexion, "
    SELECT pe.id, pe.nombre, pa.nombre AS programa
    FROM plan_estudios pe
    JOIN programa_academico pa ON pa.id = pe.programa_id
    ORDER BY pa.nombre, pe.nombre
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Planes de Estudio</title>
    <link rel="stylesheet" href="../styles.css">

    <style>
        .page-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px 30px;
            background-color: #f5f5f5;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .back-link {
            color: #2c3e50;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .back-link:hover { text-decoration: underline; }

        .top-user {
            font-size: 0.9rem;
            color: #555;
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .card-box {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .card-header-purple {
            background-color: #8e44ad;
            color: #fff;
            padding: 12px 18px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .card-subtitle {
            font-size: 0.9rem;
            color: #f0f0f0;
        }

        .card-body {
            padding: 18px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 18px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-primary:hover { background-color: #2980b9; }

        .btn-danger {
            background-color: #e74c3c;
            color: #fff;
        }
        .btn-danger:hover { background-color: #c0392b; }

        .btn-small {
            padding: 4px 10px;
            font-size: 0.8rem;
        }

        .section-title {
            margin: 25px 0 10px 0;
            font-size: 1.15rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 0.85rem;
            text-align: left;
        }
        th {
            background-color: #ecf0f1;
        }

        /* Malla curricular */
        .malla-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .malla-col {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            padding: 10px 12px;
        }
        .malla-col h4 {
            margin-bottom: 8px;
            font-size: 0.95rem;
            color: #8e44ad;
        }
        .malla-materia {
            font-size: 0.85rem;
            margin-bottom: 6px;
        } 
        .malla-materia span {
            display: block;
        }
        .malla-materia small {
            color: #777;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="top-bar">
            <a href="../menu.php" class="back-link">← Volver al menú</a>
            <div class="top-user">
                Usuario: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
                <?php if (isset($_SESSION['rol'])): ?>
                    (Rol: <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>)
                <?php endif; ?>
            </div>
        </div>

        <h1>Gestión de Planes de Estudio</h1>

        <!-- TARJETAS: NUEVO PLAN + NUEVO / EDITAR ESPACIO -->
        <div class="cards-grid">

            <!-- Registrar nuevo plan -->
            <div class="card-box">
                <div class="card-header-purple">
                    Registrar nuevo plan de estudios
                    <div class="card-subtitle">Asocia un plan a un programa académico.</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="accion" value="nuevo_plan">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="programa_id">Programa académico</label>
                                <select name="programa_id" id="programa_id" required>
                                    <option value="">Seleccione un programa</option>
                                    <?php while ($p = pg_fetch_assoc($programas)): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nombre_plan">Nombre del plan</label>
                                <input type="text" name="nombre_plan" id="nombre_plan"
                                    placeholder="Ej. Plan 2013" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha de inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio">
                            </div>
                            <div class="form-group">
                                <label for="fecha_fin">Fecha de fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin">
                            </div>
                            <div class="form-group">
                                <label for="estatus">Estatus</label>
                                <select name="estatus" id="estatus">
                                    <option value="Vigente">Vigente</option>
                                    <option value="No vigente">No vigente</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">Guardar plan de estudios</button>
                    </form>
                </div>
            </div>

            <!-- Registrar / editar espacio curricular -->
            <div class="card-box">
                <div class="card-header-purple">
                    <?php echo $espacio_editando ? 'Editar espacio curricular' : 'Registrar espacio curricular'; ?>
                    <div class="card-subtitle">
                        <?php echo $espacio_editando
                            ? 'Modifica los datos del espacio curricular seleccionado.'
                            : 'Agrega materias a un plan de estudios.'; ?>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="index.php">
                        <?php if ($espacio_editando): ?>
                            <input type="hidden" name="accion" value="editar_espacio_guardar">
                            <input type="hidden" name="espacio_id" value="<?php echo $espacio_editando['id']; ?>">
                        <?php else: ?>
                            <input type="hidden" name="accion" value="nuevo_espacio">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="plan_id">Plan de estudios</label>
                                <select name="plan_id" id="plan_id" required>
                                    <option value="">Seleccione un plan</option>
                                    <?php
                                    $planes2 = pg_query($conexion, "
                                        SELECT pe.id, pe.nombre, pa.nombre AS programa
                                        FROM plan_estudios pe
                                        JOIN programa_academico pa ON pa.id = pe.programa_id
                                        ORDER BY pa.nombre, pe.nombre
                                    ");
                                    while ($pl = pg_fetch_assoc($planes2)):
                                        $selected = '';
                                        if ($espacio_editando && $espacio_editando['plan_id'] == $pl['id']) {
                                            $selected = 'selected';
                                        }
                                    ?>
                                        <option value="<?php echo $pl['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($pl['programa'] . ' - ' . $pl['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nombre_espacio">Nombre del espacio curricular</label>
                                <input
                                    type="text"
                                    name="nombre_espacio"
                                    id="nombre_espacio"
                                    placeholder="Ej. Introducción a la Conservación"
                                    required
                                    value="<?php echo $espacio_editando ? htmlspecialchars($espacio_editando['nombre']) : ''; ?>"
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select name="tipo" id="tipo">
                                    <?php
                                    $tipos = ['' => 'Seleccione', 'Teórica' => 'Teórica', 'Taller' => 'Taller',
                                              'Laboratorio' => 'Laboratorio', 'Seminario' => 'Seminario',
                                              'Práctica' => 'Práctica'];
                                    foreach ($tipos as $val => $label):
                                        $sel = ($espacio_editando && $espacio_editando['tipo'] === $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $val; ?>" <?php echo $sel; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semestre">Semestre</label>
                                <input
                                    type="number"
                                    name="semestre"
                                    id="semestre"
                                    min="1" max="12"
                                    value="<?php echo $espacio_editando ? (int)$espacio_editando['semestre'] : ''; ?>"
                                >
                            </div>
                            <div class="form-group">
                                <label for="creditos">Créditos</label>
                                <input
                                    type="number"
                                    name="creditos"
                                    id="creditos"
                                    min="0"
                                    value="<?php echo $espacio_editando ? (int)$espacio_editando['creditos'] : ''; ?>"
                                >
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="horas_teoricas">Horas teóricas</label>
                                <input
                                    type="number"
                                    name="horas_teoricas"
                                    id="horas_teoricas"
                                    min="0"
                                    value="<?php echo $espacio_editando ? (int)$espacio_editando['horas_teoricas'] : ''; ?>"
                                >
                            </div>
                            <div class="form-group">
                                <label for="horas_practicas">Horas prácticas</label>
                                <input
                                    type="number"
                                    name="horas_practicas"
                                    id="horas_practicas"
                                    min="0"
                                    value="<?php echo $espacio_editando ? (int)$espacio_editando['horas_practicas'] : ''; ?>"
                                >
                            </div>
                            <div class="form-group">
                                <label for="responsable">Responsable</label>
                                <input
                                    type="text"
                                    name="responsable"
                                    id="responsable"
                                    placeholder="Ej. Mtra. Laura Martínez"
                                    value="<?php echo $espacio_editando ? htmlspecialchars($espacio_editando['responsable']) : ''; ?>"
                                >
                            </div>
                        </div>

                        <button type="submit" class="btn-primary">
                            <?php echo $espacio_editando ? 'Guardar cambios' : 'Guardar espacio curricular'; ?>
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /cards-grid -->

        <!-- CONSULTAR PLAN Y VER TABLA + MALLA -->
        <div class="card-box">
            <div class="card-header-purple">
                Consultar plan de estudios
                <div class="card-subtitle">Selecciona un plan para ver sus espacios curriculares.</div>
            </div>
            <div class="card-body">
                <form method="POST" action="index.php">
                    <input type="hidden" name="accion" value="consultar_plan">

                    <div class="form-row">
                        <div class="form-group" style="max-width: 400px;">
                            <label for="plan_consulta_id">Plan de estudios</label>
                            <select name="plan_consulta_id" id="plan_consulta_id" required>
                                <option value="">Seleccione un plan</option>
                                <?php
                                $planes3 = pg_query($conexion, "
                                    SELECT pe.id, pe.nombre, pa.nombre AS programa
                                    FROM plan_estudios pe
                                    JOIN programa_academico pa ON pa.id = pe.programa_id
                                    ORDER BY pa.nombre, pe.nombre
                                ");
                                while ($pl3 = pg_fetch_assoc($planes3)):
                                    $selected = ($plan_consultado && $plan_consultado['id'] == $pl3['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $pl3['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($pl3['programa'] . ' - ' . $pl3['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group" style="align-self:flex-end;">
                            <button type="submit" class="btn-primary">Consultar</button>
                        </div>
                    </div>
                </form>

                <?php if (!empty($plan_consultado)): ?>
                    <h3 class="section-title">
                        Plan: <?php echo htmlspecialchars($plan_consultado['nombre']); ?>
                        (Programa: <?php echo htmlspecialchars($plan_consultado['programa_nombre']); ?>)
                    </h3>
                    <p>
                        Estatus: <strong><?php echo htmlspecialchars($plan_consultado['estatus']); ?></strong><br>
                        Inicio: <?php echo $plan_consultado['fecha_inicio'] ?: 'N/D'; ?> |
                        Fin: <?php echo $plan_consultado['fecha_fin'] ?: 'N/D'; ?>
                    </p>

                    <?php if (!empty($espacios_plan)): ?>
                        <!-- TABLA con botones Editar/Eliminar -->
                        <table>
                            <thead>
                                <tr>
                                    <th>Sem.</th>
                                    <th>Nombre del espacio</th>
                                    <th>Tipo</th>
                                    <th>Créd.</th>
                                    <th>H. T.</th>
                                    <th>H. P.</th>
                                    <th>Responsable</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($espacios_plan as $e): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($e['semestre']); ?></td>
                                    <td><?php echo htmlspecialchars($e['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($e['tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($e['creditos']); ?></td>
                                    <td><?php echo htmlspecialchars($e['horas_teoricas']); ?></td>
                                    <td><?php echo htmlspecialchars($e['horas_practicas']); ?></td>
                                    <td><?php echo htmlspecialchars($e['responsable']); ?></td>
                                    <td>
                                        <!-- Botón editar -->
                                        <form method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="accion" value="editar_espacio_cargar">
                                            <input type="hidden" name="espacio_id" value="<?php echo $e['id']; ?>">
                                            <input type="hidden" name="plan_id" value="<?php echo $plan_consultado['id']; ?>">
                                            <button type="submit" class="btn-primary btn-small">Editar</button>
                                        </form>

                                        <!-- Botón eliminar -->
                                        <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este espacio curricular?');">
                                            <input type="hidden" name="accion" value="eliminar_espacio">
                                            <input type="hidden" name="espacio_id" value="<?php echo $e['id']; ?>">
                                            <button type="submit" class="btn-danger btn-small">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- MALLA CURRICULAR -->
                        <h3 class="section-title">Malla curricular por semestre</h3>
                        <?php
                        // Agrupar espacios por semestre
                        $malla = [];
                        foreach ($espacios_plan as $e) {
                            $sem = $e['semestre'] ?: 0;
                            if (!isset($malla[$sem])) {
                                $malla[$sem] = [];
                            }
                            $malla[$sem][] = $e;
                        }
                        ksort($malla);
                        ?>
                        <div class="malla-grid">
                            <?php foreach ($malla as $sem => $materias): ?>
                                <div class="malla-col">
                                    <h4><?php echo $sem > 0 ? "Semestre $sem" : "Sin semestre"; ?></h4>
                                    <?php foreach ($materias as $mat): ?>
                                        <div class="malla-materia">
                                            <span><strong><?php echo htmlspecialchars($mat['nombre']); ?></strong></span>
                                            <small>
                                                <?php echo htmlspecialchars($mat['tipo']); ?> |
                                                Créd.: <?php echo (int)$mat['creditos']; ?> |
                                                HT: <?php echo (int)$mat['horas_teoricas']; ?> |
                                                HP: <?php echo (int)$mat['horas_practicas']; ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php else: ?>
                        <p>No hay espacios curriculares registrados para este plan.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
if (isset($conexion) && $conexion) {
    pg_close($conexion);
}
?>
</body>
</html>
