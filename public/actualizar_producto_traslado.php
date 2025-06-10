<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        $_SESSION['error'] = "Error en la conexión: " . $conexion->connect_error;
        header("Location: formulario_inventario_producto_traslado.php");
        exit();
    }

    // Capturar datos del formulario y manejar valores nulos
    $id_producto = !empty($_POST['producto']) ? $_POST['producto'] : null; // Asumimos que 'producto' es el ID
    $cantidad = !empty($_POST['cantidad']) ? (int)$_POST['cantidad'] : null; // Convertir a entero
    $id_almacen = !empty($_POST['almacen']) ? $_POST['almacen'] : null;
    $tiempo = !empty($_POST['tiempo']) ? $_POST['tiempo'] : null;
    $id_almacen_destino_traslado = !empty($_POST['almacen_destino_traslado']) ? $_POST['almacen_destino_traslado'] : null;
    $descripcion = !empty($_POST['motivo_traslado']) ? $_POST['motivo_traslado'] : null;

    // Registrar nueva solicitud
    $insert_solicitud_query = "INSERT INTO solicitudes (id_tipo_solicitud, fecha_solicitud, id_status, id_perfil) VALUES (3, NOW(), 1, 2)";
    if ($conexion->query($insert_solicitud_query)) {
        $solicitud = $conexion->insert_id; // Capturar ID de la solicitud creada
    } else {
        $_SESSION['error'] = "Error al registrar la solicitud.";
        header("Location: formulario_inventario_producto_traslado.php");
        exit();
    }

    // Verificar cantidad disponible en el almacén
    $query = "SELECT cantidad FROM inventario_producto WHERE id_producto = ? AND id_almacen = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $id_producto, $id_almacen);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();

    if ($row) {
        $cantidad_disponible = $row['cantidad'];

        if ($cantidad_disponible >= $cantidad) {
            // Restar la cantidad del almacén original
            $nueva_cantidad = $cantidad_disponible - $cantidad;
            $update_query = "UPDATE inventario_producto SET cantidad = ? WHERE id_producto = ? AND id_almacen = ?";
            $update_stmt = $conexion->prepare($update_query);
            $update_stmt->bind_param("iss", $nueva_cantidad, $id_producto, $id_almacen);
            $update_stmt->execute();

            // Registrar el movimiento
            $id_tipo_movimiento = 3; // Asumiendo que este es el ID para 'traslado'
            $insert_movimiento_query = "INSERT INTO movimiento_producto (id_producto, id_almacen_origen, id_almacen_destino, cantidad, descripcion, id_tipo_movimiento, id_solicitud) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_movimiento_stmt = $conexion->prepare($insert_movimiento_query);
            $insert_movimiento_stmt->bind_param("ssissii", $id_producto, $id_almacen, $id_almacen_destino_traslado, $cantidad, $descripcion, $id_tipo_movimiento, $solicitud);
            $insert_movimiento_stmt->execute();

            $_SESSION['success'] = "Traslado realizado correctamente.";
            // Redireccionar a inventario_producto en caso de éxito
            header("Location: inventario_producto.php");
            exit();
        } else {
            $_SESSION['error'] = "No hay suficiente cantidad en el almacén.";
        }
    } else {
        $_SESSION['error'] = "La producto no existe en el almacén especificado.";
    }

    // Redireccionar al formulario en caso de error
    header("Location: formulario_inventario_producto_traslado.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>
