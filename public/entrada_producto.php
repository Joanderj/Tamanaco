<?php
session_start(); // Iniciar la sesión para usar variables de sesión

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la base de datos
    $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
    if ($conexion->connect_error) {
        die("Error en la conexión: " . $conexion->connect_error);
    }

    // Capturar datos del formulario y manejar valores nulos
    $producto = !empty($_POST['producto']) ? $_POST['producto'] : null; // Datos del Repuesto
    $cantidad = !empty($_POST['cantidad']) ? $_POST['cantidad'] : null; // Cantidad del Repuesto
    $almacen = !empty($_POST['almacen']) ? $_POST['almacen'] : null; // Datos del Almacen

    // Validación de datos
    $conexion->begin_transaction(); // Iniciar una transacción para asegurar atomicidad

    try {
        // Verificar si el id_repuesto = 1 existe
        $stmt_check = $conexion->prepare("SELECT cantidad FROM inventario_producto WHERE id_producto = ? and id_almacen = ?");
        if (!$stmt_check) {
            throw new Exception("Error al preparar la consulta de verificación: " . $conexion->error);
        }
        $stmt_check->bind_param("ii", $producto, $almacen);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $stmt_check->bind_result($cantidadExistente);
            $stmt_check->fetch();
            $nuevaCantidad = $cantidadExistente + $cantidad;
            // Si existe, actualizar el repuesto con id_repuesto = 1
            $stmt = $conexion->prepare("UPDATE inventario_producto SET cantidad=?,cantidad_minima = 2 WHERE id_producto=? and id_almacen=?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
            }
            $stmt->bind_param("iii", $nuevaCantidad, $producto, $almacen);
            $action = "actualizada";
        } else {
            // Si no existe, insertar una nueva empresa con id_empresa = 1
            $stmt = $conexion->prepare("INSERT INTO inventario_producto (cantidad, id_producto, id_almacen,cantidad_minima) VALUES (?, ?, ?,2)");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
            }
            $stmt->bind_param("iii", $cantidad, $producto, $almacen);
            $action = "registrada";
        }

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        // Confirmar la transacción
        $conexion->commit();

        // Guardar el mensaje en una variable de sesión
        $_SESSION['mensaje'] = "producto $action correctamente con ID: $id_producto.";

        // Redirigir al usuario a la URL deseada
        header("Location: inventario_producto.php");
        exit(); // Asegurar que el script se detenga después de la redirección
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        $_SESSION['error'] = "Error: {$e->getMessage()}";
        header("Location: formulario_inventario_producto.php");
        exit();
    } finally {
        // Cerrar los statements y la conexión
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}
?>

