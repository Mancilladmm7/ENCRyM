<?php
session_start();

// Si NO hay usuario en sesión, regresar al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include_once __DIR__ . '/funciones.php'; // por si usas tienePermiso()
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú principal - Gestión Académica</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos específicos del menú (encima de tu styles.css o aquí mismo) */

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .menu-user-info {
            font-size: 0.95rem;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .menu-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.12);
            text-align: center;
        }

        .menu-card h2 {
            margin-bottom: 10px;
            font-size: 1.4rem;
            color: #333;
        }

        .menu-card p {
            font-size: 0.95rem;
            margin-bottom: 15px;
            color: #555;
        }

        .menu-card a {
            display: inline-block;
            padding: 10px 18px;
            background-color: #3498db;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .menu-card a:hover {
            background-color: #2980b9;
        }

        .logout-form button {
            padding: 8px 14px;
            background-color: #e74c3c;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
        }

        .logout-form button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="page-container"><!-- usa el mismo estilo que DOCENTE -->

        <div class="menu-header">
            <div>
                <h1>Gestión Académica</h1>
                <div class="menu-user-info">
                    Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>
                    <?php if (isset($_SESSION['rol'])): ?>
                        (Rol: <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong>)
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón de cerrar sesión -->
            <form class="logout-form" action="logout.php" method="POST">
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>

        <div class="menu-grid">
            <div class="menu-card">
                <h2>Docentes</h2>
                <p>Registrar, consultar, editar y eliminar información de docentes.</p>
                <a href="DOCENTE/index.php">Ir a Docentes</a>
            </div>

            <div class="menu-card">
                <h2>Estudiantes</h2>
                <p>Gestión de estudiantes: altas, consultas y actualizaciones.</p>
                <a href="ESTUDIANTE/index.php">Ir a Estudiantes</a>
            </div>

            <div class="menu-card">
                <h2>Titulación</h2>
                <p>Registro y seguimiento de procesos de titulación.</p>
                <a href="TITULACION/index.php">Ir a Titulación</a>
            </div>
            <div class="menu-card">
                <h2>Programa Academico</h2>
                <p>Programa Academico: altas, consultas y actualizaciones.</p>
                <a href="PROGRAMA_ACADEMICO/index.php">Ir a programa academico</a>
            </div>
            <div class="menu-card">
                <h2>Plan de estudios</h2>
                <p>Definición de planes por programa y registro de espacios curriculares (materias).</p>
                <a href="PLAN_ESTUDIOS/index.php">Ir a Plan de estudios</a>
            </div>

        </div>

    </div>
</body>
</html>
