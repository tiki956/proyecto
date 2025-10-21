<?php
// =============================================================
// Archivo: citas.php
// Propósito: Mostrar todas las citas programadas con opciones de Edición y Eliminación.
// =============================================================
include 'conexion.php'; 

// Consulta para obtener las citas, incluyendo datos de la mascota y el dueño.
$sql_citas = "
    SELECT 
        C.ID_Cita, 
        C.Fecha_Cita, 
        C.Motivo_Cita, 
        C.Estado_Cita,
        P.Nombre_Mascota, 
        P.Especie,
        O.Nombre_Completo AS Nombre_Dueño
    FROM Citas C
    JOIN Pacientes P ON C.ID_Paciente = P.ID_Paciente
    JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
    ORDER BY C.Fecha_Cita ASC
";

$resultado_citas = $conn->query($sql_citas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .table-responsive { max-height: 80vh; overflow-y: auto; }
        .btn-action { margin-right: 5px; }
    </style>
</head>
<body>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4 text-primary">Gestión de Citas Reservadas</h2>
        
        <div class="d-flex justify-content-between mb-4">
            <a href="index.php" class="btn btn-secondary">← Volver al Registro</a>
            </div>

        <div class="table-responsive shadow-sm border rounded">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-primary sticky-top">
                    <tr>
                        <th>ID Cita</th>
                        <th>Fecha y Hora</th>
                        <th>Mascota</th>
                        <th>Dueño</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado_citas->num_rows > 0) {
                        while($cita = $resultado_citas->fetch_assoc()) {
                            // Formatear fecha para mejor lectura
                            $fecha_formateada = date('d/m/Y H:i', strtotime($cita['Fecha_Cita']));

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($cita['ID_Cita']) . "</td>";
                            echo "<td>" . htmlspecialchars($fecha_formateada) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['Nombre_Mascota']) . " (" . htmlspecialchars($cita['Especie']) . ")</td>";
                            echo "<td>" . htmlspecialchars($cita['Nombre_Dueño']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['Motivo_Cita']) . "</td>";
                            echo "<td>" . htmlspecialchars($cita['Estado_Cita']) . "</td>";
                            echo "<td class='text-center'>";
                            // Enlace para editar la cita
                            echo "<a href='editar_cita.php?id=" . $cita['ID_Cita'] . "' class='btn btn-sm btn-warning btn-action'>Editar</a>";
                            // Enlace para eliminar (usando JS para confirmar)
                            echo "<a href='eliminar_cita.php?id=" . $cita['ID_Cita'] . "' class='btn btn-sm btn-danger btn-action' onclick='return confirmarEliminacion(" . $cita['ID_Cita'] . ")'>Eliminar</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No hay citas programadas en este momento.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // Función de confirmación para eliminar
        function confirmarEliminacion(id) {
            return confirm('¿Está seguro de que desea eliminar la cita ID ' + id + '? Esta acción es irreversible.');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>