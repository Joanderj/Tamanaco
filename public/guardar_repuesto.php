<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje_error = "";

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y validar datos
    $nombre_repuesto = trim($_POST['nombre_repuesto']);
    $id_marca = intval($_POST['marca']);
    $id_modelo = intval($_POST['modelo']);
    $id_tipo = intval($_POST['tipo']);
    $sugerencia_mantenimiento = trim($_POST['sugerencia_mantenimiento']);
    $id_status = 1;
    $date_created = date('Y-m-d H:i:s');

    // Validar campos obligatorios
    if (empty($nombre_repuesto) || !$id_marca || !$id_modelo || !$id_tipo) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios.";
    }

    // Subida de imagen
    $nombre_imagen = null;
    $url_imagen = null;

    if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
        $carpeta_destino = 'servidor_img/repuesto/';
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

    // Si no hubo errores
    if (empty($mensaje_error)) {
        // Insertar repuesto
        $sql_repuesto = "INSERT INTO repuesto (nombre_repuesto, id_marca, id_modelo, id_tipo, nombre_imagen, url, id_status, date_created, sugerencia_mantenimiento) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_repuesto = $conexion->prepare($sql_repuesto);
        $stmt_repuesto->bind_param('siiississ', $nombre_repuesto, $id_marca, $id_modelo, $id_tipo, $nombre_imagen, $url_imagen, $id_status, $date_created, $sugerencia_mantenimiento);

        if ($stmt_repuesto->execute()) {
            $id_repuesto = $stmt_repuesto->insert_id;

            // Especificaciones dinámicas
            if (!empty($_POST['detalle_especificacion']) && !empty($_POST['valor_especificacion'])) {
                $detalle_especificaciones = $_POST['detalle_especificacion'];
                $valor_especificaciones = $_POST['valor_especificacion'];

                $sql_especificacion = "INSERT INTO especificaciones_repuestos (id_repuesto, detalle_especificacion, valor_especificacion) VALUES (?, ?, ?)";
                $stmt_especificacion = $conexion->prepare($sql_especificacion);

                foreach ($detalle_especificaciones as $index => $detalle) {
                    $valor = $valor_especificaciones[$index];
                    $stmt_especificacion->bind_param('iss', $id_repuesto, $detalle, $valor);
                    if (!$stmt_especificacion->execute()) {
                        $mensaje_error = "Error al guardar la especificación: " . $stmt_especificacion->error;
                        break;
                    }
                }
                $stmt_especificacion->close();
            }

            // Proveedores: permitir uno o varios
            $proveedores = [];

            if (isset($_POST['proveedores']) && is_array($_POST['proveedores']) && count($_POST['proveedores']) > 0) {
                foreach ($_POST['proveedores'] as $p) {
                    $proveedores[] = intval($p);
                }
            } elseif (!empty($_POST['proveedorUnico'])) {
                $proveedores[] = intval($_POST['proveedorUnico']);
            }

            if (empty($mensaje_error) && !empty($proveedores)) {
                $stmt_proveedor = $conexion->prepare("INSERT INTO proveedor_repuesto (id_repuesto, id_proveedor) VALUES (?, ?)");

                foreach ($proveedores as $id_proveedor) {
                    $stmt_proveedor->bind_param("ii", $id_repuesto, $id_proveedor);
                    if (!$stmt_proveedor->execute()) {
                        $mensaje_error .= " Error al vincular proveedor con ID $id_proveedor: " . $stmt_proveedor->error;
                    }
                }

                $stmt_proveedor->close();
            }

            // Redirigir si todo salió bien
            if (empty($mensaje_error)) {
                $_SESSION['mensaje_exito'] = "El repuesto y sus detalles se guardaron correctamente.";
                header("Location: repuesto.php");
                exit();
            }
        } else {
            $mensaje_error = "Error al registrar el repuesto: " . $stmt_repuesto->error;
        }

        $stmt_repuesto->close();
    }
} else {
    $mensaje_error = "Error: No se recibió ningún formulario.";
}

// Si hubo errores
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_repuesto.php");
    exit();
}

$conexion->close();
?>
