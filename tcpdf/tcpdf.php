<?php
// =============================================================
// Archivo: generar_factura.php
// Propósito: Generar un PDF de factura para una cita específica.
// REQUIERE: La librería TCPDF instalada en tu proyecto.
// =============================================================
include 'conexion.php'; 

// Ajusta esta ruta si tu librería TCPDF está en otro lugar
require_once('tcpdf/tcpdf.php'); 

$id_cita = isset($_GET['cita_id']) ? intval($_GET['cita_id']) : 0;

if ($id_cita == 0) {
    die("Error: ID de cita no proporcionado o no válido.");
}

// --- PASO 1: Obtener todos los datos necesarios para la factura ---
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

$resultado_datos = $conn->query($sql_datos);

if ($resultado_datos->num_rows == 0) {
    die("Error: Cita no encontrada o datos incompletos.");
}

$datos = $resultado_datos->fetch_assoc();
$conn->close();

// --- PASO 2: Generación del PDF con TCPDF ---

// Extender la clase TCPDF para personalizar encabezado y pie de página (Opcional)
class PDF extends TCPDF {
    // Encabezado
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 15, 'FACTURA VETERINARIA - CLÍNICA [TU NOMBRE AQUÍ]', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    // Pie de página
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . ' - Gracias por su preferencia.', 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Crear nuevo objeto PDF
$pdf = new PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Establecer información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Veterinaria');
$pdf->SetTitle('Factura Cita N° ' . $datos['ID_Cita']);
$pdf->SetSubject('Detalle de Cita y Servicios');

// Configurar fuentes
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
$pdf->setFontSubsetting(true);

$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

// ---------------------------------------------------------
// ESTRUCTURA DEL CONTENIDO HTML para el PDF
// ---------------------------------------------------------

$fecha_cita = date('d/m/Y H:i', strtotime($datos['Fecha_Cita']));
$fecha_emision = date('d/m/Y');

$html = '
<h2 style="color:#007bff; text-align:center;">FACTURA N° ' . $datos['ID_Cita'] . '</h2>
<table cellspacing="0" cellpadding="4" border="0" style="width:100%;">
    <tr>
        <td style="width:50%; background-color:#f0f0f0; border:1px solid #ccc;">
            <strong>DATOS DEL PROPIETARIO</strong><br>
            Nombre: ' . htmlspecialchars($datos['Nombre_Dueño']) . '<br>
            DNI/Cédula: ' . htmlspecialchars($datos['DNI_Cedula']) . '<br>
            Teléfono: ' . htmlspecialchars($datos['Telefono_Principal']) . '<br>
            Dirección: ' . htmlspecialchars($datos['Direccion']) . '
        </td>
        <td style="width:50%; background-color:#f0f0f0; border:1px solid #ccc;">
            <strong>DATOS DE LA FACTURA</strong><br>
            Fecha de Emisión: ' . $fecha_emision . '<br>
            Fecha de Cita: ' . $fecha_cita . '<br>
            Estado: ' . htmlspecialchars($datos['Estado_Cita']) . '
        </td>
    </tr>
</table>
<br><br>

<h3 style="color:#28a745;">DETALLE DEL PACIENTE Y MOTIVO</h3>
<table cellspacing="0" cellpadding="5" border="1" style="width:100%;">
    <tr style="background-color:#d4edda; font-weight:bold;">
        <td style="width:30%;">Mascota</td>
        <td style="width:20%;">Especie</td>
        <td style="width:50%;">Motivo de la Cita</td>
    </tr>
    <tr>
        <td>' . htmlspecialchars($datos['Nombre_Mascota']) . '</td>
        <td>' . htmlspecialchars($datos['Especie']) . ' (' . htmlspecialchars($datos['Raza']) . ')</td>
        <td>' . htmlspecialchars($datos['Motivo_Cita']) . '</td>
    </tr>
</table>
<br><br>

<h3 style="color:#dc3545;">RESUMEN DE COBROS (EJEMPLO)</h3>
<table cellspacing="0" cellpadding="5" border="1" style="width:100%;">
    <tr style="background-color:#f8d7da; font-weight:bold;">
        <td style="width:60%;">Descripción</td>
        <td style="width:20%; text-align:right;">Cantidad</td>
        <td style="width:20%; text-align:right;">Subtotal</td>
    </tr>
    <tr>
        <td>Consulta Veterinaria Básica (Asociada a la Cita)</td>
        <td style="text-align:right;">1</td>
        <td style="text-align:right;">$30.00</td>
    </tr>
    <tr>
        <td>Medicamento X (ejemplo)</td>
        <td style="text-align:right;">2</td>
        <td style="text-align:right;">$15.00</td>
    </tr>
    <tr style="font-weight:bold;">
        <td colspan="2" style="text-align:right;">TOTAL A PAGAR:</td>
        <td style="text-align:right; background-color:#ffffcc;">$45.00</td>
    </tr>
</table>
<br><br>

<p style="text-align:center;">Esta factura se genera para la cita registrada con ID ' . $datos['ID_Cita'] . '.</p>
';

// Escribir el HTML
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Cerrar y generar el documento PDF
$pdf->Output('Factura_Cita_' . $datos['ID_Cita'] . '.pdf', 'I'); // 'I' envía el PDF al navegador
exit;