<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_servicio'])) {
    echo json_encode(['error' => 'Solicitud inválida o ID de servicio no recibido']);
    exit;
}

$idServicio = intval($_POST['id_servicio']);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Servicio principal
    $stmt = $pdo->prepare("
        SELECT nombre_servicio, descripcion, tiempo_programado, tiempo_paro_maquina 
        FROM servicio 
        WHERE id_servicio = ? AND id_status = 1
    ");
    $stmt->execute([$idServicio]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$servicio) {
        echo json_encode(['error' => 'Servicio no encontrado o inactivo']);
        exit;
    }

    // Repuestos
    $stmt = $pdo->prepare("
        SELECT r.id_repuesto AS id, r.nombre_repuesto, r.url AS imagen,
               t.nombre_tipo AS tipo, m.nombre_marca AS marca, mo.nombre_modelo AS modelo,
               IFNULL(ir.cantidad, 0) AS disponible,
               IFNULL(ir.stock_minimo, 10) AS stockMinimo,
               IFNULL(ir.stock_maximo, 20) AS stockMaximo,
               sr.cantidad
        FROM servicio_repuesto sr
        JOIN repuesto r ON sr.id_repuesto = r.id_repuesto
        LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
        LEFT JOIN marca m ON r.id_marca = m.id_marca
        LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
        LEFT JOIN inventario_repuesto ir ON r.id_repuesto = ir.id_repuesto
        WHERE sr.id_servicio = ?
    ");
    $stmt->execute([$idServicio]);
    $servicio['repuestos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Productos
    $stmt = $pdo->prepare("
        SELECT p.id_producto AS id, p.nombre_producto, p.url AS imagen, p.unidad_medida AS unidad,
               t.nombre_tipo AS tipo, m.nombre_marca AS marca, mo.nombre_modelo AS modelo,
               c.nombre_clasificacion AS clasificacion,
               IFNULL(ip.cantidad, 0) AS disponible,
               IFNULL(ip.stock_minimo, 10) AS stockMinimo,
               IFNULL(ip.stock_maximo, 20) AS stockMaximo,
               sp.cantidad
        FROM servicio_producto sp
        JOIN producto p ON sp.id_producto = p.id_producto
        LEFT JOIN tipo t ON p.id_tipo = t.id_tipo
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN modelo mo ON p.id_modelo = mo.id_modelo
        LEFT JOIN clasificacion c ON p.id_clasificacion = c.id_clasificacion
        LEFT JOIN inventario_producto ip ON p.id_producto = ip.id_producto
        WHERE sp.id_servicio = ?
    ");
    $stmt->execute([$idServicio]);
    $servicio['productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Herramientas
    $stmt = $pdo->prepare("
        SELECT h.id_herramienta AS id, h.nombre_herramienta, h.descripcion, h.url AS imagen,
               t.nombre_tipo AS tipo, m.nombre_marca AS marca, mo.nombre_modelo AS modelo,
               IFNULL(ih.cantidad, 0) AS disponible,
               IFNULL(ih.stock_minimo, 10) AS stockMinimo,
               IFNULL(ih.stock_maximo, 20) AS stockMaximo,
               sh.cantidad
        FROM servicio_herramienta sh
        JOIN herramientas h ON sh.id_herramienta = h.id_herramienta
        LEFT JOIN tipo t ON h.id_tipo = t.id_tipo
        LEFT JOIN marca m ON h.id_marca = m.id_marca
        LEFT JOIN modelo mo ON h.id_modelo = mo.id_modelo
        LEFT JOIN inventario_herramientas ih ON h.id_herramienta = ih.herramienta_id
        WHERE sh.id_servicio = ?
    ");
    $stmt->execute([$idServicio]);
    $servicio['herramientas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($servicio);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
