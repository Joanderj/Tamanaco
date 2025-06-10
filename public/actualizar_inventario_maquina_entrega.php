<?php
// Iniciar sesión
session_start();

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

    // Verificar la conexión
    if ($conexion->connect_error) {
        $_SESSION['error'] = "Error en la conexión: " . $conexion->connect_error;
        header("Location: formulario_inventario_maquina.php");
        exit();
    }

    // Variables que recibirás del formulario
    $status_solicitud = 2; // Asegúrate de que estas variables estén definidas
    $status = 1; // Asegúrate de que estas variables estén definidas
    $sede_entrada = $_POST['sede'];
    $codigo =  $_POST['maquina'];
    $id_solicitud = $_POST['solicitud'];

    // Actualizar estado y sede en maquina_unica
    $update_status_query = "UPDATE maquina_unica SET id_status = ?, id_sede = ? WHERE id_maquina_unica = ?";
    $update_status_stmt = $conexion->prepare($update_status_query);
    $update_status_stmt->bind_param("iis", $status, $sede_entrada, $codigo);

    // Ejecutar la consulta
    if ($update_status_stmt->execute()) {
        // Establecer mensaje de éxito en la sesión
        $_SESSION['success'] = 'Estado y sede actualizados correctamente.';
    } else {
        // Manejo de errores
        $_SESSION['error'] = 'Error al actualizar el estado y sede: ' . $update_status_stmt->error;
    }

    // Cerrar la declaración
    $update_status_stmt->close();

    // Actualizar el estado de la solicitud
    $update_solicitud_query = "UPDATE solicitudes SET id_status = ? WHERE id_solicitud = ?";
    $update_solicitud_stmt = $conexion->prepare($update_solicitud_query);
    $update_solicitud_stmt->bind_param("ii", $status_solicitud, $id_solicitud);

    // Ejecutar la consulta
    if ($update_solicitud_stmt->execute()) {
        // Mensaje de éxito para la actualización de la solicitud
        $_SESSION['success'] .= ' Estado de la solicitud actualizado correctamente.';
    } else {
        // Manejo de errores
        $_SESSION['error'] .= ' Error al actualizar el estado de la solicitud: ' . $update_solicitud_stmt->error;
    }

    // Cerrar la declaración
    $update_solicitud_stmt->close();

    // Cerrar la conexión
    $conexion->close();

    // Redirigir a inventario_maquina
    header('Location: inventario_maquina.php');
    exit();
}

// Si no se envió el formulario, redirigir a la página del formulario
header("Location: formulario_inventario_maquina.php");
exit();
?>
