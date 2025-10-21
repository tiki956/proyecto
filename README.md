# 🐾 Sistema de Gestión para Secretaría de Veterinaria

## 📝 Descripción del Proyecto

Este proyecto es una aplicación web simple, desarrollada en **PHP y MySQL**, diseñada para facilitar las tareas administrativas de la secretaria de una clínica veterinaria.

El sistema permite la **gestión completa del ciclo de vida del paciente** (mascota y propietario), desde el registro inicial hasta la facturación de servicios y el agendamiento de citas médicas.

## ✨ Funcionalidades Clave

* **Registro Completo de Paciente:** Registra datos del propietario (nombre, DNI/Cédula, contacto) y de la mascota (nombre, especie, raza, sexo, fecha de nacimiento).
* **Gestión de Visita Inicial:** Permite registrar la fecha, hora y el motivo de la primera visita del paciente al momento de la creación (lo que genera automáticamente la primera cita histórica).
* **Búsqueda y Gestión:** Permite buscar pacientes por DNI, nombre del dueño o nombre de la mascota, y acceder a su ficha para gestión.
* **Agendamiento de Citas:** Permite programar nuevas citas médicas, especificando fecha, **hora** y motivo.
* **Facturación (Integración):** Preparado para generar facturas de servicios a partir de una cita específica (usando la integración con **TCPDF**).
* **Listado Rápido:** Muestra un listado de todos los pacientes registrados en la página principal (`index.php`) con su última cita registrada.

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP
* **Base de Datos:** MySQL / MariaDB
* **Frontend/Estilos:** HTML5, Bootstrap 5, CSS
* **Librerías Adicionales:** TCPDF (para generación de PDFs/Facturas)

## 📁 Estructura de Archivos

| Archivo | Propósito |
| :--- | :--- |
| `index.php` | Página principal. Contiene el formulario de registro y el listado de pacientes. |
| `procesar.php` | Script PHP que maneja la lógica de inserción de nuevos Propietarios y Pacientes. Incluye validación anti-duplicados (DNI/Email). |
| `conexion.php` | Script para establecer la conexión a la base de datos (MySQLi). |
| `buscar_paciente.php` | Interfaz de búsqueda y gestión de pacientes (muestra información y listado de citas). |
| `agendar_cita.php` | Formulario y lógica para registrar una nueva cita médica (Fecha y Hora). |
| `editar_datos_paciente.php` | (Asumiendo su existencia) Formulario para modificar los datos del Propietario y la Mascota. |
| `generar_factura.php` | (Requiere TCPDF) Lógica para generar y descargar la factura en formato PDF. |
| `style.css` | Archivo de estilos CSS para un diseño más elegante y limpio. |
| `README.md` | Este archivo. |

## ⚙️ Requisitos y Configuración

### 1. Requisitos del Servidor

Para ejecutar este proyecto, necesitas un entorno de servidor local:

* **Servidor Web:** Apache (incluido en XAMPP o WAMP).
* **PHP:** Versión 7.x o superior.
* **Base de Datos:** MySQL o MariaDB.

### 2. Configuración de la Base de Datos

1.  **Crea la base de datos** en tu gestor (por ejemplo, phpMyAdmin). El nombre de la base de datos debe coincidir con el que uses en `conexion.php` (ej: `mi_sistema_veterinario`).
2.  **Define las tablas** `Propietarios`, `Pacientes` y `Citas`. (Basado en la funcionalidad, se asume el siguiente esquema básico):

    ```sql
    -- Tabla Propietarios
    CREATE TABLE Propietarios (
        ID_Propietario INT AUTO_INCREMENT PRIMARY KEY,
        Nombre_Completo VARCHAR(100) NOT NULL,
        DNI_Cedula VARCHAR(20) UNIQUE NOT NULL,
        Telefono_Principal VARCHAR(20) NOT NULL,
        Email VARCHAR(100) UNIQUE,
        Direccion VARCHAR(255)
    );

    -- Tabla Pacientes (Mascotas)
    CREATE TABLE Pacientes (
        ID_Paciente INT AUTO_INCREMENT PRIMARY KEY,
        ID_Propietario INT,
        Nombre_Mascota VARCHAR(100) NOT NULL,
        Especie VARCHAR(50) NOT NULL,
        Raza VARCHAR(100),
        Sexo CHAR(1), -- 'M' o 'H'
        Fecha_Nacimiento DATE,
        FOREIGN KEY (ID_Propietario) REFERENCES Propietarios(ID_Propietario)
    );

    -- Tabla Citas
    CREATE TABLE Citas (
        ID_Cita INT AUTO_INCREMENT PRIMARY KEY,
        ID_Paciente INT,
        Fecha_Cita DATETIME NOT NULL, -- Almacena Fecha Y HORA
        Motivo_Cita TEXT NOT NULL,
        Estado_Cita VARCHAR(50) NOT NULL, -- Ej: 'Pendiente', 'Realizada', 'Cancelada'
        FOREIGN KEY (ID_Paciente) REFERENCES Pacientes(ID_Paciente)
    );
    ```

### 3. Ajustar `conexion.php`

Asegúrate de que tu archivo `conexion.php` contenga las credenciales correctas para tu servidor local:

```php
<?php
// Archivo: conexion.php
$servidor = "localhost"; 
$usuario = "root";       // O el usuario que uses
$password = "";          // O la contraseña de tu base de datos
$base_de_datos = "mi_sistema_veterinario"; // Usa el nombre de tu BD

// Crear conexión
$conexion = new mysqli($servidor, $usuario, $password, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
    // Es buena práctica usar die() en entornos de desarrollo para detener el script
    // En producción, se debería manejar este error de forma más elegante.
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Configurar el conjunto de caracteres a UTF-8 para evitar problemas con acentos
$conexion->set_charset("utf8");
?>
