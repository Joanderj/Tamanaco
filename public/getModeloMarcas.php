<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar si la conexión falló
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos.']));
}

// Obtener el ID del modelo de la solicitud
$idModelo = isset($_GET['id_modelo']) ? (int)$_GET['id_modelo'] : 0;

// Validar que se proporcionó un ID válido
if ($idModelo <= 0) {
    echo json_encode(['error' => 'ID del modelo inválido.']);
    exit;
}

// Consulta para obtener el nombre y el año del modelo
$queryModelo = "SELECT nombre_modelo, año FROM modelo WHERE id_modelo = ?";
$stmtModelo = $conexion->prepare($queryModelo);
$stmtModelo->bind_param("i", $idModelo);
$stmtModelo->execute();
$resultModelo = $stmtModelo->get_result();

// Validar que el modelo exista
if ($resultModelo->num_rows === 0) {
    echo json_encode(['error' => 'Modelo no encontrado.']);
    $stmtModelo->close();
    $conexion->close();
    exit;
}

$modelo = $resultModelo->fetch_assoc();

// Consulta para obtener las marcas relacionadas con el modelo y con status 1
$queryMarcas = "
    SELECT ma.nombre_marca 
    FROM marca ma
    INNER JOIN marca_modelo mm ON ma.id_marca = mm.id_marca
    WHERE mm.id_modelo = ? AND mm.id_status = 1
";
$stmtMarcas = $conexion->prepare($queryMarcas);
$stmtMarcas->bind_param("i", $idModelo);
$stmtMarcas->execute();
$resultMarcas = $stmtMarcas->get_result();

$marcas = [];
while ($row = $resultMarcas->fetch_assoc()) {
    $marcas[] = $row['nombre_marca'];
}

// Generar la respuesta en formato JSON
echo json_encode([
    'nombreModelo' => $modelo['nombre_modelo'],
    'año' => $modelo['año'], // Incluimos el atributo año del modelo
    'marcas' => $marcas
]);

// Cerrar la conexión y los recursos
$stmtModelo->close();
$stmtMarcas->close();
$conexion->close();
?>