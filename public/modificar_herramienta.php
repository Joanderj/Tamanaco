<?php
session_start();

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener ID de la herramienta y validar
$id_herramienta = $_POST['id_herramienta'] ?? null;
if (!$id_herramienta || !is_numeric($id_herramienta)) {
    $_SESSION['mensaje_error'] = "ID de herramienta no válido.";
    header("Location: herramienta.php");
    exit();
}

// Obtener y sanitizar los datos
$nombre = trim($_POST['nombre_herramienta'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$id_marca = intval($_POST['id_marca'] ?? 0);
$id_modelo = intval($_POST['id_modelo'] ?? 0);
$id_tipo = intval($_POST['id_tipo'] ?? 0);

// Validación básica
if (empty($nombre) || $id_marca === 0) {
    $_SESSION['mensaje_error'] = "Campos obligatorios faltantes.";
    header("Location: herramienta.php");
    exit();
}

// Variables para la imagen
$nombre_imagen = null;
$url_imagen = null;

if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = $_FILES['nombre_imagen']['name'];
    $temporal = $_FILES['nombre_imagen']['tmp_name'];
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    // Validar extensión permitida
    $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $ext_permitidas)) {
        $_SESSION['mensaje_error'] = "Formato de imagen no permitido.";
        header("Location: herramienta.php");
        exit();
    }

    // Crear nombre único para la imagen
    $nombre_unico = uniqid('herramienta_', true) . "." . $extension;
    $ruta_destino = "../public/servidor_img/herramientas/" . $nombre_unico;

    if (move_uploaded_file($temporal, $ruta_destino)) {
        $nombre_imagen = $nombre_unico;
        // Ruta relativa para guardar en DB (sin ../)
        $url_imagen = "servidor_img/herramientas/" . $nombre_unico;

        // Buscar la imagen antigua para eliminarla
        $consulta_img = $conexion->prepare("SELECT url FROM herramientas WHERE id_herramienta = ?");
        $consulta_img->bind_param("i", $id_herramienta);
        $consulta_img->execute();
        $resultado = $consulta_img->get_result();

        if ($resultado && $fila = $resultado->fetch_assoc()) {
            $imagen_antigua = $fila['url']; // Ejemplo: public/servidor_img/herramientas/archivo.jpg
            $ruta_antigua = "../" . $imagen_antigua;
            if (!empty($imagen_antigua) && file_exists($ruta_antigua) && strpos($imagen_antigua, 'default.jpg') === false) {
                unlink($ruta_antigua);
            }
        }
        $consulta_img->close();
    } else {
        $_SESSION['mensaje_error'] = "Error al subir la imagen.";
        header("Location: herramienta.php");
        exit();
    }
}

// Construir la consulta UPDATE con o sin imagen
$sql = "UPDATE herramientas SET 
            nombre_herramienta = ?, 
            descripcion = ?, 
            id_marca = ?, 
            id_modelo = ?, 
            id_tipo = ?";

$params = [$nombre, $descripcion, $id_marca, $id_modelo, $id_tipo];
$types = "ssiii";

if ($nombre_imagen && $url_imagen) {
    $sql .= ", nombre_imagen = ?, url = ?";
    $params[] = $nombre_imagen;
    $params[] = $url_imagen;
    $types .= "ss";
}

$sql .= " WHERE id_herramienta = ?";
$params[] = $id_herramienta;
$types .= "i";

// Preparar y ejecutar
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    $_SESSION['mensaje_error'] = "Error en la preparación de la consulta.";
    header("Location: herramienta.php");
    exit();
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['mensaje_exito'] = "La herramienta se actualizó correctamente.";
} else {
    $_SESSION['mensaje_error'] = "Error al actualizar: " . $stmt->error;
}

$stmt->close();
$conexion->close();

// Redirigir de vuelta
header("Location: herramienta.php");
exit();
?>
