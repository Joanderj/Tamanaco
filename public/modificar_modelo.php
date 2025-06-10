<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_modelo = $_POST['id_modelo'] ?? null; // Ensure id_modelo is set
    $nombre_modelo = $_POST['nombre_modelo'];
    $marcas_activas = isset($_POST['marcas']) ? array_keys($_POST['marcas']) : [];

    if ($id_modelo) {
        // Update the model name
        $stmt_update_modelo = $conexion->prepare("UPDATE modelo SET nombre_modelo = ? WHERE id_modelo = ?");
        $stmt_update_modelo->bind_param("si", $nombre_modelo, $id_modelo);
        $stmt_update_modelo->execute();
        $stmt_update_modelo->close();

        // Set all brands to inactive first
        $stmt_set_inactive = $conexion->prepare("UPDATE marca_modelo SET id_status = 2 WHERE id_modelo = ?");
        $stmt_set_inactive->bind_param("i", $id_modelo);
        $stmt_set_inactive->execute();
        $stmt_set_inactive->close();

        // Prepare statements for checking existence and inserting/updating brands
        $stmt_check_existence = $conexion->prepare("SELECT COUNT(*) FROM marca_modelo WHERE id_modelo = ? AND id_marca = ?");
        $stmt_insert_marca = $conexion->prepare("INSERT INTO marca_modelo (id_modelo, id_marca, id_status) VALUES (?, ?, ?)");
        $stmt_update_marca = $conexion->prepare("UPDATE marca_modelo SET id_status = ? WHERE id_modelo = ? AND id_marca = ?");

        // Set selected brands to active
        $active_status = 1; // Declare active_status separately
        foreach ($marcas_activas as $id_marca) {
            // Check if the brand already exists in marca_modelo
            $stmt_check_existence->bind_param("ii", $id_modelo, $id_marca);
            $stmt_check_existence->execute();
            $stmt_check_existence->bind_result($exists);
            $stmt_check_existence->fetch();
            $stmt_check_existence->free_result(); // Free the result set

            if ($exists > 0) {
                // Update existing brand to active
                $stmt_update_marca->bind_param("iii", $active_status, $id_modelo, $id_marca);
                $stmt_update_marca->execute();
            } else {
                // Insert new brand with active status
                $stmt_insert_marca->bind_param("iii", $id_modelo, $id_marca, $active_status);
                $stmt_insert_marca->execute();
            }
        }

        // Close statements after the loop
        $stmt_check_existence->close();
        $stmt_insert_marca->close();
        $stmt_update_marca->close();

        $_SESSION['mensaje_exito'] = "El modelo y las marcas se actualizaron correctamente.";
        header("Location: modelo.php");
        exit();
    } else {
        echo "ID de modelo no proporcionado.";
    }
}

$conexion->close();
?>