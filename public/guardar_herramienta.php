<?php
// Iniciar sesión para manejar mensajes entre páginas
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
    // Capturar y validar los datos enviados
    $nombre_herramienta = trim($_POST['nombre_herramienta']);
    $descripcion = trim($_POST['descripcion']);
    $id_marca = intval($_POST['id_marca']);
    $id_modelo = intval($_POST['id_modelo']);
    $id_tipo = intval($_POST['id_tipo']);
    $id_status = 1; // Estado automático como Activo (1)
    $date_created = date('Y-m-d H:i:s');

    // Validar campos requeridos
    if (empty($nombre_herramienta) || !$id_marca || !$id_modelo || !$id_tipo) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios.";
    } else {
        // Subida de imagen (opcional)
        $nombre_imagen = null;
        $url_imagen = null;
        if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] == UPLOAD_ERR_OK) {
            $carpeta_destino = 'servidor_img/herramientas/';
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            $nombre_imagen_original = $_FILES['nombre_imagen']['name'];
            $extension = pathinfo($nombre_imagen_original, PATHINFO_EXTENSION);
            $nombre_imagen = uniqid() . '_' . pathinfo($nombre_imagen_original, PATHINFO_FILENAME) . '.' . $extension;
            $ruta_imagen = $carpeta_destino . $nombre_imagen;

            if (!move_uploaded_file($_FILES['nombre_imagen']['tmp_name'], $ruta_imagen)) {
                $mensaje_error = "Error al subir la imagen.";
            } else {
                $url_imagen = "../public/" . $ruta_imagen;
            }
        }

        // Si no hubo errores en la imagen
        if (empty($mensaje_error)) {
            // Insertar herramienta en la tabla `herramientas`
            $sql_herramienta = "INSERT INTO herramientas (nombre_herramienta, descripcion, url, nombre_imagen, id_status, id_marca, id_modelo, id_tipo, date_created) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_herramienta = $conexion->prepare($sql_herramienta);
            $stmt_herramienta->bind_param('sssisiiss', $nombre_herramienta, $descripcion, $url_imagen, $nombre_imagen, $id_status, $id_marca, $id_modelo, $id_tipo, $date_created);

            if ($stmt_herramienta->execute()) {
                $_SESSION['mensaje_exito'] = "La herramienta se guardó correctamente.";
                header("Location: herramienta.php");
                exit();
            } else {
                $mensaje_error = "Error al registrar la herramienta: " . $stmt_herramienta->error;
            }
            $stmt_herramienta->close();
        }
    }
}

// Si hubo errores, guardar mensaje de error en la sesión y redirigir al formulario
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_herramienta.php");
    exit();
}

// Cerrar conexión
$conexion->close();
?>