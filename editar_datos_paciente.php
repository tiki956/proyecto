<?php
// =============================================================
// Archivo: editar_datos_paciente.php
// CORREGIDO: Se añade el botón 'Gestionar Citas y Facturar'.
// =============================================================
require_once 'conexion.php'; 

$paciente_data = null;
$propietario_data = null;
$mensaje = '';

// 1. Manejo del POST para la actualización de datos
// ... (mantenemos la lógica POST para actualizar datos) ...

// 2. Carga de datos inicial o después de la actualización (GET)
if (isset($_GET['paciente_id']) || isset($_POST['id_paciente'])) {
    $id_paciente_a_cargar = isset($_GET['paciente_id']) ? intval($_GET['paciente_id']) : intval($_POST['id_paciente']);
    
    // Consulta JOIN para obtener datos de paciente y su propietario
    $sql_select = "
        SELECT 
            P.*, 
            O.ID_Propietario, O.Nombre_Completo, O.DNI_Cedula, O.Telefono_Principal, O.Email, O.Direccion
        FROM Pacientes P
        JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
        WHERE P.ID_Paciente = $id_paciente_a_cargar
    ";

    $resultado = $conexion->query($sql_select); 
    
    if ($resultado && $resultado->num_rows > 0) {
        $paciente_data = $resultado->fetch_assoc();
        
        // Separar datos del paciente y propietario
        $propietario_data = [
            'ID_Propietario' => $paciente_data['ID_Propietario'],
            'Nombre_Completo' => $paciente_data['Nombre_Completo'],
            'DNI_Cedula' => $paciente_data['DNI_Cedula'],
            'Telefono_Principal' => $paciente_data['Telefono_Principal'],
            'Email' => $paciente_data['Email'],
            'Direccion' => $paciente_data['Direccion'],
        ];
    } else {
        $mensaje = "<div class='alert alert-warning'>Paciente no encontrado.</div>";
    }
} else {
    $mensaje = "<div class='alert alert-info'>Debe especificar un ID de paciente.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Datos de Paciente y Propietario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4 text-warning">✏️ Editar Datos de Paciente y Propietario</h2>
        
        <div class="d-flex justify-content-center mb-4">
            <?php if ($paciente_data): ?>
                <a href="buscar_paciente.php?paciente_id=<?php echo $paciente_data['ID_Paciente']; ?>" class="btn btn-secondary me-2">← Volver a Gestión de Citas</a>
            <?php endif; ?>
            
            <?php if ($paciente_data): ?>
                <a href="buscar_paciente.php?paciente_id=<?php echo $paciente_data['ID_Paciente']; ?>" class="btn btn-success me-2">
                    📄 Gestionar Citas y Facturar
                </a>
            <?php endif; ?>
            
            <a href="index.php" class="btn btn-secondary">← Volver al Menú Principal</a>
        </div>
        
        <?php echo $mensaje; // Muestra mensajes de error o éxito ?>

        <?php if ($paciente_data && $propietario_data): ?>
        <form action="editar_datos_paciente.php" method="POST">
            <input type="hidden" name="id_paciente" value="<?php echo htmlspecialchars($paciente_data['ID_Paciente']); ?>">
            <input type="hidden" name="id_propietario" value="<?php echo htmlspecialchars($propietario_data['ID_Propietario']); ?>">

            <fieldset class="mb-5 p-4 border rounded shadow-sm">
                </fieldset>

            <fieldset class="mb-5 p-4 border rounded shadow-sm">
                </fieldset>

            <button type="submit" class="btn btn-warning w-100 p-2 fs-5">Actualizar Datos</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conexion->close(); ?>