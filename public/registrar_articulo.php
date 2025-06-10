<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

    // Obtener datos del formulario
$nombre = $_POST['nombre_articulo'];
$descripcion = $_POST['descripcion'];
$id_plantas = $_POST['planta']; // Array de IDs de artículos
$fecha_creacion = date("Y-m-d H:i:s"); // Fecha actual

// Insertar datos del articulo
$sql = "INSERT INTO articulo (descripcion_articulo, nombre_articulo, fecha_ingreso)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $descripcion, $nombre, $fecha_creacion);
$stmt->execute();

$id_articulo = $stmt->insert_id; // Obtener el ID del articulo recién insertado

// Insertar relaciones planta-artículo
$sqlRelacion = "INSERT INTO planta_articulo (id_planta, id_articulo) VALUES (?, ?)";
$stmtRelacion = $conn->prepare($sqlRelacion);

foreach ($id_plantas as $id_planta) {
    $stmtRelacion->bind_param("ii", $id_planta, $id_articulo);
    $stmtRelacion->execute();
}

echo "Planta y sus artículos registrados exitosamente.";

// Cerrar conexiones
$stmt->close();
$stmtRelacion->close();
$conn->close();
?>