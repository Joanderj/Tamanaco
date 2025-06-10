<?php
// Iniciar sesión para mensajes entre páginas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables para los mensajes
$mensaje_error = "";
$mensaje_exito = "";

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre_proveedor = isset($_POST['nombre_proveedor']) ? trim($_POST['nombre_proveedor']) : "";
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : "";
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : "";

    // Validar que los campos obligatorios no estén vacíos
    if (empty($nombre_proveedor)) {
        $mensaje_error = "El nombre del proveedor es obligatorio.";
    } elseif (empty($email)) {
        $mensaje_error = "El correo electrónico del proveedor es obligatorio.";
    } else {
        // Verificar si el proveedor ya existe basado en el email
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM proveedor WHERE email = ?");
        $stmt_validar->bind_param("s", $email);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "El proveedor ya existe con este correo electrónico.";
        } else {
            // Insertar el proveedor en la base de datos
            $stmt = $conexion->prepare("INSERT INTO proveedor (nombre_proveedor, telefono, email, direccion, id_status, date_created) VALUES (?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssss", $nombre_proveedor, $telefono, $email, $direccion);

            if ($stmt->execute()) {
                // Mensaje de éxito
                $_SESSION['mensaje_exito'] = "El proveedor fue registrado exitosamente.";
                header("Location: proveedor.php"); // Redirigir a la lista de proveedores
                exit();
            } else {
                $mensaje_error = "Error al guardar el proveedor: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_proveedor.php"); // Redirigir al formulario
    exit();
}

// Cerrar conexión
$conexion->close();
?>