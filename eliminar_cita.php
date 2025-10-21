<?php
// =============================================================
// Archivo: eliminar_cita.php
// Propósito: Procesar la eliminación de una cita.
// =============================================================
include 'conexion.php'; 

$id_cita = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_cita > 0) {
    // Consulta DELETE
    $sql_delete = "DELETE FROM Citas WHERE ID_Cita = $id_cita";
    
    if ($conn->query($sql_delete) === TRUE) {
        $mensaje = "Cita ID $id_cita eliminada correctamente.";
    } else {
        $mensaje = "Error al intentar eliminar la cita: " . $conn->error;
    }
} else {
    $mensaje = "ID de cita no válido para eliminar.";
}

$conn->close();

// Redireccionar de vuelta a la búsqueda, sin término de búsqueda
echo "<script>alert('$mensaje'); window.location.href = 'buscar_paciente.php';</script>";
exit;
?>