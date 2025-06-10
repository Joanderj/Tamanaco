<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la almacen
$id_planta = isset($_GET['id_planta']) ? (int)$_GET['id_planta'] : 0;

// Consulta para obtener el nombre de la planta
$queryvalor1 = "SELECT p.nombre,s.nombre_status,p.fecha_creacion,se.nombre_sede FROM planta p 
JOIN status s ON s.id_status = p.id_status
JOIN sede se ON se.id_sede = p.id_sede
WHERE p.id_planta= ?";
$stmtvalor1 = $conexion->prepare($queryvalor1);
$stmtvalor1->bind_param("i", $id_planta);
$stmtvalor1->execute();
$resultvalor1 = $stmtvalor1->get_result();
$valor1 = $resultvalor1->fetch_assoc();


// Consulta para obtener las sedes relacionados
$queryvalor2 = "
    SELECT a.nombre_articulo
    FROM articulo a
    INNER JOIN planta_articulo pa ON a.id_articulo = pa.id_articulo
    WHERE pa.id_planta = ?
";
$stmtvalor2 = $conexion->prepare($queryvalor2);
$stmtvalor2->bind_param("i", $id_planta);
$stmtvalor2->execute();
$resultvalor2 = $stmtvalor2->get_result();

$valor2 = [];
while ($row = $resultvalor2->fetch_assoc()) {
    $valor2[] = $row['nombre_articulo'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombre' => $valor1['nombre'],
    'sede' => $valor1['nombre_sede'],
    'fecha' => $valor1['fecha_creacion'],
    'status' => $valor1['nombre_status'],
    'articulo' => $valor2
]);

// Cerrar conexión
$conexion->close();
?>