<?php
session_start();
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener datos del formulario
$id_repuesto = $_POST['id_repuesto'];
$nombre = $_POST['nombre_repuesto'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$tipo = $_POST['tipo'];
$sugerencia = $_POST['sugerencia_mantenimiento'];

$ids_esp = $_POST['id_especificacion'] ?? [];
$detalles_esp = $_POST['detalle_especificacion'] ?? [];
$valores_esp = $_POST['valor_especificacion'] ?? [];

// Variables para imagen
$nombre_imagen = null;
$url_imagen = null;

// Procesar imagen si se carga
if (isset($_FILES['nombre_imagen']) && $_FILES['nombre_imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = $_FILES['nombre_imagen']['name'];
    $temporal = $_FILES['nombre_imagen']['tmp_name'];
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($extension, $ext_permitidas)) {
        $_SESSION['mensaje_error'] = "Formato de imagen no permitido.";
        header("Location: repuesto.php");
        exit();
    }

    $nombre_unico = uniqid('repuesto_', true) . "." . $extension;
    $ruta_destino = "servidor_img/repuesto/" . $nombre_unico;

    if (move_uploaded_file($temporal, $ruta_destino)) {
        $nombre_imagen = $nombre_unico;
        $url_imagen = $ruta_destino;
    }
}

// Actualizar información del repuesto
$sql_update = "UPDATE repuesto 
               SET nombre_repuesto = ?, id_marca = ?, id_modelo = ?, id_tipo = ?, sugerencia_mantenimiento = ?";

$params = [$nombre, $marca, $modelo, $tipo, $sugerencia];

// Si se cargó imagen, agregarla al UPDATE
if ($nombre_imagen) {
    $sql_update .= ", imagen = ?";
    $params[] = $url_imagen;
}

$sql_update .= " WHERE id_repuesto = ?";
$params[] = $id_repuesto;

$stmt = $conexion->prepare($sql_update);
$stmt->execute($params);

// Actualizar especificaciones
for ($i = 0; $i < count($valores_esp); $i++) {
    $detalle = $detalles_esp[$i];
    $valor = $valores_esp[$i];

    if (isset($ids_esp[$i])) {
        // Ya existe, actualizamos
        $id_esp = $ids_esp[$i];
        $sql_upd_esp = "UPDATE especificaciones_repuestos SET detalle_especificacion = ?, valor_especificacion = ? WHERE id_especificacion = ? AND id_repuesto = ?";
        $stmt_esp = $conexion->prepare($sql_upd_esp);
        $stmt_esp->execute([$detalle, $valor, $id_esp, $id_repuesto]);
    } else {
        // Nueva especificación
        $sql_ins_esp = "INSERT INTO especificaciones_repuestos (id_repuesto, detalle_especificacion, valor_especificacion) VALUES (?, ?, ?)";
        $stmt_new = $conexion->prepare($sql_ins_esp);
        $stmt_new->execute([$id_repuesto, $detalle, $valor]);
    }
}

$_SESSION['mensaje_exito'] = "Repuesto modificado correctamente.";
header("Location: repuesto.php");
exit();
