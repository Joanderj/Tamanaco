<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_marca = $_POST['id_marca'] ?? null; // Ensure id_marca is set
    $nombre_marca = $_POST['nombre_marca'];
    $modelos_activos = isset($_POST['modelos']) ? array_keys($_POST['modelos']) : [];

    if ($id_marca) {
        // Update the brand name
        $stmt_update_marca = $conexion->prepare("UPDATE marca SET nombre_marca = ? WHERE id_marca = ?");
        $stmt_update_marca->bind_param("si", $nombre_marca, $id_marca);
        $stmt_update_marca->execute();
        $stmt_update_marca->close();

        // Set all models to inactive first
        $stmt_set_inactive = $conexion->prepare("UPDATE marca_modelo SET id_status = 2 WHERE id_marca = ?");
        $stmt_set_inactive->bind_param("i", $id_marca);
        $stmt_set_inactive->execute();
        $stmt_set_inactive->close();

        // Prepare statements for checking existence and inserting/updating models
        $stmt_check_existence = $conexion->prepare("SELECT COUNT(*) FROM marca_modelo WHERE id_marca = ? AND id_modelo = ?");
        $stmt_insert_modelo = $conexion->prepare("INSERT INTO marca_modelo (id_marca, id_modelo, id_status) VALUES (?, ?, ?)");
        $stmt_update_modelo = $conexion->prepare("UPDATE marca_modelo SET id_status = ? WHERE id_marca = ? AND id_modelo = ?");

        // Set selected models to active
        $active_status = 1; // Declare active_status separately
        foreach ($modelos_activos as $id_modelo) {
            // Check if the model already exists in marca_modelo
            $stmt_check_existence->bind_param("ii", $id_marca, $id_modelo);
            $stmt_check_existence->execute();
            $stmt_check_existence->bind_result($exists);
            $stmt_check_existence->fetch();
            $stmt_check_existence->free_result(); // Free the result set

            if ($exists > 0) {
                // Update existing model to active
                $stmt_update_modelo->bind_param("iii", $active_status, $id_marca, $id_modelo);
                $stmt_update_modelo->execute();
            } else {
                // Insert new model with active status
                $stmt_insert_modelo->bind_param("iii", $id_marca, $id_modelo, $active_status);
                $stmt_insert_modelo->execute();
            }
        }

        // Close statements after the loop
        $stmt_check_existence->close();
        $stmt_insert_modelo->close();
        $stmt_update_modelo->close();

        $_SESSION['mensaje_exito'] = "La marca y los modelos se actualizaron correctamente.";
        header("Location: marca.php");
        exit();
    } else {
        echo "ID de marca no proporcionado.";
    }
}

$conexion->close();
?>