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
        header("Location: formulario_inventario_herramienta.php");
        exit();
    }

    // Variables que recibirás del formulario
    $status_solicitud = 2; // Estado de la solicitud (actualizar según sea necesario)
    $id_almacen_destino_traslado = $_POST['almacen'];
    $id_herramienta = $_POST['herramienta'];
    $id_solicitud = $_POST['solicitud'];
    $cantidad = $_POST['cantidad']; // Asegúrate de que esta variable esté definida en el formulario

    // Actualizar el estado de la solicitud
    $update_solicitud_query = "UPDATE solicitudes SET id_status = ? WHERE id_solicitud = ?";
    $update_solicitud_stmt = $conexion->prepare($update_solicitud_query);
    $update_solicitud_stmt->bind_param("ii", $status_solicitud, $id_solicitud);

    // Sumar la cantidad al almacén de destino
    $query_destino = "SELECT cantidad FROM inventario_herramientas WHERE herramienta_id = ? AND id_almacen = ?";
    $stmt_destino = $conexion->prepare($query_destino);
    $stmt_destino->bind_param("si", $id_herramienta, $id_almacen_destino_traslado);
    $stmt_destino->execute();
    $resultado_destino = $stmt_destino->get_result();
    $row_destino = $resultado_destino->fetch_assoc();

    if ($row_destino) {
        // Si existe, actualizar la cantidad
        $cantidad_destino = $row_destino['cantidad'];
        $nueva_cantidad_destino = $cantidad_destino + $cantidad;

        // Actualizar la cantidad en el almacén de destino
        $update_query_destino = "UPDATE inventario_herramientas SET cantidad = ? WHERE herramienta_id = ? AND id_almacen = ?";
        $update_stmt_destino = $conexion->prepare($update_query_destino);
        $update_stmt_destino->bind_param("isi", $nueva_cantidad_destino, $id_herramienta, $id_almacen_destino_traslado);
        $update_stmt_destino->execute();
    } else {
        // Si no existe la herramienta en el almacén de destino, insertarlo
        $insert_query = "INSERT INTO inventario_herramientas (herramienta_id, cantidad, id_almacen) VALUES (?, ?, ?)";
        $insert_stmt = $conexion->prepare($insert_query);
        $insert_stmt->bind_param("isi", $id_herramienta, $cantidad, $id_almacen_destino_traslado);
        $insert_stmt->execute();
    }

    // Ejecutar la consulta para actualizar el estado de la solicitud
    if ($update_solicitud_stmt->execute()) {
        // Mensaje de éxito para la actualización de la solicitud
        $_SESSION['success'] = 'Estado de la solicitud actualizado correctamente.';
    } else {
        // Manejo de errores
        $_SESSION['error'] = 'Error al actualizar el estado de la solicitud: ' . $update_solicitud_stmt->error;
    }

    // Cerrar las declaraciones
    $update_solicitud_stmt->close();
    if (isset($update_stmt_destino)) $update_stmt_destino->close();
    if (isset($insert_stmt)) $insert_stmt->close();
    $stmt_destino->close();

    // Cerrar la conexión
    $conexion->close();

    // Redirigir a inventario_herramienta
    header('Location: inventario_herramienta.php');
    exit();
}

// Si no se envió el formulario, redirigir a la página del formulario
header("Location: formulario_inventario_herramienta.php");
exit();
?>
