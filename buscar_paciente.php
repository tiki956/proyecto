<?php
// =============================================================
// Archivo: buscar_paciente.php
// Objetivo: Búsqueda, Gestión de Citas y BOTÓN DE FACTURACIÓN.
// =============================================================
require_once 'conexion.php'; 

$pacientes_encontrados = []; 
$busqueda_activa = false;
$termino = '';
$paciente_seleccionado_data = null;
$mensaje = '';

// Lógica de Búsqueda (DNI, Nombre Mascota, Nombre Dueño)
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda_activa = true;
    $termino = $conexion->real_escape_string(trim($_GET['busqueda']));
    
    // Consulta para encontrar pacientes por DNI, nombre de dueño o nombre de mascota
    $sql_busqueda = "
        SELECT 
            P.ID_Paciente, 
            P.Nombre_Mascota, 
            P.Especie, 
            P.Raza,
            O.Nombre_Completo AS Nombre_Dueño, 
            O.DNI_Cedula,
            O.Telefono_Principal
        FROM Pacientes P
        JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
        WHERE O.DNI_Cedula LIKE '%$termino%'
           OR O.Nombre_Completo LIKE '%$termino%'
           OR P.Nombre_Mascota LIKE '%$termino%'
    ";

    $resultado_busqueda = $conexion->query($sql_busqueda);

    if ($resultado_busqueda) {
        if ($resultado_busqueda->num_rows > 0) {
            while ($fila = $resultado_busqueda->fetch_assoc()) {
                $pacientes_encontrados[] = $fila;
            }
            
            // Si solo se encuentra un paciente, se carga para gestión inmediata
            if (count($pacientes_encontrados) == 1) {
                // Redirigir para usar el paciente_id, evitando resultados confusos si hay nombres similares
                header("Location: buscar_paciente.php?paciente_id=" . $pacientes_encontrados[0]['ID_Paciente']);
                exit();
            }
        } else {
            $mensaje = "<div class='alert alert-warning mt-4'>⚠️ No se encontraron pacientes ni dueños con el término '$termino'.</div>";
        }
    } else {
         $mensaje = "<div class='alert alert-danger mt-4'>❌ Error en la consulta de búsqueda: " . $conexion->error . "</div>";
    }
} 

