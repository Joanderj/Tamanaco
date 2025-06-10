<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar si hay errores de conexión
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID de la marca desde la solicitud
$marcaId = isset($_GET['marca']) ? (int)$_GET['marca'] : 0;

// Validar que el ID sea válido
if ($marcaId <= 0) {
    echo json_encode([]);
    exit;
}

// Consultar los modelos relacionados a la marca seleccionada
$consulta = "
    SELECT modelo.id_modelo, modelo.nombre_modelo 
    FROM modelo
    INNER JOIN marca_modelo ON modelo.id_modelo = marca_modelo.id_modelo
    WHERE marca_modelo.id_marca = ? AND modelo.id_status = 1
";
$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $marcaId);
$stmt->execute();
$resultado = $stmt->get_result();

$modelos = [];
while ($fila = $resultado->fetch_assoc()) {
    $modelos[] = $fila;
}

// Devolver los modelos como JSON
echo json_encode($modelos);

// Cerrar la conexión
$stmt->close();
$conexion->close();
?>