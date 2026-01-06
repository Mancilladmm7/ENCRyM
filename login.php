<?php
session_start(); // Iniciar sesión para gestionar la sesión de usuario


// Verificar si el usuario ya está autenticado
if (isset($_SESSION['usuario'])) {
    // Si el usuario ya está autenticado, muestra el contenido de la página
    header("Location: menu.php"); // Redirige a la página principal si ya está logueado
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario']) && isset($_POST['contraseña'])) {
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'EJEMPLO'; 
    $user = 'postgres';
    $password = 'Mancilla0106';

    try {
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {z
        echo 'Error de conexión: ' . $e->getMessage();
    }

    // Obtener los datos enviados por el formulario
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Validar el usuario y la contraseña
    $stmt = $pdo->prepare("
        SELECT u.*, r.nombre AS rol_nombre, r.permisos
        FROM usuarios u
        JOIN roles r ON u.rol_id = r.id
        WHERE u.nombre_usuario = ?
    ");
    $stmt->execute([$usuario]);
    $resultado = $stmt->fetch();

    // Comparar las contraseñas (sin cifrar)
    if ($resultado && $_POST['contraseña'] == $resultado['contraseña']) {
        // Si las credenciales son correctas
    
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = $resultado['rol_nombre'];
        $_SESSION['permisos'] = explode(',', $resultado['permisos']); // arreglo de permisos

        header("Location: menu.php"); // Redirige a la página principal
        exit();
    } else {
        // Si las credenciales son incorrectas
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Inicio de sesión</h1>

        <!-- Mostrar mensaje de error si las credenciales son incorrectas -->
        <?php if (isset($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" id="usuario" required>

            <label for="contraseña">Contraseña:</label>
            <input type="password" name="contraseña" id="contraseña" required>

            <button type="submit">Iniciar sesión</button>
        </form>
    </div>
</body>
</html>
