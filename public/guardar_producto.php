<?php
session_start();

$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$mensaje_error = "";
$mensaje_exito = "";

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = trim($_POST['nombre_producto']);
    $id_marca = intval($_POST['marca']);
    $id_modelo = intval($_POST['modelo']);
    $id_tipo = intval($_POST['tipo']);
    $id_clasificacion = intval($_POST['clasificacion']);
    $unidad_medida = trim($_POST['unidad_medida']);
    $nombre_imagen_original = $_FILES['nombre_imagen']['name'] ?? null;
    $proveedores = $_POST['proveedores'] ?? [];
    $id_status = 1;
    $date_created = date('Y-m-d H:i:s');
 
    if (empty($nombre_producto) || !$id_marca || !$id_modelo || !$id_tipo || !$id_clasificacion || empty($unidad_medida)) {
        $mensaje_error = "Por favor, complete todos los campos obligatorios.";
    } else {
        // Insertar el producto
        $sql_insert = "INSERT INTO producto (nombre_producto, id_marca, id_modelo, id_tipo, id_clasificacion, unidad_medida, id_status, date_created)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->bind_param(
            'siiiisis',
            $nombre_producto,
            $id_marca,
            $id_modelo,
            $id_tipo,
            $id_clasificacion,
            $unidad_medida,
            $id_status,
            $date_created
        );

        if ($stmt_insert->execute()) {
            $id_producto = $stmt_insert->insert_id;
        } else {
            $mensaje_error = "Error al guardar el producto: " . $stmt_insert->error;
        }
        $stmt_insert->close();

        // Subida de imagen
        if (empty($mensaje_error) && !empty($_FILES['nombre_imagen']['tmp_name'])) {
            $carpeta_destino = 'servidor_img/producto/';
            if (!is_dir($carpeta_destino)) {
                mkdir($carpeta_destino, 0777, true);
            }

            $extension = pathinfo($nombre_imagen_original, PATHINFO_EXTENSION);
            $nombre_imagen_final = uniqid() . '_' . pathinfo($nombre_imagen_original, PATHINFO_FILENAME) . '.' . $extension;
            $ruta_imagen = $carpeta_destino . $nombre_imagen_final;

            if (!move_uploaded_file($_FILES['nombre_imagen']['tmp_name'], $ruta_imagen)) {
                $mensaje_error = "Error al subir la imagen.";
            } else {
                $sql_update = "UPDATE producto SET nombre_imagen = ?, url = ? WHERE id_producto = ?";
                $stmt_update = $conexion->prepare($sql_update);
                $stmt_update->bind_param('ssi', $nombre_imagen_final, $ruta_imagen, $id_producto);

                if (!$stmt_update->execute()) {
                    $mensaje_error = "Error al actualizar el producto con la imagen: " . $stmt_update->error;
                }

                $stmt_update->close();
            }
        }

        // Vincular proveedores
        if (empty($mensaje_error)) {
            if (!empty($proveedores)) {
                $stmt_proveedor = $conexion->prepare("INSERT INTO proveedor_producto (id_producto, id_proveedor) VALUES (?, ?)");

                foreach ($proveedores as $id_proveedor) {
                    $stmt_proveedor->bind_param("ii", $id_producto, $id_proveedor);

                    if (!$stmt_proveedor->execute()) {
                        $mensaje_error .= " Error al vincular proveedor con ID $id_proveedor: " . $stmt_proveedor->error;
                    }
                }

                $stmt_proveedor->close();
            }
        }

        if (empty($mensaje_error)) {
            $mensaje_exito = "El producto fue guardado correctamente.";
        }
    }
} else {
    $mensaje_error = "Error: No se recibió ningún formulario.";
}

// Guardar mensajes y redirigir
if (!empty($mensaje_error)) {
    $_SESSION['mensaje_error'] = $mensaje_error;
    header("Location: formulario_guardar_producto.php");
    exit();
} elseif (!empty($mensaje_exito)) {
    $_SESSION['mensaje_exito'] = $mensaje_exito;
    header("Location: producto.php");
    exit();
}

$conexion->close();
?>
