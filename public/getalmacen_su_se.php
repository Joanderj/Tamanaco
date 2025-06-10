<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la almacen
$idalmacen = isset($_GET['id_almacen']) ? (int)$_GET['id_almacen'] : 0;

// Consulta para obtener el nombre del almcen
$queryvalor1 = "SELECT a.nombre,s.nombre_status,a.fecha_creacion,sc.nombre as sucursal FROM almacen a 
JOIN status s ON s.id_status = a.id_status
JOIN sucursal sc ON sc.id_sucursal = a.id_sucursal
WHERE id_almacen = ?";
$stmtvalor1 = $conexion->prepare($queryvalor1);
$stmtvalor1->bind_param("i", $idalmacen);
$stmtvalor1->execute();
$resultvalor1 = $stmtvalor1->get_result();
$valor1 = $resultvalor1->fetch_assoc();


// Consulta para obtener las sedes relacionados
$queryvalor2 = "
    SELECT s.nombre_sede
    FROM sede s
    INNER JOIN almacen a ON a.id_sede = s.id_sede
    WHERE a.id_almacen = ?
";
$stmtvalor2 = $conexion->prepare($queryvalor2);
$stmtvalor2->bind_param("i", $idalmacen);
$stmtvalor2->execute();
$resultvalor2 = $stmtvalor2->get_result();

$valor2 = [];
while ($row = $resultvalor2->fetch_assoc()) {
    $valor2[] = $row['nombre_sede'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombre' => $valor1['nombre'],
    'fecha' => $valor1['fecha_creacion'],
    'sucursal' => $valor1['sucursal'],
    'status' => $valor1['nombre_status'],
    'sede' => $valor2
]);

// Cerrar conexión
$conexion->close();
?>