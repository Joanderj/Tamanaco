<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener ID del producto y validar
$id_producto = $_POST['id_producto'] ?? null;
if (!$id_producto || !is_numeric($id_producto)) {
    $_SESSION['mensaje_error'] = "ID de producto no válido.";
    header("Location: producto.php");
    exit();
}

// Obtener y sanitizar los datos
$nombre = trim($_POST['nombre_producto'] ?? '');
$id_marca = intval($_POST['id_marca'] ?? 0);
$id_modelo = intval($_POST['id_modelo'] ?? 0);
$id_tipo = intval($_POST['id_tipo'] ?? 0);
$id_clasificacion = intval($_POST['id_clasificacion'] ?? 0);
$unidad_medida = trim($_POST['unidad_medida'] ?? '');

// Validación básica
if (empty($nombre) || $id_marca === 0 || empty($unidad_medida)) {
    $_SESSION['mensaje_error'] = "Campos obligatorios faltantes.";
    header("Location: producto.php");
    exit();
}

// Variables para la imagen
$nombre_imagen = null;
$url_imagen = null;

if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = $_FILES['nombre_imagen']['name'];
    $temporal = $_FILES['nombre_imagen']['tmp_name'];
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $ext_permitidas)) {
        $_SESSION['mensaje_error'] = "Formato de imagen no permitido.";
        header("Location: producto.php");
        exit();
    }

    $nombre_unico = uniqid('producto_', true) . "." . $extension;
    $ruta_destino = "servidor_img/producto/" . $nombre_unico;

    if (move_uploaded_file($temporal, $ruta_destino)) {
        $nombre_imagen = $nombre_unico;
        $url_imagen = "servidor_img/producto/" . $nombre_unico;

        // Borrar imagen antigua
        $consulta_img = $conexion->prepare("SELECT url FROM producto WHERE id_producto = ?");
        $consulta_img->bind_param("i", $id_producto);
        $consulta_img->execute();
        $resultado = $consulta_img->get_result();

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $imagen_antigua = $fila['url'];
            $ruta_antigua = "../" . $imagen_antigua;
            if (!empty($imagen_antigua) && file_exists($ruta_antigua) && strpos($imagen_antigua, 'default.jpg') === false) {
                unlink($ruta_antigua);
            }
        }
        $consulta_img->close();
    } else {
        $_SESSION['mensaje_error'] = "Error al subir la imagen.";
        header("Location: producto.php");
        exit();
    }
}

// Construir consulta UPDATE
$sql = "UPDATE producto SET 
            nombre_producto = ?, 
            id_marca = ?, 
            id_modelo = ?, 
            id_tipo = ?, 
            id_clasificacion = ?, 
            unidad_medida = ?";

$params = [$nombre, $id_marca, $id_modelo, $id_tipo, $id_clasificacion, $unidad_medida];
$types = "siiiss";

if ($nombre_imagen && $url_imagen) {
    $sql .= ", nombre_imagen = ?, url = ?";
    $params[] = $nombre_imagen;
    $params[] = $url_imagen;
    $types .= "ss";
}

$sql .= " WHERE id_producto = ?";
$params[] = $id_producto;
$types .= "i";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    $_SESSION['mensaje_error'] = "Error en la preparación de la consulta.";
    header("Location: producto.php");
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['mensaje_exito'] = "El producto se actualizó correctamente.";
} else {
    $_SESSION['mensaje_error'] = "Error al actualizar: " . $stmt->error;
}

$stmt->close();
$conexion->close();

header("Location: producto.php");
exit();
?>
