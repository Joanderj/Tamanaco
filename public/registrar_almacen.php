<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos del formulario
$sucursal = $_POST['sucursal'];
$almacen = $_POST['almacen'];
$sede = !empty($_POST['sede']) ? $_POST['sede'] : null;
$id_status = 1; // ID de status fijo
$fecha_creacion = date("Y-m-d H:i:s"); // Fecha actual

// Insertar datos en la tabla sucursal
$sql = "INSERT INTO almacen (id_sucursal, id_status, nombre, fecha_creacion,id_sede)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissi", $sucursal, $id_status, $almacen, $fecha_creacion,$sede);

if ($stmt->execute()) {
    echo "Sucursal registrada exitosamente.";
} else {
    echo "Error: " . $stmt->error;
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>