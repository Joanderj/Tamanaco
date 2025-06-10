<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        $_SESSION['error'] = "Error en la conexión: " . $conexion->connect_error;
        header("Location: formulario_inventario_maquina.php");
        exit();
    }

    // Capturar datos del formulario
    $maquina = $_POST['maquina'] ?? null;
    $codigo = $_POST['codigo_maquina'] ?? null;
    $sede = $_POST['sede'] ?? null;
    $sede_entrada = $_POST['sede_entrada'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $cantidad = 1;
    $tipo_movimiento = 2;
    $status = 23;

    if ($maquina && $codigo && $sede && $sede_entrada) {
        // Registrar nueva solicitud
        $insert_solicitud_query = "INSERT INTO solicitudes (id_tipo_solicitud, fecha_solicitud, id_status, id_perfil) VALUES (3, NOW(), 1, 2)";
        if ($conexion->query($insert_solicitud_query)) {
            $solicitud = $conexion->insert_id; // Capturar ID de la solicitud creada
        } else {
            $_SESSION['error'] = "Error al registrar la solicitud.";
            header("Location: formulario_inventario_maquina_traslado.php");
            exit();
        }

        // Verificar si la máquina existe en la sede de origen
        $query = "SELECT cantidad FROM inventario_maquina WHERE id_maquina = ? AND sede_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $maquina, $sede);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // La máquina existe en el origen, restar 1
            $row = $resultado->fetch_assoc();
            $nueva_cantidad = $row['cantidad'];

            if ($nueva_cantidad < 0) {
                $_SESSION['error'] = "La cantidad a retirar supera la cantidad disponible.";
                header("Location: formulario_inventario_maquina.php");
                exit();
            }

            // Actualizar cantidad en sede origen
            $update_query = "UPDATE inventario_maquina SET cantidad = ? WHERE id_maquina = ? AND sede_id = ?";
            $update_stmt = $conexion->prepare($update_query);
            $update_stmt->bind_param("iii", $nueva_cantidad, $maquina, $sede);
            $update_stmt->execute();
            $update_stmt->close();

            // Verificar si ya existe en sede destino
            $check_destino_query = "SELECT cantidad FROM inventario_maquina WHERE id_maquina = ? AND sede_id = ?";
            $check_destino_stmt = $conexion->prepare($check_destino_query);
            $check_destino_stmt->bind_param("ii", $maquina, $sede_entrada);
            $check_destino_stmt->execute();
            $destino_resultado = $check_destino_stmt->get_result();

            if ($destino_resultado->num_rows > 0) {
                // Ya existe, sumar 1
                $row_destino = $destino_resultado->fetch_assoc();
                $nueva_cantidad_destino = $row_destino['cantidad'];

                $update_destino_query = "UPDATE inventario_maquina SET cantidad = ? WHERE id_maquina = ? AND sede_id = ?";
                $update_destino_stmt = $conexion->prepare($update_destino_query);
                $update_destino_stmt->bind_param("iii", $nueva_cantidad_destino, $maquina, $sede_entrada);
                $update_destino_stmt->execute();
                $update_destino_stmt->close();
            } else {
                // No existe, insertar nuevo
                $insert_destino_query = "INSERT INTO inventario_maquina (id_maquina, sede_id, cantidad) VALUES (?, ?, ?)";
                $insert_destino_stmt = $conexion->prepare($insert_destino_query);
                $insert_destino_stmt->bind_param("iii", $maquina, $sede_entrada, $cantidad);
                $insert_destino_stmt->execute();
                $insert_destino_stmt->close();
            }

            $check_destino_stmt->close();

            // Actualizar estado y sede en maquina_unica
            $update_status_query = "UPDATE maquina_unica SET id_status = ?, id_sede = ? WHERE id_maquina_unica = ?";
            $update_status_stmt = $conexion->prepare($update_status_query);
            $update_status_stmt->bind_param("iis", $status, $sede_entrada, $codigo);
            $update_status_stmt->execute();
            $update_status_stmt->close();

            // Registrar movimiento
            $movimiento_query = "INSERT INTO movimiento_maquina (id_maquina, id_almacen_origen, id_almacen_destino, descripcion, id_tipo_movimiento, id_solicitud) VALUES (?, ?, ?, ?, ?, ?)";
            $movimiento_stmt = $conexion->prepare($movimiento_query);
            $movimiento_stmt->bind_param("iiisii", $codigo, $sede, $sede_entrada, $descripcion, $tipo_movimiento, $solicitud);
            if ($movimiento_stmt->execute()) {
                $_SESSION['success'] = "Movimiento y solicitud registrados correctamente.";
                $movimiento_stmt->close();
                $stmt->close();
                $conexion->close();
                header("Location: inventario_maquina.php");
                exit();
            } else {
                $_SESSION['error'] = "Error al registrar el movimiento.";
            }
        } else {
            $_SESSION['error'] = "Máquina no encontrada en el inventario de la sede origen.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Datos inválidos. Por favor, verifica la información.";
    }

    $conexion->close();
    header("Location: formulario_inventario_maquina_traslado.php");
    exit();
}
?>
