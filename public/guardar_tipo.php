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
    $nombre_tipo = $_POST['nombre_tipo'];
    $id_clasificaciones = isset($_POST['id_clasificacion']) ? $_POST['id_clasificacion'] : null;

    // Validar que el nombre del tipo no esté vacío
    if (empty($nombre_tipo)) {
        $mensaje_error = "El nombre del tipo es obligatorio.";
    } elseif ($id_clasificaciones === null || empty($id_clasificaciones)) {
        $mensaje_error = "Por favor, seleccione al menos una clasificación para vincular al tipo.";
    } else {
        // Verificar si el tipo ya existe
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM tipo WHERE nombre_tipo = ?");
        $stmt_validar->bind_param("s", $nombre_tipo);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "El tipo ya existe. Por favor, ingrese un nombre único.";
        } else {
            // Insertar el tipo
            $stmt = $conexion->prepare("INSERT INTO tipo (nombre_tipo, id_status, fecha_creacion) VALUES (?, 1, NOW())");
            $stmt->bind_param("s", $nombre_tipo);

            if ($stmt->execute()) {
                $id_tipo = $stmt->insert_id;

                // Vincular el tipo con las clasificaciones seleccionadas
                $stmt_vincular = $conexion->prepare("INSERT INTO tipo_clasificacion (id_tipo, id_clasificacion) VALUES (?, ?)");
                foreach ($id_clasificaciones as $id_clasificacion) {
                    $stmt_vincular->bind_param("ii", $id_tipo, $id_clasificacion);
                    $stmt_vincular->execute();
                }

                // Guardar mensaje de éxito en la sesión
                $_SESSION['mensaje_exito'] = "El tipo y sus clasificaciones se guardaron correctamente.";

                // Redirigir a tipo.php
                header("Location: tipo.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar el tipo: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_tipo.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>