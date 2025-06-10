<?php
session_start();

$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cargo = $_POST['id_cargo'] ?? null;
    $nombre_cargo = trim($_POST['nombre_cargo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!$id_cargo || $nombre_cargo === '') {
        $_SESSION['mensaje_error'] = "Debe completar los campos obligatorios.";
        header("Location: formulario_modificar_cargo.php?id=$id_cargo");
        exit();
    }

    $stmt = $conexion->prepare("UPDATE cargo SET nombre_cargo = ?, descripcion = ? WHERE id_cargo = ?");
    $stmt->bind_param("ssi", $nombre_cargo, $descripcion, $id_cargo);

    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = "Cargo modificado correctamente.";
        header("Location: cargo.php");
        exit();
    } else {
        $_SESSION['mensaje_error'] = "Error al modificar el cargo.";
        header("Location: formulario_modificar_cargo.php?id=$id_cargo");
        exit();
    }
}

$conexion->close();
?>
