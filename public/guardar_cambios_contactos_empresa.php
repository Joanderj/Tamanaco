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
    $tlf1 = !empty($_POST['tlf1']) ? $_POST['tlf1'] : null; // telefono de la empresa
    $tlf2 = !empty($_POST['tlf2']) ? $_POST['tlf2'] : null; // otro telefono de la empresa
    $cr1 = !empty($_POST['cr1']) ? $_POST['cr1'] : null; // correo electronico  de la empresa
    $cr2 = !empty($_POST['cr2']) ? $_POST['cr2'] : null; // otro correo electronico de la empresa

    // Datos fijos o predeterminados
    $id_empresa = 1; // ID de la empresa siempre será 1
    $id_status = 1; // Estado activo por defecto

    // Validación de datos
    $conexion->begin_transaction(); // Iniciar una transacción para asegurar atomicidad

    try {
        // Verificar si el id_empresa = 1 existe
        $stmt_check = $conexion->prepare("SELECT id_empresa FROM empresa WHERE id_empresa = ?");
        if (!$stmt_check) {
            throw new Exception("Error al preparar la consulta de verificación: " . $conexion->error);
        }
        $stmt_check->bind_param("i", $id_empresa);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Si existe, actualizar la empresa con id_empresa = 1
            $stmt = $conexion->prepare("UPDATE empresa SET telefono_1=?, telefono_2=?, correo_1=?,correo_2=? WHERE id_empresa=?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
            }
            $stmt->bind_param("isssi", $tlf1, $tlf2, $cr1, $cr2, $id_empresa);
            $action = "actualizada";
        } else {
            // Si no existe, insertar una nueva empresa con id_empresa = 1
            $stmt = $conexion->prepare("INSERT INTO empresa (id_empresa, telefono_1, telefono_2, correo_1, correo_2, status_id_status) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
            }
            $stmt->bind_param("issssi", $id_empresa, $tlf1, $tlf2, $cr1, $cr2, $id_status);
            $action = "registrada";
        }

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        // Confirmar la transacción
        $conexion->commit();

        // Guardar el mensaje en una variable de sesión
        $_SESSION['mensaje'] = "Empresa $action correctamente con ID: $id_empresa.";

        // Redirigir al usuario a la URL deseada
        header("Location: contactos_empresa.php");
        exit(); // Asegurar que el script se detenga después de la redirección
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        $_SESSION['error'] = "Error: {$e->getMessage()}";
        header("Location: contactos_empresa.php");
        exit();
    } finally {
        // Cerrar los statements y la conexión
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}
?>

