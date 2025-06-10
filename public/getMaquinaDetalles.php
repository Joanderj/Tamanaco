<?php
header('Content-Type: application/json');

if (!isset($_GET['id_maquina']) || !is_numeric($_GET['id_maquina'])) {
    echo json_encode(['error' => 'ID de máquina inválido']);
    exit;
}

$id = intval($_GET['id_maquina']);

$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Obtener datos básicos de la máquina
$sql = "SELECT 
            m.nombre_maquina,
            m.descripcion_funcionamiento,
            m.elaborada_por,
            m.date_created,
            ma.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo
        FROM maquina m
        LEFT JOIN marca ma ON m.id_marca = ma.id_marca
        LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON m.id_tipo = t.id_tipo
        WHERE m.id_maquina = $id
        LIMIT 1";

$resultado = $conexion->query($sql);
if (!$resultado || $resultado->num_rows === 0) {
    echo json_encode(['error' => 'Máquina no encontrada']);
    exit;
}

$maquina = $resultado->fetch_assoc();

// Especificaciones
$sqlEsp = "SELECT nombre_especificacion, descripcion_especificacion 
           FROM especificaciones_maquina 
           WHERE id_maquina = $id";
$resEsp = $conexion->query($sqlEsp);
$especificaciones = [];
while ($row = $resEsp->fetch_assoc()) {
    $especificaciones[] = $row;
}

// Características
$sqlCar = "SELECT nombre_caracteristica, descripcion_caracteristica 
           FROM caracteristicas_maquina 
           WHERE id_maquina = $id";
$resCar = $conexion->query($sqlCar);
$caracteristicas = [];
while ($row = $resCar->fetch_assoc()) {
    $caracteristicas[] = $row;
}

$conexion->close();

// Enviar datos como JSON
echo json_encode([
    'nombreMaquina' => $maquina['nombre_maquina'],
    'tipo' => $maquina['nombre_tipo'],
    'marca' => $maquina['nombre_marca'],
    'modelo' => $maquina['nombre_modelo'],
    'descripcion' => $maquina['descripcion_funcionamiento'],
    'elaboradaPor' => $maquina['elaborada_por'],
    'fechaCreacion' => $maquina['date_created'],
    'especificaciones' => $especificaciones,
    'caracteristicas' => $caracteristicas
]);
?>
