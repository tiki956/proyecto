// =============================================================
// Archivo: script.js
// Propósito: Validaciones del lado del cliente antes de enviar.
// =============================================================

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registroForm');
    
    form.addEventListener('submit', function(event) {
        
        // --- Validación del Propietario ---
        const nombrePropietario = document.getElementById('nombre_propietario').value.trim();
        const telefonoPrincipal = document.getElementById('telefono_principal').value.trim();

        if (nombrePropietario === "" || telefonoPrincipal === "") {
            alert('¡Atención! El Nombre del Propietario y el Teléfono Principal son campos obligatorios.');
            event.preventDefault(); // Detener el envío del formulario
            return false;
        }

        // --- Validación de la Mascota ---
        const nombreMascota = document.getElementById('nombre_mascota').value.trim();
        const especie = document.getElementById('especie').value;

        if (nombreMascota === "" || especie === "") {
            alert('¡Atención! El Nombre de la Mascota y la Especie son campos obligatorios.');
            event.preventDefault(); 
            return false;
        }

        // Si llega aquí, el formulario pasa la validación y se envía a procesar.php
    });
});