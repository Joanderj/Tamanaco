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
    $nombre_marca = $_POST['nombre_marca'];
    $vincular_opcion = isset($_POST['vincular_opcion']) ? $_POST['vincular_opcion'] : null;

    // Validar que el nombre de la marca no esté vacío
    if (empty($nombre_marca)) {
        $mensaje_error = "El nombre de la marca es obligatorio.";
    } elseif ($vincular_opcion === null) {
        $mensaje_error = "Por favor, seleccione una opción para vincular los modelos.";
    } else {
        // Verificar si la marca ya existe
        $stmt_validar = $conexion->prepare("SELECT COUNT(*) AS total FROM marca WHERE nombre_marca = ?");
        $stmt_validar->bind_param("s", $nombre_marca);
        $stmt_validar->execute();
        $resultado_validar = $stmt_validar->get_result();
        $fila_validar = $resultado_validar->fetch_assoc();

        if ($fila_validar['total'] > 0) {
            $mensaje_error = "La marca ya existe. Por favor, ingrese un nombre único.";
        } else {
            // Insertar la marca
            $stmt = $conexion->prepare("INSERT INTO marca (nombre_marca, id_status, fecha_creacion) VALUES (?, 1, NOW())");
            $stmt->bind_param("s", $nombre_marca);

            if ($stmt->execute()) {
                $id_marca = $stmt->insert_id;
 
                // Vincular modelos según la opción seleccionada
                if ($vincular_opcion === "uno") {
                    $modelo_uno = $_POST['modelo_uno'];
                    $stmt_vincular = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
                    $stmt_vincular->bind_param("ii", $id_marca, $modelo_uno);
                    $stmt_vincular->execute();
                } elseif ($vincular_opcion === "varios") {
                    $modelos = isset($_POST['modelos']) ? $_POST['modelos'] : [];
                    if (!empty($modelos)) {
                        foreach ($modelos as $id_modelo) {
                            $stmt_vincular = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo) VALUES (?, ?)");
                            $stmt_vincular->bind_param("ii", $id_marca, $id_modelo);
                            $stmt_vincular->execute();
                        }
                    }
                }

                // Guardar mensaje de éxito en la sesión
                $_SESSION['mensaje_exito'] = "La marca y los modelos se guardaron correctamente.";

                // Redirigir a marca.php
                header("Location: marca.php");
                exit();
            } else {
                $mensaje_error = "Error al guardar la marca: " . $stmt->error;
            }
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_marca.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>