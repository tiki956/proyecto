<?php 
// =============================================================
// Archivo: index.php
// Versión: Diseño Elegante/Dinámico y con Campos de Hora/Fecha Inicial
// =============================================================
require_once 'conexion.php'; 

// --- Lógica de la Consulta del Listado ---
$sql_listado = "
    SELECT 
        P.ID_Paciente, 
        P.Nombre_Mascota, 
        P.Especie, 
        P.Raza,
        O.Nombre_Completo AS Nombre_Dueño, 
        O.DNI_Cedula,
        O.Telefono_Principal,
        (
            SELECT Fecha_Cita
            FROM Citas C
            WHERE C.ID_Paciente = P.ID_Paciente
            ORDER BY Fecha_Cita DESC
            LIMIT 1
        ) AS Ultima_Cita
    FROM Pacientes P
    JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
    ORDER BY O.Nombre_Completo ASC, P.Nombre_Mascota ASC
";

// Manejo de error de conexión suave para que el HTML se muestre
$resultado_listado = @$conexion->query($sql_listado) ?: (object)['num_rows' => 0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Veterinaria - Secretaria</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet"> 
</head>
<body>

    <div class="container mt-5 mb-5">
        
        <?php 
        // Mostrar el mensaje de error si viene de procesar.php (ej. DNI/Email duplicado)
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger mb-4">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>

        <h2 class="text-center text-primary">Registro de Nuevo Paciente y Propietario</h2>
        
        <div class="text-center mb-5">
            <a href="buscar_paciente.php" class="btn btn-primary btn-lg shadow-sm" style="font-size: 1.1rem;">
                🔍 Buscar Paciente, Gestionar Citas y Facturar
            </a>
        </div>

        <form id="registroForm" action="procesar.php" method="POST">
            
            <fieldset class="mb-5">
                <legend class="text-primary">Datos del Propietario</legend>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre_propietario" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_propietario" name="nombre_propietario" required>
                    </div>
                    <div class="col-md-6">
                        <label for="dni_cedula" class="form-label">DNI / Cédula</label>
                        <input type="text" class="form-control" id="dni_cedula" name="dni_cedula" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefono_principal" class="form-label">Teléfono Principal</label>
                        <input type="tel" class="form-control" id="telefono_principal" name="telefono_principal" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="col-12">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                </div>
            </fieldset>

            <fieldset class="mb-5">
                <legend class="text-success">Datos de la Mascota</legend>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nombre_mascota" class="form-label">Nombre de la Mascota</label>
                        <input type="text" class="form-control" id="nombre_mascota" name="nombre_mascota" required>
                    </div>
                    <div class="col-md-6">
                        <label for="especie" class="form-label">Especie</label>
                        <select class="form-select" id="especie" name="especie" required>
                            <option value="">Seleccionar...</option>
                            <option value="Perro">Perro</option>
                            <option value="Gato">Gato</option>
                            <option value="Ave">Ave</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="raza" class="form-label">Raza</label>
                        <input type="text" class="form-control" id="raza" name="raza">
                    </div>
                    <div class="col-md-4">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select class="form-select" id="sexo" name="sexo" required>
                            <option value="">Seleccionar...</option>
                            <option value="M">Macho</option>
                            <option value="H">Hembra</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                    </div>
                </div>

                <div class="row g-3 pt-3 mt-3 border-top border-2">
                    <h5 class="fw-bold text-secondary">📅 Detalle de la Primera Visita (Registro Inicial)</h5>
                    
                    <div class="col-md-6">
                        <label for="fecha_inicial" class="form-label">Fecha de la Visita</label>
                        <input type="date" class="form-control" id="fecha_inicial" name="fecha_inicial" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="hora_inicial" class="form-label">Hora de la Visita</label>
                        <input type="time" class="form-control" id="hora_inicial" name="hora_inicial" value="<?php echo date('H:i'); ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="motivo_inicial" class="form-label fw-bold">Motivo de la Primera Visita / Notas Iniciales</label>
                        <textarea class="form-control" id="motivo_inicial" name="motivo_inicial" rows="3" placeholder="Ej: Viene por primera vez, presenta diarrea hace 3 días. No tiene vacunas."></textarea>
                        <div class="form-text">Este dato se guardará como la primera cita histórica del paciente.</div>
                    </div>
                </div>
                </fieldset>

            <button type="submit" class="btn btn-success w-100 p-3 mb-5">Registrar Propietario y Mascota</button>
        </form>
        
        <h3 class="text-center text-secondary">Listado de Pacientes Registrados (<?php echo $resultado_listado->num_rows; ?>)</h3>
        
        <div class="table-responsive table-responsive-list">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-secondary sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Mascota</th>
                        <th>Especie / Raza</th>
                        <th>Dueño (Propietario)</th>
                        <th>DNI/Cédula</th>
                        <th>Teléfono</th>
                        <th>Última Cita</th> 
                        <th class="text-center">Gestión Rápida</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultado_listado->num_rows > 0) {
                        // Reiniciar el puntero si se usó @$conexion->query
                        $resultado_listado->data_seek(0); 
                        while($paciente = $resultado_listado->fetch_assoc()) {
                            $link_busqueda = "buscar_paciente.php?busqueda=" . urlencode($paciente['DNI_Cedula']);
                            $fecha_cita = !empty($paciente['Ultima_Cita']) ? date('d/m/Y H:i', strtotime($paciente['Ultima_Cita'])) : 'Sin Citas';

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($paciente['ID_Paciente']) . "</td>";
                            echo "<td>" . htmlspecialchars($paciente['Nombre_Mascota']) . "</td>";
                            echo "<td>" . htmlspecialchars($paciente['Especie']) . " / " . htmlspecialchars($paciente['Raza']) . "</td>";
                            echo "<td>" . htmlspecialchars($paciente['Nombre_Dueño']) . "</td>";
                            echo "<td>" . htmlspecialchars($paciente['DNI_Cedula']) . "</td>";
                            echo "<td>" . htmlspecialchars($paciente['Telefono_Principal']) . "</td>";
                            echo "<td>" . $fecha_cita . "</td>";
                            echo "<td class='text-center'>";
                            echo "<a href='" . $link_busqueda . "' class='btn btn-sm btn-info shadow-sm'>Ver Citas / Facturar</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>Aún no hay pacientes ni propietarios registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
<?php 
// Cerrar la conexión si existe
if (isset($conexion)) {
    $conexion->close(); 
}
?>