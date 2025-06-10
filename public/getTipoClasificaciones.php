<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_clasificacion = $_POST['id_clasificacion'] ?? null; // ID de clasificación
    $nombre_clasificacion = $_POST['nombre_clasificacion'];
    $abreviatura = $_POST['abreviatura_clasificacion'];
    $tipos_activos = isset($_POST['tipos']) ? array_keys($_POST['tipos']) : [];

    if ($id_clasificacion) {
        // Actualizar el nombre y abreviatura de la clasificación
        $stmt_update_clasificacion = $conexion->prepare("UPDATE clasificacion SET nombre_clasificacion = ?, abreviacion_clasificacion = ? WHERE id_clasificacion = ?");
        $stmt_update_clasificacion->bind_param("ssi", $nombre_clasificacion, $abreviatura, $id_clasificacion);
        $stmt_update_clasificacion->execute();
        $stmt_update_clasificacion->close();

        // Desactivar todas las relaciones de tipo con clasificación
        $stmt_set_inactive = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = 2 WHERE id_clasificacion = ?");
        $stmt_set_inactive->bind_param("i", $id_clasificacion);
        $stmt_set_inactive->execute();
        $stmt_set_inactive->close();

        // Preparar consultas para existencia e inserción/actualización
        $stmt_check_existence = $conexion->prepare("SELECT COUNT(*) FROM tipo_clasificacion WHERE id_clasificacion = ? AND id_tipo = ?");
        $stmt_insert_tipo = $conexion->prepare("INSERT INTO tipo_clasificacion (id_tipo, id_clasificacion, id_status) VALUES (?, ?, ?)");
        $stmt_update_tipo = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = ? WHERE id_clasificacion = ? AND id_tipo = ?");

        // Activar solo los tipos seleccionados
        $active_status = 1;
        foreach ($tipos_activos as $id_tipo) {
            $stmt_check_existence->bind_param("ii", $id_clasificacion, $id_tipo);
            $stmt_check_existence->execute();
            $stmt_check_existence->bind_result($exists);
            $stmt_check_existence->fetch();
            $stmt_check_existence->free_result();

            if ($exists > 0) {
                $stmt_update_tipo->bind_param("iii", $active_status, $id_clasificacion, $id_tipo);
                $stmt_update_tipo->execute();
            } else {
                $stmt_insert_tipo->bind_param("iii", $id_tipo, $id_clasificacion, $active_status);
                $stmt_insert_tipo->execute();
            }
        }

        // Cerrar consultas
        $stmt_check_existence->close();
        $stmt_insert_tipo->close();
        $stmt_update_tipo->close();

        $_SESSION['mensaje_exito'] = "La clasificación se actualizó correctamente y los tipos seleccionados fueron activados.";
        header("Location: clasificacion.php");
        exit();
    } else {
        echo "ID de clasificación no proporcionado.";
    }
}

$conexion->close();
?>