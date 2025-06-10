<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        $_SESSION['error'] = "Error en la conexión: " . $conexion->connect_error;
        header("Location: formulario_inventario_herramienta_traslado.php");
        exit();
    }

    // Capturar datos del formulario y manejar valores nulos
    $id_herramienta = !empty($_POST['herramienta']) ? $_POST['herramienta'] : null; // Asumimos que 'herramienta' es el ID
    $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : null;
    $id_almacen = !empty($_POST['almacen']) ? $_POST['almacen'] : null;
    $id_almacen_destino_traslado = !empty($_POST['almacen_destino_traslado']) ? $_POST['almacen_destino_traslado'] : null;
    $descripcion = !empty($_POST['motivo_traslado']) ? $_POST['motivo_traslado'] : null;


    // Registrar nueva solicitud
        $insert_solicitud_query = "INSERT INTO solicitudes (id_tipo_solicitud, fecha_solicitud, id_status, id_perfil) VALUES (3, NOW(), 1, 2)";
        if ($conexion->query($insert_solicitud_query)) {
            $solicitud = $conexion->insert_id; // Capturar ID de la solicitud creada
        } else {
            $_SESSION['error'] = "Error al registrar la solicitud.";
            header("Location: formulario_inventario_maquina_traslado.php");
            exit();
        }
    // Verificar cantidad disponible en el almacén
    $query = "SELECT cantidad FROM inventario_herramientas WHERE herramienta_id = ? AND id_almacen = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $id_herramienta, $id_almacen);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();

    if ($row) {
        $cantidad_disponible = $row['cantidad'];

        if ($cantidad_disponible >= $cantidad) {
            // Restar la cantidad del almacén original
            $nueva_cantidad = $cantidad_disponible - $cantidad;
            $update_query = "UPDATE inventario_herramientas SET cantidad = ? WHERE herramienta_id = ? AND id_almacen = ?";
            $update_stmt = $conexion->prepare($update_query);
            $update_stmt->bind_param("iss", $nueva_cantidad, $id_herramienta, $id_almacen);
            $update_stmt->execute();

            // Sumar la cantidad al almacén de destino
            $query_destino = "SELECT cantidad FROM inventario_herramientas WHERE herramienta_id = ? AND id_almacen = ?";
            $stmt_destino = $conexion->prepare($query_destino);
            $stmt_destino->bind_param("ss", $id_herramienta, $id_almacen_destino_traslado);
            $stmt_destino->execute();
            $resultado_destino = $stmt_destino->get_result();
            $row_destino = $resultado_destino->fetch_assoc();

            if ($row_destino) {
                $cantidad_destino = $row_destino['cantidad'];
                $nueva_cantidad_destino = $cantidad_destino + $cantidad;

                // Actualizar la cantidad en el almacén de destino
                $update_query_destino = "UPDATE inventario_herramientas SET cantidad = ? WHERE herramienta_id = ? AND id_almacen = ?";
                $update_stmt_destino = $conexion->prepare($update_query_destino);
                $update_stmt_destino->bind_param("iss", $nueva_cantidad_destino, $id_herramienta, $id_almacen_destino_traslado);
                $update_stmt_destino->execute();
            } else {
                // Si no existe el herramienta en el almacén de destino, insertarlo
                $insert_query = "INSERT INTO inventario_herramientas (herramienta_id, cantidad, id_almacen) VALUES (?, ?, ?)";
                $insert_stmt = $conexion->prepare($insert_query);
                $insert_stmt->bind_param("sis", $id_herramienta, $cantidad, $id_almacen_destino_traslado);
                $insert_stmt->execute();
            }

            // Registrar el movimiento
            $id_tipo_movimiento = 3; // Asumiendo que este es el ID para 'traslado'
            $insert_movimiento_query = "INSERT INTO movimiento_herramientas (herramienta_id, id_almacen_origen, id_almacen_destino, cantidad, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_movimiento_stmt = $conexion->prepare($insert_movimiento_query);
            $insert_movimiento_stmt->bind_param("ssissi", $id_herramienta, $id_almacen, $id_almacen_destino_traslado, $cantidad, $descripcion, $id_tipo_movimiento);
            $insert_movimiento_stmt->execute();

            $_SESSION['success'] = "Traslado realizado correctamente.";
            // Redireccionar a inventario_herramienta en caso de éxito
            header("Location: inventario_herramienta.php");
            exit();
        } else {
            $_SESSION['error'] = "No hay suficiente cantidad en el almacén.";
        }
    } else {
        $_SESSION['error'] = "El herramienta no existe en el almacén especificado.";
    }

    // Redireccionar al formulario en caso de error
    header("Location: formulario_inventario_herramienta_traslado.php");
    exit();
}
?>
