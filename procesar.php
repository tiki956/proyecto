<?php
// =============================================================
// Archivo: procesar.php
// CORREGIDO: Se añade lógica de validación para evitar duplicados (DNI y Email).
// =============================================================
require_once 'conexion.php'; 

// Verificación de conexión
if (!isset($conexion) || $conexion->connect_error) {
    die("Error de conexión fatal. Revise su archivo conexion.php.");
}

// 1. Recoger y sanear datos del Propietario y Paciente
$nombre_propietario = $conexion->real_escape_string($_POST['nombre_propietario']); 
$dni_cedula = $conexion->real_escape_string($_POST['dni_cedula']);
$telefono_principal = $conexion->real_escape_string($_POST['telefono_principal']);
$email = $conexion->real_escape_string($_POST['email']);
$direccion = $conexion->real_escape_string($_POST['direccion']);

$nombre_mascota = $conexion->real_escape_string($_POST['nombre_mascota']);
$especie = $conexion->real_escape_string($_POST['especie']);
$raza = $conexion->real_escape_string($_POST['raza']);
$sexo = $conexion->real_escape_string($_POST['sexo']);
$fecha_nacimiento = $conexion->real_escape_string($_POST['fecha_nacimiento']);
$motivo_inicial = $conexion->real_escape_string($_POST['motivo_inicial']);

$fecha_nacimiento_db = empty($fecha_nacimiento) ? "NULL" : "'$fecha_nacimiento'";


// =================================================
// PASO 1.5: VALIDACIÓN ANTI-DUPLICADOS (NUEVO)
// =================================================
$sql_check = "
    SELECT ID_Propietario 
    FROM Propietarios 
    WHERE DNI_Cedula = '$dni_cedula' OR Email = '$email'
    LIMIT 1
";
$resultado_check = $conexion->query($sql_check);

if ($resultado_check && $resultado_check->num_rows > 0) {
    $error_mensaje = "❌ Error: Ya existe un Propietario registrado con ese DNI/Cédula o Email. Por favor, revise el listado o use la función 'Buscar Paciente' si cree que ya está registrado.";
    header("Location: index.php?error=" . urlencode($error_mensaje));
    exit();
}


// 2. Insertar Propietario
$sql_propietario = "
    INSERT INTO Propietarios (Nombre_Completo, DNI_Cedula, Telefono_Principal, Email, Direccion)
    VALUES ('$nombre_propietario', '$dni_cedula', '$telefono_principal', '$email', '$direccion')
";

if ($conexion->query($sql_propietario) === TRUE) {
    $id_propietario = $conexion->insert_id;
    
    // 3. Insertar Paciente
    $sql_paciente = "
        INSERT INTO Pacientes (ID_Propietario, Nombre_Mascota, Especie, Raza, Sexo, Fecha_Nacimiento)
        VALUES ('$id_propietario', '$nombre_mascota', '$especie', '$raza', '$sexo', $fecha_nacimiento_db)
    ";

    if ($conexion->query($sql_paciente) === TRUE) {
        $id_paciente = $conexion->insert_id;

        // 4. Insertar Cita Inicial con el Motivo
        if (!empty($motivo_inicial)) {
            $fecha_actual = date('Y-m-d H:i:s');
            $estado_cita_inicial = 'Realizada';

            $sql_cita_inicial = "
                INSERT INTO Citas (ID_Paciente, Fecha_Cita, Motivo_Cita, Estado_Cita)
                VALUES ('$id_paciente', '$fecha_actual', '$motivo_inicial (Visita Inicial)', '$estado_cita_inicial')
            ";
            
            $conexion->query($sql_cita_inicial);
        }

        // Redirección exitosa: A la ficha del paciente recién creado para gestión
        header("Location: buscar_paciente.php?paciente_id=" . $id_paciente . "&registro=exitoso");
        exit();

    } else {
        // Error en inserción del Paciente
        $error_mensaje = "Error al registrar el paciente: " . $conexion->error;
        // Si el paciente falla, limpiamos el propietario recién insertado
        $conexion->query("DELETE FROM Propietarios WHERE ID_Propietario = $id_propietario");
        header("Location: index.php?error=" . urlencode($error_mensaje));
        exit();
    }

} else {
    // ESTA PARTE YA NO DEBERÍA SER NECESARIA POR LA VALIDACIÓN, PERO SE MANTIENE POR SI FALLA ALGO MÁS
    $error_mensaje = "Error al registrar el propietario: " . $conexion->error;
    header("Location: index.php?error=" . urlencode($error_mensaje));
    exit();
}

// Cerrar la conexión
$conexion->close(); 
?>