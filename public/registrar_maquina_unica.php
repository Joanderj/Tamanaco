<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

$id_maquina = $_POST['maquina'];
$codigo = $_POST['codigo'];
$almacen = $_POST['almacen'];
$id_sede = $_POST['sede'];
$status = 1; // Activa
$fecha = date("Y-m-d");

$stmt = $conexion->prepare("INSERT INTO maquina_unica (id_maquina, CodigoUnico, Almacen, id_sede, id_status, FechaUltimaActualizacion) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssis", $id_maquina, $codigo, $almacen, $id_sede, $status, $fecha);

if ($stmt->execute()) {
    echo "<script>alert('Máquina registrada correctamente'); window.location.href='inventario_maquina.php';</script>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
