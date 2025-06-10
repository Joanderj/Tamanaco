<?php
// Configuración de conexión a la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'bd_tamanaco';

// Conexión a la base de datos
$conexion = new mysqli($host, $username, $password, $dbname);

// Verificar la conexión
if ($conexion->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos: ' . $conexion->connect_error]));
}

// Verificar el parámetro id_cargo desde la solicitud
if (!isset($_GET['id_cargo']) || !is_numeric($_GET['id_cargo'])) {
    echo json_encode(['error' => 'ID del cargo inválido']);
    exit;
}

$idCargo = (int)$_GET['id_cargo']; // Convertir el ID a entero

// Consultar el nombre y la descripción del cargo
$queryCargo = "SELECT nombre_cargo, descripcion FROM cargo WHERE id_cargo = ?";
$stmtCargo = $conexion->prepare($queryCargo);

if (!$stmtCargo) {
    echo json_encode(['error' => 'Error al preparar la consulta de cargo: ' . $conexion->error]);
    exit;
}

$stmtCargo->bind_param('i', $idCargo);
$stmtCargo->execute();
$resultCargo = $stmtCargo->get_result();

if ($resultCargo->num_rows === 0) {
    echo json_encode(['error' => 'Cargo no encontrado']);
    exit;
}

$cargo = $resultCargo->fetch_assoc();

// Consultar las personas relacionadas con este cargo
$queryPersonas = "SELECT primer_nombre, primer_apellido FROM personas WHERE id_cargo = ?";
$stmtPersonas = $conexion->prepare($queryPersonas);

if (!$stmtPersonas) {
    echo json_encode(['error' => 'Error al preparar la consulta de personas: ' . $conexion->error]);
    exit;
}

$stmtPersonas->bind_param('i', $idCargo);
$stmtPersonas->execute();
$resultPersonas = $stmtPersonas->get_result();

$personas = [];
while ($row = $resultPersonas->fetch_assoc()) {
    $personas[] = $row;
}

// Retornar los datos en formato JSON
echo json_encode([
    'nombreCargo' => $cargo['nombre_cargo'],
    'descripcion' => $cargo['descripcion'],
    'personas' => $personas
]);

// Cerrar recursos
$stmtCargo->close();
$stmtPersonas->close();
$conexion->close();
?>