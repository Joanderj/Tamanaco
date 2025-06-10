<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        $_SESSION['error'] = "Error en la conexión: " . $conexion->connect_error;
        header("Location: formulario_inventario_herramienta.php");
        exit();
    }

    // Capturar datos del formulario y manejar valores nulos
    $herramienta = !empty($_POST['herramienta']) ? $_POST['herramienta'] : null;
    $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : null;
    $almacen = !empty($_POST['almacen']) ? $_POST['almacen'] : null;
    $descripcion = "Entrada de herramienta"; // Descripción más acorde
    $tipo_movimiento = 1; // Asignación directa

    // Verificar si los datos son válidos
    if ($herramienta && $cantidad && $almacen) {
        // Verificar si el herramienta ya existe en el inventario
        $query = "SELECT cantidad FROM inventario_herramientas WHERE herramienta_id = ? AND id_almacen = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $herramienta, $almacen);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            // El herramienta existe, actualizar la cantidad
            $row = $resultado->fetch_assoc();
            $nueva_cantidad = $row['cantidad'] + $cantidad;

            $update_query = "UPDATE inventario_herramientas SET cantidad = ? WHERE herramienta_id = ? AND id_almacen = ?";
            $update_stmt = $conexion->prepare($update_query);
            $update_stmt->bind_param("iii", $nueva_cantidad, $herramienta, $almacen);
            if ($update_stmt->execute()) {
                // Registrar movimiento en movimiento_herramienta
                $movimiento_query = "INSERT INTO movimiento_herramientas (herramienta_id, id_almacen_destino, cantidad, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?)";
                $movimiento_stmt = $conexion->prepare($movimiento_query);
                $movimiento_stmt->bind_param("iiisi", $herramienta, $almacen, $cantidad, $descripcion, $tipo_movimiento);
                $movimiento_stmt->execute();

                // Cerrar declaraciones
                $movimiento_stmt->close();
                $update_stmt->close();

                // Almacenar mensaje de éxito
                $_SESSION['success'] = "herramienta actualizado correctamente.";
                // Redirigir a inventario_herramienta.php si la actualización fue exitosa
                header("Location: inventario_herramienta.php");
                exit();
            } else {
                $_SESSION['error'] = "Error al actualizar el herramienta.";
                header("Location: formulario_inventario_herramienta.php");
                exit();
            }
        } else {
            // El herramienta no existe, insertar un nuevo registro
            $insert_query = "INSERT INTO inventario_herramientas (herramienta_id, id_almacen, cantidad) VALUES (?, ?, ?)";
            $insert_stmt = $conexion->prepare($insert_query);
            $insert_stmt->bind_param("iii", $herramienta, $almacen, $cantidad);
            if ($insert_stmt->execute()) {
                // Registrar movimiento en movimiento_herramienta
                $movimiento_query = "INSERT INTO movimiento_herramientas (herramienta_id, id_almacen_destino, cantidad, descripcion, id_tipo_movimiento) VALUES (?, ?, ?, ?, ?)";
                $movimiento_stmt = $conexion->prepare($movimiento_query);
                $movimiento_stmt->bind_param("iiisi", $herramienta, $almacen, $cantidad, $descripcion, $tipo_movimiento);
                $movimiento_stmt->execute();

                // Cerrar declaraciones
                $movimiento_stmt->close();
                $insert_stmt->close();

                // Almacenar mensaje de éxito
                $_SESSION['success'] = "herramienta insertado correctamente.";
                // Redirigir a inventario_herramienta.php si la inserción fue exitosa
                header("Location: inventario_herramienta.php");
                exit();
            } else {
                $_SESSION['error'] = "Error al insertar el herramienta.";
                header("Location: formulario_inventario_herramienta.php");
                exit();
            }
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        $_SESSION['error'] = "Datos inválidos. Por favor, verifica la información.";
        header("Location: formulario_inventario_herramienta.php");
        exit();
    }

    // Cerrar la conexión
    $conexion->close();
}
?>
