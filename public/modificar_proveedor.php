<?php
session_start();

$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_proveedor = $_POST['id_proveedor'] ?? null;
    $nombre_proveedor = trim($_POST['nombre_proveedor'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (!$id_proveedor || empty($nombre_proveedor)) {
        $_SESSION['mensaje_error'] = "Debe completar los campos obligatorios.";
        header("Location: formulario_modificar_proveedor.php?id=$id_proveedor");
        exit();
    }

    $stmt = $conexion->prepare("
        UPDATE proveedor 
        SET nombre_proveedor = ?, telefono = ?, email = ?, direccion = ?, date_updated = NOW() 
        WHERE id_proveedor = ?
    ");
    $stmt->bind_param("ssssi", $nombre_proveedor, $telefono, $email, $direccion, $id_proveedor);

    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = "Proveedor modificado correctamente.";
        header("Location: proveedor.php");
        exit();
    } else {
        $_SESSION['mensaje_error'] = "Error al modificar el proveedor.";
        header("Location: formulario_modificar_proveedor.php?id=$id_proveedor");
        exit();
    }
}

$conexion->close();
?>
