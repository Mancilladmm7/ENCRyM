<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CONFIGURA TUS DATOS REALES AQUÍ
$host = "localhost";
$port = "5432";
$dbname = "EJEMPLO"; // Cambia este valor
$user = "postgres";  // Cambia este valor si es otro usuario
$password = "Mancilla0106"; // Cambia por tu contraseña real

$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_string);

if (!$conn) {
    die("❌ Error de conexión a PostgreSQL");
} else {
    echo "✅ Conexión exitosa a PostgreSQL";
}
?>


