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
    $historia = !empty($_POST['historia']) ? $_POST['historia'] : null; // historia  de la empresa
    $vision = !empty($_POST['vision']) ? $_POST['vision'] : null; // vision de la empresa
    $mision = !empty($_POST['mision']) ? $_POST['mision'] : null; // mision de la empresa
    $especificos = !empty($_POST['especificos']) ? $_POST['especificos'] : null; // objetivo especifico de la empresa
    $general = !empty($_POST['general']) ? $_POST['general'] : null; // objetivo general de la empresa

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
            $stmt = $conexion->prepare("UPDATE empresa SET historia=?, mision=?, vision=?,objetivo_general=?,objetivos_especificos=? WHERE id_empresa=?");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
            }
            $stmt->bind_param("ssssss", $historia, $mision, $vision, $general,$especificos, $id_empresa);
            $action = "actualizada";
        } else {
            // Si no existe, insertar una nueva empresa con id_empresa = 1
            $stmt = $conexion->prepare("INSERT INTO empresa (id_empresa, historia, mision, vision, objetivo_general,objetivos_especificos, status_id_status) VALUES (?,?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta de inserción: " . $conexion->error);
            }
            $stmt->bind_param("isssssi", $id_empresa, $historia, $mision, $vision, $general,$especificos, $id_status);
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
        header("Location: sobre_nosotros.php");
        exit(); // Asegurar que el script se detenga después de la redirección
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        $_SESSION['error'] = "Error: {$e->getMessage()}";
        header("Location: sobre_nosotros.php");
        exit();
    } finally {
        // Cerrar los statements y la conexión
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}
?>

