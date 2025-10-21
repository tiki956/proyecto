<?php
// =============================================================
// Archivo: conexion.php
// ¡SOLUCIÓN DE ERROR FATAL! 🛑
// =============================================================
// Datos de conexión a la base de datos (¡VERIFICA ESTOS VALORES!)
$host = "localhost"; 
$user = "root"; 
$password = ""; 
// 🚨 ATENCIÓN: DEBES REVISAR QUE ESTA BASE DE DATOS EXISTA EN TU PHP-MYADMIN.
// Si el nombre es diferente, CÁMBIALO AQUÍ.
$db = "pagina_para_secretarias_de_veterinaria"; 

// Establecer conexión
$conexion = new mysqli($host, $user, $password, $db);

// Verificar la conexión y atrapar el error "Unknown database"
if ($conexion->connect_error) {
    // El error que estás viendo ("Unknown database") se atrapará aquí.
    die("
        <div style='border: 2px solid red; padding: 15px; background-color: #fdd; color: #a00; font-family: monospace;'>
            <h3>🔴 ERROR FATAL DE CONEXIÓN A LA BASE DE DATOS</h3>
            <p><strong>Problema:</strong> No se encontró la base de datos con el nombre <strong>'$db'</strong>.</p>
            <p><strong>Detalle del Error:</strong> " . $conexion->connect_error . "</p>
            <p><strong>SOLUCIÓN:</strong></p>
            <ol>
                <li>Abre tu <strong>phpMyAdmin</strong>.</li>
                <li>Verifica que exista una base de datos llamada <strong>'$db'</strong>.</li>
                <li>Si el nombre es incorrecto, edita la línea <code>\$db = \"mi_sistema_veterinario\";</code> en <strong>conexion.php</strong> con el nombre correcto.</li>
                <li>Si no existe, debes crearla y luego importar tu estructura de tablas.</li>
            </ol>
        </div>
    ");
}

// Establecer el juego de caracteres a UTF8
$conexion->set_charset("utf8");

// La variable $conexion está lista para ser usada.
?>