<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la almacen
$id_articulo = isset($_GET['id_articulo']) ? (int)$_GET['id_articulo'] : 0;

// Consulta para obtener el nombre de la planta
$queryvalor1 = "SELECT nombre_articulo,fecha_ingreso FROM articulo 
WHERE id_articulo= ?";
$stmtvalor1 = $conexion->prepare($queryvalor1);
$stmtvalor1->bind_param("i", $id_articulo);
$stmtvalor1->execute();
$resultvalor1 = $stmtvalor1->get_result();
$valor1 = $resultvalor1->fetch_assoc();


// Consulta para obtener las sedes relacionados
$queryvalor2 = "
    SELECT p.nombre
    FROM planta p
    INNER JOIN planta_articulo pa ON p.id_planta = pa.id_planta
    WHERE pa.id_articulo = ?
";
$stmtvalor2 = $conexion->prepare($queryvalor2);
$stmtvalor2->bind_param("i", $id_articulo);
$stmtvalor2->execute();
$resultvalor2 = $stmtvalor2->get_result();

$valor2 = [];
while ($row = $resultvalor2->fetch_assoc()) {
    $valor2[] = $row['nombre'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombre' => $valor1['nombre_articulo'],
    'fecha' => $valor1['fecha_ingreso'],
    'articulo' => $valor2
]);

// Cerrar conexión
$conexion->close();
?>