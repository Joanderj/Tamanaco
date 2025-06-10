<?php
session_start();
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_maquina = $_POST['id_maquina'] ?? null;
    if (!$id_maquina || !is_numeric($id_maquina)) {
        $_SESSION['mensaje_error'] = "ID de máquina no válido.";
        header("Location: maquina.php");
        exit();
    }

    // Datos principales
    $nombre_maquina = trim($_POST['nombre_maquina'] ?? '');
    $descripcion_funcionamiento = trim($_POST['descripcion_funcionamiento'] ?? '');
    $elaborada_por = trim($_POST['elaborada_por'] ?? '');
    $id_marca = intval($_POST['marca'] ?? 0);
    $id_modelo = intval($_POST['modelo'] ?? 0);
    $id_tipo = intval($_POST['tipo'] ?? 0);
    $sugerencia_mantenimiento = trim($_POST['sugerencia_mantenimiento'] ?? '');
    $color = trim($_POST['color'] ?? '');

    if (empty($nombre_maquina) || $id_marca === 0 || $id_modelo === 0 || $id_tipo === 0) {
        $_SESSION['mensaje_error'] = "Campos obligatorios faltantes.";
        header("Location: maquina.php");
        exit();
    }

    // Imagen actual
    $consulta_img = $conexion->prepare("SELECT url FROM maquina WHERE id_maquina = ?");
    $consulta_img->bind_param("i", $id_maquina);
    $consulta_img->execute();
    $resultado = $consulta_img->get_result();
    $datos_maquina = $resultado->fetch_assoc();
    $url_imagen_actual = $datos_maquina['url'] ?? null;
    $consulta_img->close();

    $nombre_imagen = null;
    $url_imagen = null;

    // Procesar nueva imagen
    if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['nombre_imagen'];
        $nombre_archivo = $archivo['name'];
        $temporal = $archivo['tmp_name'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $permitidas)) {
            $_SESSION['mensaje_error'] = "Formato de imagen no permitido.";
            header("Location: maquina.php");
            exit();
        }

        $nombre_unico = uniqid('maquina_', true) . "." . $extension;
        $ruta_destino = "servidor_img/maquina/" . $nombre_unico;

        if (move_uploaded_file($temporal, $ruta_destino)) {
            $nombre_imagen = $nombre_unico;
            $url_imagen = $ruta_destino;

            if ($url_imagen_actual && file_exists("../" . $url_imagen_actual) && strpos($url_imagen_actual, 'default.jpg') === false) {
                unlink("../" . $url_imagen_actual);
            }
        } else {
            $_SESSION['mensaje_error'] = "Error al subir la imagen.";
            header("Location: maquina.php");
            exit();
        }
    }

    // Actualizar datos principales
    $sql = "UPDATE maquina SET 
                nombre_maquina = ?, 
                descripcion_funcionamiento = ?, 
                elaborada_por = ?, 
                id_marca = ?, 
                id_modelo = ?, 
                id_tipo = ?, 
                sugerencia_mantenimiento = ?, 
                color = ?";
    $parametros = [
        $nombre_maquina, 
        $descripcion_funcionamiento, 
        $elaborada_por, 
        $id_marca, 
        $id_modelo, 
        $id_tipo, 
        $sugerencia_mantenimiento,
        $color
    ];
    $tipos = "ssssiiis";

    if ($nombre_imagen && $url_imagen) {
        $sql .= ", nombre_imagen = ?, url = ?";
        $parametros[] = $nombre_imagen;
        $parametros[] = $url_imagen;
        $tipos .= "ss";
    }

    $sql .= " WHERE id_maquina = ?";
    $parametros[] = $id_maquina;
    $tipos .= "i";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        $_SESSION['mensaje_error'] = "Error en la preparación de la consulta.";
        header("Location: maquina.php");
        exit();
    }

    $stmt->bind_param($tipos, ...$parametros);
    $stmt->execute();
    $stmt->close();
$ids_caracteristicas = $_POST['id_caracteristica'] ?? [];
$nombres_caracteristica = $_POST['nombres_caracteristica'] ?? [];
$descripciones_caracteristica = $_POST['descripciones_caracteristica'] ?? [];

foreach ($nombres_caracteristica as $index => $nombre) {
    $descripcion = $descripciones_caracteristica[$index] ?? '';
    $id = $ids_caracteristicas[$index] ?? null;

    if ($id && is_numeric($id)) {
        // Ya existe, actualizar
        $stmt = $conexion->prepare("UPDATE caracteristicas_maquina SET nombre_caracteristica = ?, descripcion_caracteristica = ? WHERE id_caracteristica = ? AND id_maquina = ?");
        $stmt->bind_param("ssii", $nombre, $descripcion, $id, $id_maquina);
        $stmt->execute();
        $stmt->close();
    } else {
        // Nueva, insertar
        $stmt = $conexion->prepare("INSERT INTO caracteristicas_maquina (id_maquina, nombre_caracteristica, descripcion_caracteristica, fecha_creacion) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $id_maquina, $nombre, $descripcion);
        $stmt->execute();
        $stmt->close();
    }
}
$ids_especificaciones = $_POST['id_especificacion'] ?? [];
$nombres_especificacion = $_POST['nombres_especificacion'] ?? [];
$descripciones_especificacion = $_POST['descripciones_especificacion'] ?? [];

foreach ($nombres_especificacion as $index => $nombre) {
    $descripcion = $descripciones_especificacion[$index] ?? '';
    $id = $ids_especificaciones[$index] ?? null;

    if ($id && is_numeric($id)) {
        // Ya existe, actualizar
        $stmt = $conexion->prepare("UPDATE especificaciones_maquina SET nombre_especificacion = ?, descripcion_especificacion = ? WHERE id_especificacion = ? AND id_maquina = ?");
        $stmt->bind_param("ssii", $nombre, $descripcion, $id, $id_maquina);
        $stmt->execute();
        $stmt->close();
    } else {
        // Nueva, insertar
        $stmt = $conexion->prepare("INSERT INTO especificaciones_maquina (id_maquina, nombre_especificacion, descripcion_especificacion, fecha_creacion) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $id_maquina, $nombre, $descripcion);
        $stmt->execute();
        $stmt->close();
    }
}



    $_SESSION['mensaje_exito'] = "Máquina modificada correctamente.";
    header("Location: maquina.php");
    exit();

} else {
    echo "Acceso no permitido.";
}
?>
