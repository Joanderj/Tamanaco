<?php
header('Content-Type: application/json');
require_once 'conexion.php'; // Incluye tu archivo de conexión

if (!isset($_GET['id_servicio'])) {
    echo json_encode([]);
    exit;
}

$id_servicio = intval($_GET['id_servicio']);

// Consulta los repuestos asociados al servicio
$sql = "
    SELECT 
        r.id_repuesto AS id,
        r.nombre,
        r.marca,
        r.modelo,
        r.tipo,
        r.unidad,
        r.clasificacion,
        r.imagen,
        isr.cantidad,
        COALESCE(ir.cantidad, 0) AS disponible,
        COALESCE(ir.stock_maximo, 0) AS stockMaximo
    FROM item_servicio_repuesto isr
    INNER JOIN repuesto r ON isr.id_repuesto = r.id_repuesto
    LEFT JOIN inventario_repuesto ir ON r.id_repuesto = ir.id_repuesto
    WHERE isr.id_servicio = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_servicio);
$stmt->execute();
$result = $stmt->get_result();

$repuestos = [];

while ($row = $result->fetch_assoc()) {
    $repuestos[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'marca' => $row['marca'],
        'modelo' => $row['modelo'],
        'tipo' => $row['tipo'],
        'unidad' => $row['unidad'],
        'clasificacion' => $row['clasificacion'],
        'imagen' => $row['imagen'] ? $row['imagen'] : 'img/repuestos/default.png',
        'cantidad' => intval($row['cantidad']),
        'disponible' => intval($row['disponible']),
        'stockMaximo' => intval($row['stockMaximo'])
    ];
}

echo json_encode($repuestos);
