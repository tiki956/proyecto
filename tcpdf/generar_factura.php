<?php
// =============================================================
// Archivo: generar_factura.php
// CORREGIDO: Se consolidó el HTML para evitar errores de renderizado y saltos.
// =============================================================
require_once('tcpdf/tcpdf.php'); // Ruta a la librería TCPDF
require_once('conexion.php');    // Tu archivo de conexión a la base de datos

// Verificar que se hayan pasado los parámetros necesarios
if (!isset($_GET['cita_id'])) { 
    die("Error: Faltan parámetros para generar la factura. Se requiere 'cita_id'.");
}

$id_cita = intval($_GET['cita_id']); // Convertir a entero para seguridad

// --- 1. Obtener Datos de la Factura (usando la lógica de la cita) ---

$datos = null; 

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

$resultado_datos = $conexion->query($sql_datos); 

if ($resultado_datos && $resultado_datos->num_rows > 0) {
    $datos = $resultado_datos->fetch_assoc();
    
    // EJEMPLO DE DATOS DE SERVICIOS (Debe obtenerse de la BD en un sistema real)
    $servicios = [
        ['descripcion' => 'Consulta Veterinaria Básica', 'cantidad' => 1, 'precio_unitario' => 30.00],
        ['descripcion' => 'Aplicación de Vacuna (Triple Viral)', 'cantidad' => 1, 'precio_unitario' => 25.00],
        ['descripcion' => 'Desparasitación Interna', 'cantidad' => 1, 'precio_unitario' => 10.00],
    ];
    
    $total_factura = 0;
    foreach ($servicios as &$servicio) { 
        $servicio['subtotal'] = $servicio['cantidad'] * $servicio['precio_unitario'];
        $total_factura += $servicio['subtotal'];
    }
    unset($servicio); 
    
} else {
    $conexion->close();
    die("Error: Cita no encontrada o datos incompletos para ID_Cita: $id_cita.");
}

$conexion->close(); 


// --- 2. Generación del PDF con TCPDF ---

// Extender la clase TCPDF para personalizar encabezado y pie de página (Opcional)
class PDF extends TCPDF {
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 15, 'FACTURA VETERINARIA - CLÍNICA ANIMALIA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Line(10, 22, 200, 22); 
    }
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' - Gracias por su preferencia.', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear nuevo objeto PDF
$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración general del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Clínica Animalia');
$pdf->SetTitle('Factura Cita N° ' . $datos['ID_Cita']);
$pdf->SetSubject('Detalle de Cita y Servicios');

// Establecer fuentes predeterminadas y márgenes
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 5, PDF_MARGIN_RIGHT); 
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);

$pdf->SetFont('dejavusans', '', 10); 

$pdf->AddPage();

// ---------------------------------------------------------
// CONSOLIDAR TODO EL CONTENIDO HTML EN UNA SOLA CADENA ($html)
// ---------------------------------------------------------

$fecha_cita = date('d/m/Y H:i', strtotime($datos['Fecha_Cita']));
$fecha_emision = date('d/m/Y H:i'); 

$html = ''; // Variable ÚNICA para construir todo el HTML

// 1. Cabecera de la Factura
$html .= '
<h2 style="color:#007bff; text-align:center; margin-bottom: 20px;">FACTURA DE SERVICIOS - Cita N° ' . $datos['ID_Cita'] . '</h2>

<table cellspacing="0" cellpadding="4" border="0" style="width:100%;">
    <tr>
        <td style="width:50%; background-color:#f0f0f0; border:1px solid #ccc; padding: 8px;">
            <span style="font-weight:bold; color:#333;">DATOS DEL PROPIETARIO</span><br>
            Nombre: ' . htmlspecialchars($datos['Nombre_Dueño']) . '<br>
            DNI/Cédula: ' . htmlspecialchars($datos['DNI_Cedula']) . '<br>
            Teléfono: ' . htmlspecialchars($datos['Telefono_Principal']) . '<br>
            Dirección: ' . htmlspecialchars($datos['Direccion']) . '
        </td>
        <td style="width:50%; background-color:#f0f0f0; border:1px solid #ccc; padding: 8px;">
            <span style="font-weight:bold; color:#333;">INFORMACIÓN DE LA FACTURA</span><br>
            Fecha de Emisión: ' . $fecha_emision . '<br>
            Fecha y Hora de Cita: ' . $fecha_cita . '<br>
            Mascota: ' . htmlspecialchars($datos['Nombre_Mascota']) . ' (' . htmlspecialchars($datos['Especie']) . ')<br>
            Estado de Cita: <span style="font-weight:bold; color:#007bff;">' . htmlspecialchars($datos['Estado_Cita']) . '</span>
        </td>
    </tr>
</table>
<br><br>
';

// 2. Detalle de la Cita
$html .= '
<h3 style="color:#28a745; margin-bottom: 10px;">DETALLE DE LA CITA</h3>
<table cellspacing="0" cellpadding="5" border="1" style="width:100%;">
    <tr style="background-color:#d4edda; font-weight:bold; color:#333;">
        <td style="width:100%;">Motivo de la Consulta</td>
    </tr>
    <tr>
        <td>' . nl2br(htmlspecialchars($datos['Motivo_Cita'])) . '</td>
    </tr>
</table>
<br><br>
';

// 3. Resumen de Cobros
$html .= '
<h3 style="color:#dc3545; margin-bottom: 10px;">RESUMEN DE COBROS</h3>
<table cellspacing="0" cellpadding="5" border="1" style="width:100%;">
    <tr style="background-color:#f8d7da; font-weight:bold; color:#333;">
        <td style="width:60%;">Descripción del Servicio/Producto</td>
        <td style="width:20%; text-align:right;">Cantidad</td>
        <td style="width:20%; text-align:right;">Subtotal (USD)</td>
    </tr>
';

foreach ($servicios as $servicio) {
    $html .= '
    <tr>
        <td style="width:60%;">' . htmlspecialchars($servicio['descripcion']) . '</td>
        <td style="width:20%; text-align:right;">' . htmlspecialchars($servicio['cantidad']) . '</td>
        <td style="width:20%; text-align:right;">$' . number_format($servicio['subtotal'], 2) . '</td>
    </tr>
    ';
}

$html .= '
    <tr style="font-weight:bold; background-color:#e0f7fa;">
        <td colspan="2" style="text-align:right; border-top:2px solid #333;">TOTAL A PAGAR:</td>
        <td style="text-align:right; background-color:#ffffcc; border-top:2px solid #333;">$' . number_format($total_factura, 2) . '</td>
    </tr>
</table>
<br><br>

<p style="text-align:center; font-size: 9pt; color: #555;">Esta es una factura generada automáticamente. Para cualquier consulta, contacte a la clínica.</p>
<br><br><br>
<p style="text-align:center; border-top: 1px solid #000; width: 40%; margin: auto; padding-top: 5px;">Firma Autorizada</p>
';

// **ÚNICA LLAMADA A writeHTML**
$pdf->writeHTML($html, true, false, true, false, '');

// --- 3. Salida del PDF ---

$nombre_archivo = 'Factura_Cita_' . $datos['ID_Cita'] . '_' . date('Ymd_His') . '.pdf';

$pdf->Output($nombre_archivo, 'I'); 
exit;
?>