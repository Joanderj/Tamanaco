<?php
// Iniciar sesión para enviar mensajes entre páginas
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables para los mensajes
$mensaje_error = "";

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre_cargo = isset($_POST['nombre_cargo']) ? trim($_POST['nombre_cargo']) : "";
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : "";

    // Validar que el nombre del cargo no esté vacío
    if (empty($nombre_cargo)) {
        $mensaje_error = "El nombre del cargo es obligatorio.";
    } else {
        // Verificar si el cargo ya existe
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM cargo WHERE nombre_cargo = ?");
        $stmt_validar->bind_param("s", $nombre_cargo);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "El cargo ya existe. Por favor, ingrese un nombre único.";
        } else {
            // Insertar el cargo
            $stmt = $conexion->prepare("INSERT INTO cargo (nombre_cargo, descripcion, date_create, status) VALUES (?, ?, NOW(), 1)");
            $stmt->bind_param("ss", $nombre_cargo, $descripcion);

            if ($stmt->execute()) {
                // Guardar mensaje de éxito en la sesión
                $_SESSION['mensaje_exito'] = "El cargo se guardó correctamente.";

                // Redirigir a cargo.php
                header("Location: cargo.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar el cargo: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_cargo.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>