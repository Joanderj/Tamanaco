<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

    // Obtener datos del formulario
$nombre = $_POST['planta'];
$id_sede = $_POST['sede'];
$id_articulos = $_POST['articulo']; // Array de IDs de artículos
$fecha_creacion = date("Y-m-d H:i:s"); // Fecha actual

// Insertar datos en la tabla planta
$sql = "INSERT INTO planta (id_sede, id_status, nombre, fecha_creacion)
        VALUES (?, 1, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $id_sede, $nombre, $fecha_creacion);
$stmt->execute();

$id_planta = $stmt->insert_id; // Obtener el ID de la planta recién insertada

// Insertar relaciones planta-artículo
$sqlRelacion = "INSERT INTO planta_articulo (id_planta, id_articulo) VALUES (?, ?)";
$stmtRelacion = $conn->prepare($sqlRelacion);

foreach ($id_articulos as $id_articulo) {
    $stmtRelacion->bind_param("ii", $id_planta, $id_articulo);
    $stmtRelacion->execute();
}

echo "Planta y sus artículos registrados exitosamente.";

// Cerrar conexiones
$stmt->close();
$stmtRelacion->close();
$conn->close();
?>