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
    $nombre_clasificacion = isset($_POST['nombre_clasificacion']) ? trim($_POST['nombre_clasificacion']) : "";
    $abreviatura_clasificacion = isset($_POST['abreviatura_clasificacion']) ? trim($_POST['abreviatura_clasificacion']) : "";
    $vincular_opcion = isset($_POST['vincular_opcion']) ? $_POST['vincular_opcion'] : null;

    // Validar que los campos requeridos no estén vacíos
    if (empty($nombre_clasificacion) || empty($abreviatura_clasificacion)) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios.";
    } elseif ($vincular_opcion === null) {
        $mensaje_error = "Por favor, seleccione una opción para vincular los tipos.";
    } else {
        // Verificar si la clasificación ya existe
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM clasificacion WHERE nombre_clasificacion = ? OR abreviacion_clasificacion = ?");
        $stmt_validar->bind_param("ss", $nombre_clasificacion, $abreviatura_clasificacion);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "La clasificación o abreviatura ya existe. Por favor, ingrese valores únicos.";
        } else {
            // Insertar la clasificación con un id_status válido
            $id_status = 1; // Assuming 1 is a valid status ID
            $stmt = $conexion->prepare("INSERT INTO clasificacion (nombre_clasificacion, abreviacion_clasificacion, id_status) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nombre_clasificacion, $abreviatura_clasificacion, $id_status);

            if ($stmt->execute()) {
                $id_clasificacion = $stmt->insert_id;

                // Vincular tipos según la opción seleccionada
                if ($vincular_opcion === "uno") {
                    $tipo_uno = $_POST['tipo_uno'];
                    $stmt_vincular = $conexion->prepare("INSERT INTO tipo_clasificacion (id_clasificacion, id_tipo) VALUES (?, ?)");
                    $stmt_vincular->bind_param("ii", $id_clasificacion, $tipo_uno);
                    $stmt_vincular->execute();
                } elseif ($vincular_opcion === "varios") {
                    $tipos = isset($_POST['tipos']) ? $_POST['tipos'] : [];
                    if (!empty($tipos)) {
                        foreach ($tipos as $id_tipo) {
                            $stmt_vincular = $conexion->prepare("INSERT INTO tipo_clasificacion (id_clasificacion, id_tipo) VALUES (?, ?)");
                            $stmt_vincular->bind_param("ii", $id_clasificacion, $id_tipo);
                            $stmt_vincular->execute();
                        }
                    }
                }

                // Guardar mensaje de éxito en la sesión
                $_SESSION['mensaje_exito'] = "La clasificación y los tipos se guardaron correctamente.";

                // Redirigir a clasificacion.php
                header("Location: clasificacion.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar la clasificación: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_clasificacion.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>