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

    // Capturar datos del formulario y manejar valores nulos
    $maquina = $_POST['maquina'] ?? null;
    $codigo = $_POST['codigo_maquina'] ?? null;
    $sede = $_POST['sede'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $cantidad = 1;
    $tipo_movimiento = 4;
    $status = 2;

    // Verificar si los datos son válidos
    if ($maquina && $codigo && $sede) {
        // Verificar si la máquina ya existe en el inventario
        $query = "SELECT cantidad FROM inventario_maquina WHERE id_maquina = ? AND sede_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $maquina, $sede);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // La máquina existe, actualizar la cantidad
            $row = $resultado->fetch_assoc();
            $nueva_cantidad = $row['cantidad'];

            if ($nueva_cantidad < 0) {
                $_SESSION['error'] = "La cantidad a retirar supera la cantidad disponible.";
                header("Location: formulario_inventario_maquina.php");
                exit();
            }

            // Actualizar la cantidad en el inventario
            $update_query = "UPDATE inventario_maquina SET cantidad = ? WHERE id_maquina = ? AND sede_id = ?";
            $update_stmt = $conexion->prepare($update_query);
            $update_stmt->bind_param("iii", $nueva_cantidad, $maquina, $sede);

            if ($update_stmt->execute()) {
                // Actualizar status en la tabla maquina_unica
                $update_status_query = "UPDATE maquina_unica SET id_status = ? WHERE id_maquina_unica = ?";
                $update_status_stmt = $conexion->prepare($update_status_query);
                $update_status_stmt->bind_param("is", $status, $codigo);
                $update_status_stmt->execute();
                $update_status_stmt->close();

                // Registrar movimiento en movimiento_maquina
                $movimiento_query = "INSERT INTO movimiento_maquina (id_maquina, id_almacen_origen, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?)";
                $movimiento_stmt = $conexion->prepare($movimiento_query);
                $movimiento_stmt->bind_param("iisi", $maquina, $sede, $descripcion, $tipo_movimiento);

                if ($movimiento_stmt->execute()) {
                    $_SESSION['success'] = "Movimiento registrado, cantidad actualizada y estado modificado exitosamente.";
                    header("Location: inventario_maquina.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Error al registrar el movimiento.";
                }
            } else {
                $_SESSION['error'] = "Error al actualizar la cantidad de la máquina.";
            }
        } else {
            $_SESSION['error'] = "Máquina no encontrada en el inventario.";
        }

        // Cerrar statements
        $stmt->close();
        $update_stmt->close();
        if (isset($movimiento_stmt)) $movimiento_stmt->close();

    } else {
        $_SESSION['error'] = "Datos inválidos. Por favor, verifica la información.";
    }

    $conexion->close();
    header("Location: formulario_inventario_maquina.php");
    exit();
}
?>
