<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la marca
$idMarca = isset($_GET['id_marca']) ? (int)$_GET['id_marca'] : 0;

// Consulta para obtener el nombre de la marca
$queryMarca = "SELECT nombre_marca FROM marca WHERE id_marca = ?";
$stmtMarca = $conexion->prepare($queryMarca);
$stmtMarca->bind_param("i", $idMarca);
$stmtMarca->execute();
$resultMarca = $stmtMarca->get_result();
$marca = $resultMarca->fetch_assoc();

// Consulta para obtener los modelos relacionados con status 1
$queryModelos = "
    SELECT mo.nombre_modelo 
    FROM modelo mo
    INNER JOIN marca_modelo mm ON mo.id_modelo = mm.id_modelo
    WHERE mm.id_marca = ? AND mm.id_status = 1
";
$stmtModelos = $conexion->prepare($queryModelos);
$stmtModelos->bind_param("i", $idMarca);
$stmtModelos->execute();
$resultModelos = $stmtModelos->get_result();

$modelos = [];
while ($row = $resultModelos->fetch_assoc()) {
    $modelos[] = $row['nombre_modelo'];
}

// Respuesta en formato JSON
echo json_encode([
    'nombreMarca' => $marca['nombre_marca'],
    'modelos' => $modelos
]);

// Cerrar conexión
$conexion->close();
?>