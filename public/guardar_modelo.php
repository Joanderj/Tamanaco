<?php
// Iniciar sesión para enviar mensajes entre páginas
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Variables para los mensajes
$mensaje_error = "";

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre_modelo = $_POST['nombre_modelo'];
    $vincular_opcion = $_POST['vincular_opcion'];
    $vincular_marca = $vincular_opcion === 'una' ? $_POST['marca_una'] : $_POST['marcas'];

    // Validar que el nombre del modelo no esté vacío
    if (empty($nombre_modelo)) {
        $mensaje_error = "El nombre del modelo es obligatorio.";
    } elseif (empty($vincular_marca)) {
        $mensaje_error = "Por favor, seleccione al menos una marca para vincular el modelo.";
    } else {
        // Verificar si el modelo ya existe
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM modelo WHERE nombre_modelo = ?");
        $stmt_validar->bind_param("s", $nombre_modelo);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "El modelo ya existe. Por favor, ingrese un nombre único.";
        } else {
            // Insertar el modelo
            $stmt = $conexion->prepare("INSERT INTO modelo (nombre_modelo, id_status, fecha_creacion) VALUES (?, 1, NOW())");
            $stmt->bind_param("s", $nombre_modelo);

            if ($stmt->execute()) {
                $id_modelo = $stmt->insert_id;

                // Vincular el modelo con la(s) marca(s) seleccionada(s)
                if ($vincular_opcion === 'una') {
                    $stmt_vincular = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
                    $stmt_vincular->bind_param("ii", $vincular_marca, $id_modelo);
                    $stmt_vincular->execute();
                } else {
                    foreach ($vincular_marca as $marca_id) {
                        $stmt_vincular = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
                        $stmt_vincular->bind_param("ii", $marca_id, $id_modelo);
                        $stmt_vincular->execute();
                    }
                }

                // Guardar mensaje de éxito en la sesión
                $_SESSION['mensaje_exito'] = "El modelo se guardó correctamente y se vinculó a la(s) marca(s).";

                // Redirigir a modelo.php 
                header("Location: modelo.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar el modelo: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_modelo.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>