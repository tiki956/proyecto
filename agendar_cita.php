<?php
// =============================================================
// Archivo: agendar_cita.php
// Objetivo: Formulario y lógica para agendar una nueva cita (incluye HORA).
// =============================================================
require_once 'conexion.php'; 

$paciente_id = 0;
$paciente_data = null;
$mensaje = '';

// Motivos de cita comunes (para el selector)
$motivos_comunes = [
    'Consulta General',
    'Vacunación',
    'Desparasitación',
    'Revisión Post-Operatoria',
    'Control de Rutina',
    'Emergencia',
    'Otro (Especifique abajo)'
];

// 1. Obtener el ID del paciente desde la URL
if (isset($_GET['paciente_id'])) {
    $paciente_id = intval($_GET['paciente_id']);
}

// 2. Procesar el formulario POST (Guardar la cita)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agendar_cita'])) {
    // Recoger y sanear datos
    $paciente_id_post = intval($_POST['paciente_id']);
    $fecha_cita = $conexion->real_escape_string($_POST['fecha_cita']);
    $hora_cita = $conexion->real_escape_string($_POST['hora_cita']); // <-- CAMPO HORA

    // Obtener y combinar Motivos
    $motivo_select = $conexion->real_escape_string($_POST['motivo_select']);
    $motivo_texto = $conexion->real_escape_string($_POST['motivo_texto']);
    
    // Lógica para combinar la selección y el texto libre
    if ($motivo_select == 'Otro (Especifique abajo)' || empty($motivo_select)) {
        $motivo_final = $motivo_texto;
    } elseif (!empty($motivo_texto)) {
        $motivo_final = $motivo_select . " - Detalle: " . $motivo_texto;
    } else {
        $motivo_final = $motivo_select;
    }

    $estado = 'Pendiente'; // Estado por defecto al agendar

    // Combinar fecha y hora para el formato MySQL DATETIME
    $fecha_hora_cita = $fecha_cita . ' ' . $hora_cita . ':00'; // <-- COMBINACIÓN DE FECHA Y HORA

    // Inserción en la tabla Citas
    $sql_insert = "
        INSERT INTO Citas (ID_Paciente, Fecha_Cita, Motivo_Cita, Estado_Cita)
        VALUES ($paciente_id_post, '$fecha_hora_cita', '$motivo_final', '$estado')
    ";

    if ($conexion->query($sql_insert)) {
        $mensaje = "<div class='alert alert-success mt-3'>✅ Cita agendada correctamente para el paciente ID $paciente_id_post el día " . date('d/m/Y H:i', strtotime($fecha_hora_cita)) . ".</div>";
    } else {
        $mensaje = "<div class='alert alert-danger mt-3'>❌ Error al agendar la cita: " . htmlspecialchars($conexion->error) . "</div>";
    }

    $paciente_id = $paciente_id_post;
}

// 3. Cargar datos del paciente (para pre-llenar la página)
if ($paciente_id > 0) {
    $sql_select_paciente = "
        SELECT 
            P.Nombre_Mascota, P.Especie, O.Nombre_Completo AS Nombre_Dueño
        FROM Pacientes P
        JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
        WHERE P.ID_Paciente = $paciente_id
    ";
    $resultado = $conexion->query($sql_select_paciente);
    if ($resultado && $resultado->num_rows > 0) {
        $paciente_data = $resultado->fetch_assoc();
    } else {
        $mensaje = "<div class='alert alert-warning mt-3'>⚠️ No se encontró el paciente con ID $paciente_id.</div>";
        $paciente_id = 0; 
    }
} else {
    // Si la secretaria viene sin paciente_id, lo mandamos al buscador
    header("Location: buscar_paciente.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Nueva Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4 text-success">➕ Agendar Nueva Cita Médica</h2>
        
        <div class="d-flex justify-content-center mb-4">
            <a href="buscar_paciente.php?paciente_id=<?php echo $paciente_id; ?>" class="btn btn-secondary me-2">← Volver a Gestión de Citas</a>
            <a href="index.php" class="btn btn-secondary">← Volver al Menú Principal</a>
        </div>
        
        <?php echo $mensaje; ?>

        <?php if ($paciente_data): ?>
        <div class="card mb-4 shadow-lg border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Datos del Paciente</h5>
            </div>
            <div class="card-body">
                <p><strong>Mascota:</strong> <?php echo htmlspecialchars($paciente_data['Nombre_Mascota']); ?> (<?php echo htmlspecialchars($paciente_data['Especie']); ?>)</p>
                <p><strong>Propietario:</strong> <?php echo htmlspecialchars($paciente_data['Nombre_Dueño']); ?></p>
                <p><strong>ID Paciente:</strong> <?php echo $paciente_id; ?></p>
            </div>
        </div>

        <form action="agendar_cita.php" method="POST" class="p-4 border rounded shadow-sm">
            <input type="hidden" name="paciente_id" value="<?php echo $paciente_id; ?>">
            <input type="hidden" name="agendar_cita" value="1">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="fecha_cita" class="form-label">Fecha de la Cita</label>
                    <input type="date" class="form-control" id="fecha_cita" name="fecha_cita" required>
                </div>
                <div class="col-md-6">
                    <label for="hora_cita" class="form-label">**Hora de la Cita**</label>
                    <input type="time" class="form-control" id="hora_cita" name="hora_cita" required> 
                </div>
                
                <div class="col-12">
                    <label for="motivo_select" class="form-label">Motivo (Selección Común)</label>
                    <select class="form-select" id="motivo_select" name="motivo_select">
                        <option value="">Seleccione un motivo (Opcional)...</option>
                        <?php foreach ($motivos_comunes as $motivo): ?>
                            <option value="<?php echo htmlspecialchars($motivo); ?>"><?php echo htmlspecialchars($motivo); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label for="motivo_texto" class="form-label">Motivo (Detalle Específico/Texto Libre)</label>
                    <textarea class="form-control" id="motivo_texto" name="motivo_texto" rows="2" placeholder="Ej: Control de glucosa, segunda dosis de vacuna, dolor en la pata izquierda..."></textarea>
                    <div class="form-text">Úselo para especificar si seleccionó "Otro" o para añadir más detalles al motivo seleccionado.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 p-2 fs-5 mt-4">Confirmar Agendamiento de Cita</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php 
if (isset($conexion)) {
    $conexion->close(); 
}
?>