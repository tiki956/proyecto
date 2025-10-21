<?php
// =============================================================
// Archivo: generar_factura.php
// Objetivo: Generar un PDF de factura para una cita específica.
// =============================================================

// REQUIERE: La librería TCPDF instalada en tu proyecto
require_once('tcpdf/tcpdf.php'); // Asegúrate que esta ruta sea correcta
require_once('conexion.php');    // Tu archivo de conexión a la BD

// 1. Verificar el ID de la cita
if (!isset($_GET['cita_id']) || empty($_GET['cita_id'])) {
    die("Error: Faltan parámetros para generar la factura.");
}
$id_cita = intval($_GET['cita_id']); 
$datos = null; 

// 2. Obtener Datos de la Factura (Paciente, Dueño, Cita)
$sql_datos = "
    SELECT 
        C.ID_Cita, C.Fecha_Cita, C.Motivo_Cita, C.Estado_Cita, 
        P.Nombre_Mascota, P.Especie, P.Raza,
        O.Nombre_Completo AS Nombre_Dueño, O.DNI_Cedula, O.Telefono_Principal, O.Direccion
    FROM Citas C
    JOIN Pacientes P ON C.ID_Paciente = P.ID_Paciente
    JOIN Propietarios O ON P.ID_Propietario = O.ID_Propietario
    WHERE C.ID_Cita = $id_cita
";

$resultado = $conexion->query($sql_datos);

if ($resultado && $resultado->num_rows > 0) {
    $datos = $resultado->fetch_assoc();
} else {
    die("Error: No se encontraron datos para la Cita ID $id_cita.");
}

// 3. Obtener Detalles de Servicios/Productos de la Factura
// Aquí deberías tener una tabla adicional (e.g., Detalle_Factura)
// Por ahora, usaremos un detalle de ejemplo basado en el Motivo de la Cita:
$servicios = [
    ['Descripcion' => 'Consulta Médica General', 'Cantidad' => 1, 'Precio' => 25.00, 'Total' => 25.00],
    ['Descripcion' => 'Vacuna ' . htmlspecialchars($datos['Motivo_Cita']), 'Cantidad' => 1, 'Precio' => 15.00, 'Total' => 15.00],
    ['Descripcion' => 'Medicamento (Ejemplo)', 'Cantidad' => 2, 'Precio' => 5.00, 'Total' => 10.00],
];
$subtotal = array_sum(array_column($servicios, 'Total'));
$iva = $subtotal * 0.13; // Ejemplo de IVA del 13%
$total_final = $subtotal + $iva;


// ----------------------------------------------------------------------------------
// 4. CREACIÓN DEL PDF CON TCPDF
// ----------------------------------------------------------------------------------

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración básica del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Veterinaria');
$pdf->SetTitle("Factura Cita N. $id_cita");
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// HTML para la cabecera de la factura (Datos de la clínica y cliente)
$html_cabecera = '
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 60%;">
                <h1 style="color: #0d6efd;">Factura Veterinaria</h1>
                <p><strong>Número de Cita:</strong> ' . htmlspecialchars($datos['ID_Cita']) . '</p>
                <p><strong>Fecha de Emisión:</strong> ' . date('d/m/Y') . '</p>
            </td>
            <td style="width: 40%; text-align: right;">
                <p><strong>Clínica Veterinaria Amigos</strong></p>
                <p>RUC: 123456789</p>
                <p>Dirección: Calle Falsa 123, Ciudad</p>
                <p>Teléfono: 555-1234</p>
            </td>
        </tr>
    </table>
    <hr style="color: #0d6efd;">

    <h3 style="color: #28a745;">Datos del Cliente y Paciente</h3>
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border: 1px solid #ccc;">
        <tr>
            <td style="width: 50%;"><strong>Propietario:</strong> ' . htmlspecialchars($datos['Nombre_Dueño']) . '</td>
            <td style="width: 50%;"><strong>DNI/Cédula:</strong> ' . htmlspecialchars($datos['DNI_Cedula']) . '</td>
        </tr>
        <tr>
            <td style="width: 50%;"><strong>Mascota:</strong> ' . htmlspecialchars($datos['Nombre_Mascota']) . ' (' . htmlspecialchars($datos['Especie']) . ')</td>
            <td style="width: 50%;"><strong>Motivo de Cita:</strong> ' . htmlspecialchars($datos['Motivo_Cita']) . '</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Dirección:</strong> ' . htmlspecialchars($datos['Direccion']) . '</td>
        </tr>
    </table>
    <br><br>
';

// HTML para el detalle de la factura (Servicios)
$html_detalle = '
    <h3 style="color: #dc3545;">Detalle de Servicios</h3>
    <table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 55%;">Descripción</th>
                <th style="width: 15%; text-align: center;">Cant.</th>
                <th style="width: 15%; text-align: right;">Precio Unit.</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($servicios as $servicio) {
            $html_detalle .= '
            <tr>
                <td style="width: 55%;">' . htmlspecialchars($servicio['Descripcion']) . '</td>
                <td style="width: 15%; text-align: center;">' . $servicio['Cantidad'] . '</td>
                <td style="width: 15%; text-align: right;">$ ' . number_format($servicio['Precio'], 2) . '</td>
                <td style="width: 15%; text-align: right;">$ ' . number_format($servicio['Total'], 2) . '</td>
            </tr>';
        }

$html_detalle .= '
        </tbody>
    </table>
    <br>
';

// HTML para el total de la factura
$html_total = '
    <table border="0" cellpadding="5" cellspacing="0" style="width: 100%;">
        <tr>
            <td style="width: 70%;">&nbsp;</td>
            <td style="width: 30%; background-color: #eee; border: 1px solid #ccc;">
                <strong>Subtotal:</strong> <span style="float: right;">$ ' . number_format($subtotal, 2) . '</span>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;">&nbsp;</td>
            <td style="width: 30%; background-color: #eee; border: 1px solid #ccc;">
                <strong>IVA (13%):</strong> <span style="float: right;">$ ' . number_format($iva, 2) . '</span>
            </td>
        </tr>
        <tr>
            <td style="width: 70%;">&nbsp;</td>
            <td style="width: 30%; background-color: #0d6efd; color: white; border: 1px solid #0d6efd;">
                <span style="font-size: 14pt; font-weight: bold;">TOTAL A PAGAR:</span> <span style="float: right; font-size: 14pt; font-weight: bold;">$ ' . number_format($total_final, 2) . '</span>
            </td>
        </tr>
    </table>
    <br><br>
    <p style="text-align: center; border-top: 1px solid #ccc; padding-top: 10px;">¡Gracias por su confianza!</p>
';

// Escribir el HTML al PDF
$pdf->writeHTML($html_cabecera, true, false, true, false, '');
$pdf->writeHTML($html_detalle, true, false, true, false, '');
$pdf->writeHTML($html_total, true, false, true, false, '');

// 5. Salida del PDF
$nombre_archivo = "Factura_Paciente_" . $datos['Nombre_Mascota'] . "_Cita_" . $id_cita . ".pdf";

// 'I' para mostrar en el navegador
$pdf->Output($nombre_archivo, 'I');

// Si deseas que se descargue automáticamente, usa 'D':
// $pdf->Output($nombre_archivo, 'D');

$conexion->close();
?>