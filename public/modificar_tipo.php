<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tipo = $_POST['id_tipo'] ?? null; // Ensure id_tipo is set
    $nombre_tipo = $_POST['nombre_tipo'];
    $clasificaciones_activas = isset($_POST['id_clasificacion']) ? $_POST['id_clasificacion'] : [];

    if ($id_tipo) {
        // Update the type name
        $stmt_update_tipo = $conexion->prepare("UPDATE tipo SET nombre_tipo = ? WHERE id_tipo = ?");
        $stmt_update_tipo->bind_param("si", $nombre_tipo, $id_tipo);
        $stmt_update_tipo->execute();
        $stmt_update_tipo->close();

        // Set all classifications to inactive first
        $stmt_set_inactive = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = 2 WHERE id_tipo = ?");
        $stmt_set_inactive->bind_param("i", $id_tipo);
        $stmt_set_inactive->execute();
        $stmt_set_inactive->close();

        // Prepare statements for checking existence and inserting/updating classifications
        $stmt_check_existence = $conexion->prepare("SELECT COUNT(*) FROM tipo_clasificacion WHERE id_tipo = ? AND id_clasificacion = ?");
        $stmt_insert_clasificacion = $conexion->prepare("INSERT INTO tipo_clasificacion (id_tipo, id_clasificacion, id_status) VALUES (?, ?, ?)");
        $stmt_update_clasificacion = $conexion->prepare("UPDATE tipo_clasificacion SET id_status = ? WHERE id_tipo = ? AND id_clasificacion = ?");

        // Set selected classifications to active
        $active_status = 1; // Declare active_status separately
        foreach ($clasificaciones_activas as $id_clasificacion) {
            // Check if the classification already exists in tipo_clasificacion
            $stmt_check_existence->bind_param("ii", $id_tipo, $id_clasificacion);
            $stmt_check_existence->execute();
            $stmt_check_existence->bind_result($exists);
            $stmt_check_existence->fetch();
            $stmt_check_existence->free_result(); // Free the result set

            if ($exists > 0) {
                // Update existing classification to active
                $stmt_update_clasificacion->bind_param("iii", $active_status, $id_tipo, $id_clasificacion);
                $stmt_update_clasificacion->execute();
            } else {
                // Insert new classification with active status
                $stmt_insert_clasificacion->bind_param("iii", $id_tipo, $id_clasificacion, $active_status);
                $stmt_insert_clasificacion->execute();
            }
        }

        // Close statements after the loop
        $stmt_check_existence->close();
        $stmt_insert_clasificacion->close();
        $stmt_update_clasificacion->close();

        $_SESSION['mensaje_exito'] = "El tipo y las clasificaciones se actualizaron correctamente.";
        header("Location: tipo.php");
        exit();
    } else {
        echo "ID de tipo no proporcionado.";
    }
}

$conexion->close();
?>