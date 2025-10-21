<?php
// =============================================================
// ARCHIVO DE PRUEBA: editar_cita.php
// Propósito: Verificar si el archivo es accesible.
// =============================================================

// 1. Verificar si el archivo puede ejecutarse
echo "<h1>Paso 1: ¡El archivo editar_cita.php es accesible!</h1>";

// 2. Intentar incluir la conexión
if (file_exists('conexion.php')) {
    include 'conexion.php';
    echo "<p>Paso 2: Se encontró conexion.php.</p>";
} else {
    die("<h2 style='color:red;'>Error Crítico: No se encontró conexion.php. Colócalo en la misma carpeta.</h2>");
}

// 3. Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("<h2 style='color:red;'>Error Crítico: Falló la conexión a la base de datos. Revisa las credenciales en conexion.php.</h2>");
} else {
    echo "<p>Paso 3: ¡Conexión a la base de datos exitosa! Los problemas anteriores eran de código PHP, no de conexión.</p>";
}

// 4. Mostrar el ID recibido
$id_cita = isset($_GET['id']) ? intval($_GET['id']) : 0;
echo "<h2>ID de Cita Recibido en la URL: " . $id_cita . "</h2>";

if ($id_cita == 0) {
    echo "<p style='color:orange;'>Advertencia: El ID debe ser un número válido, no 0.</p>";
}

echo "<hr><a href='buscar_paciente.php'>Volver al Buscador</a>";

?>