// Lógica de Gestión de Citas (Cuando ya se ha seleccionado un paciente por ID)
if (isset($_GET['paciente_id']) && intval($_GET['paciente_id']) > 0) {
    $id_paciente_seleccionado = intval($_GET['paciente_id']);
    
    // 1. Consulta de Datos del Paciente y Dueño
    $sql_datos_paciente = "
        SELECT 
            P.ID_Paciente, P.Nombre_Mascota, P.Especie, P.Raza, P.ID_Propietario,
            O.Nombre_Completo AS Nombre_Dueño, O.DNI_Cedula
        FROM Pacientes P
        JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
        WHERE P.ID_Paciente = $id_paciente_seleccionado
    ";
    $res_datos_paciente = $conexion->query($sql_datos_paciente);
    
    if ($res_datos_paciente && $res_datos_paciente->num_rows == 1) {
        $paciente_seleccionado_data = $res_datos_paciente->fetch_assoc();
    } else {
        $mensaje = "<div class='alert alert-danger mt-4'>❌ Error: Paciente ID $id_paciente_seleccionado no encontrado.</div>";
    }

    // 2. Consulta de Citas (Se hará en la sección HTML)
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Paciente y Gestionar Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .btn-action { margin-left: 5px; }
    </style>
</head>
<body>

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4 text-primary">🔍 Buscar Paciente y Gestionar Citas</h2>
        
        <div class="d-flex justify-content-start mb-4">
            <a href="index.php" class="btn btn-secondary">← Volver al Menú Principal</a>
        </div>

        <form method="GET" action="buscar_paciente.php" class="mb-5 p-3 border rounded shadow-sm">
            <div class="input-group">
                <input type="text" class="form-control form-control-lg" name="busqueda" placeholder="Ingrese DNI, Nombre de Dueño o Mascota" value="<?php echo htmlspecialchars($termino); ?>" required>
                <button type="submit" class="btn btn-primary btn-lg">Buscar Paciente</button>
            </div>
        </form>

        <?php echo $mensaje; // Muestra errores de consulta o paciente no encontrado ?>

        <?php if ($paciente_seleccionado_data): ?>
            <?php
            // Datos del paciente y dueño
            $nombre_dueno = htmlspecialchars($paciente_seleccionado_data['Nombre_Dueño']);
            $nombre_mascota = htmlspecialchars($paciente_seleccionado_data['Nombre_Mascota']);
            $id_paciente = htmlspecialchars($paciente_seleccionado_data['ID_Paciente']);
            $dni_cedula = htmlspecialchars($paciente_seleccionado_data['DNI_Cedula']);

            // Consulta de Citas para este paciente
            $sql_citas = "
                SELECT ID_Cita, Fecha_Cita, Motivo_Cita, Estado_Cita
                FROM Citas
                WHERE ID_Paciente = $id_paciente
                ORDER BY Fecha_Cita DESC
            ";
            $resultado_citas = $conexion->query($sql_citas);
            $num_citas = $resultado_citas ? $resultado_citas->num_rows : 0;
            ?>

            <div class="card mb-4 shadow-lg border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Paciente: <?php echo $nombre_mascota; ?> (<?php echo htmlspecialchars($paciente_seleccionado_data['Especie']); ?>)</h4>
                    <small>Dueño: <?php echo $nombre_dueno; ?> | DNI/Cédula: <?php echo $dni_cedula; ?> | ID Paciente: <?php echo $id_paciente; ?></small>
                </div>
                
                <div class="card-body">
                    <div class="d-flex justify-content-start mb-3">
                        <a href="agendar_cita.php?paciente_id=<?php echo $id_paciente; ?>" class="btn btn-success me-2">➕ Agendar Nueva Cita</a>
                        <a href="editar_datos_paciente.php?paciente_id=<?php echo $id_paciente; ?>" class="btn btn-warning me-2">✏️ Editar Datos de Paciente/Dueño</a>
                    </div>

                    <h5 class="mt-4 border-bottom pb-2">Citas Programadas/Históricas (<?php echo $num_citas; ?>)</h5>

                    <?php if ($num_citas > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID Cita</th>
                                    <th>Fecha y Hora</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($cita = $resultado_citas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cita['ID_Cita']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($cita['Fecha_Cita'])); ?></td>
                                    <td><?php echo htmlspecialchars($cita['Motivo_Cita']); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            if ($cita['Estado_Cita'] == 'Realizada') echo 'bg-success';
                                            elseif ($cita['Estado_Cita'] == 'Pendiente') echo 'bg-warning text-dark';
                                            else echo 'bg-danger'; 
                                            ?>">
                                            <?php echo htmlspecialchars($cita['Estado_Cita']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="editar_cita.php?cita_id=<?php echo $cita['ID_Cita']; ?>" class="btn btn-sm btn-info btn-action">Editar Cita</a>
                                        <a href="eliminar_cita.php?cita_id=<?php echo $cita['ID_Cita']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmarEliminacion();">Eliminar Cita</a>
                                        
                                        <?php if ($cita['Estado_Cita'] == 'Realizada'): ?>
                                             <a href="generar_factura.php?cita_id=<?php echo $cita['ID_Cita']; ?>" class="btn btn-sm btn-dark btn-action" target="_blank">📄 Generar PDF (Factura)</a>
                                        <?php else: ?>
                                             <button class="btn btn-sm btn-secondary btn-action" disabled>Factura Inactiva</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-light mt-3">Este paciente no tiene citas registradas.</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($busqueda_activa && count($pacientes_encontrados) > 1): ?>
            <div class='alert alert-info mt-4'>Se encontraron **<?php echo count($pacientes_encontrados); ?>** coincidencias. Seleccione un paciente:</div>
            <div class="list-group">
                <?php foreach ($pacientes_encontrados as $p): ?>
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Mascota:</strong> <?php echo htmlspecialchars($p['Nombre_Mascota']); ?> (<?php echo htmlspecialchars($p['Especie']); ?>) <br>
                            <strong>Dueño:</strong> <?php echo htmlspecialchars($p['Nombre_Dueño']); ?> | DNI: <?php echo htmlspecialchars($p['DNI_Cedula']); ?> | Tel: <?php echo htmlspecialchars($p['Telefono_Principal']); ?>
                        </div>
                        <a href="buscar_paciente.php?paciente_id=<?php echo $p['ID_Paciente']; ?>" class="btn btn-info">Gestionar Citas →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <script>
            function confirmarEliminacion() {
                return confirm("¿Está seguro de que desea eliminar esta cita? Esta acción es irreversible.");
            }
        </script>
    </div>
</body>
</html>
<?php $conexion->close(); ?>