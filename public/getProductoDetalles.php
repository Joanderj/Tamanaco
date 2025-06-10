<?php
require 'db_connection.php'; // Este archivo debe crear una instancia PDO en $conn

$id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : 0;

try {
    $sql = "
        SELECT 
            p.id_producto,
            p.nombre_producto,
            p.unidad_medida,
            p.url,
            p.url,
            p.date_created,
            m.nombre_marca,
            mo.nombre_modelo,
            t.nombre_tipo,
            c.nombre_clasificacion,
            s.nombre_status
        FROM producto p
        LEFT JOIN marca m ON p.id_marca = m.id_marca
        LEFT JOIN modelo mo ON p.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON p.id_tipo = t.id_tipo
        LEFT JOIN clasificacion c ON p.id_clasificacion = c.id_clasificacion
        LEFT JOIN status s ON p.id_status = s.id_status
        WHERE p.id_producto = :id_producto
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt->execute();

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $detalles = [
            "Marca: " . $producto['nombre_marca'],
            "Modelo: " . $producto['nombre_modelo'],
            "Tipo: " . $producto['nombre_tipo'],
            "Clasificación: " . $producto['nombre_clasificacion'],
            "Unidad de medida: " . $producto['unidad_medida'],
            "Status: " . $producto['nombre_status'],
            "Imagen: " . $producto['url']
        ];

        echo json_encode([
            'nombreProducto' => $producto['nombre_producto'],
            'fechaCreacion' => $producto['date_created'],
            'detalles' => $detalles
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>
