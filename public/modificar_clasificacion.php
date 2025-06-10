<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_clasificacion = $_POST['id_clasificacion'] ?? null; // Validar ID
    $nombre_clasificacion = $_POST['nombre_clasificacion'] ?? ''; 
    $tipos_activos = isset($_POST['tipos']) ? $_POST['tipos'] : [];

    if (!$id_clasificacion) {
        die("ID de clasificación no proporcionado.");
    }

    // Actualizar el nombre de la clasificación
    $stmt_update_clasificacion = $conexion->prepare("UPDATE clasificacion SET nombre_clasificacion = ? WHERE id_clasificacion = ?");
    $stmt_update_clasificacion->bind_param("si", $nombre_clasificacion, $id_clasificacion);
    $stmt_update_clasificacion->execute();
    $stmt_update_clasificacion->close();

    // Desactivar todos los tipos relacionados con esta clasificación
    $stmt_set_inactive = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = 2 WHERE id_clasificacion = ?");
    $stmt_set_inactive->bind_param("i", $id_clasificacion);
    $stmt_set_inactive->execute();
    $stmt_set_inactive->close();

    // Preparar consultas para verificar existencia e insertar/actualizar registros
    $stmt_check_existence = $conexion->prepare("SELECT id_status FROM tipo_clasificacion WHERE id_clasificacion = ? AND id_tipo = ?");
    $stmt_insert_tipo = $conexion->prepare("INSERT INTO tipo_clasificacion (id_clasificacion, id_tipo, id_status) VALUES (?, ?, ?)");
    $stmt_update_tipo = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = ? WHERE id_clasificacion = ? AND id_tipo = ?");

    // Activar los tipos seleccionados
    foreach ($tipos_activos as $id_tipo => $status) {
        // Verificar si el tipo ya existe en tipo_clasificacion
        $stmt_check_existence->bind_param("ii", $id_clasificacion, $id_tipo);
        $stmt_check_existence->execute();
        $resultado = $stmt_check_existence->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt_check_existence->free_result();

        if ($fila) {
            // Si existe, actualizar el estado
            $stmt_update_tipo->bind_param("iii", $status, $id_clasificacion, $id_tipo);
            $stmt_update_tipo->execute();
        } else {
            // Insertar nuevo registro con estado correspondiente
            $stmt_insert_tipo->bind_param("iii", $id_clasificacion, $id_tipo, $status);
            $stmt_insert_tipo->execute();
        }
    }

    // Cerrar consultas
    $stmt_check_existence->close();
    $stmt_insert_tipo->close();
    $stmt_update_tipo->close();

    $_SESSION['mensaje_exito'] = "La clasificación y los tipos se actualizaron correctamente.";
    header("Location: clasificacion.php");
    exit();
}

$conexion->close();
?>