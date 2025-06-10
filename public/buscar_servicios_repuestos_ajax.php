<?php
require_once 'db_connection.php';

$idServicio = isset($_GET['id_servicio']) ? intval($_GET['id_servicio']) : 0;
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$por_pagina = 5;
$inicio = ($pagina - 1) * $por_pagina;

if ($idServicio <= 0) {
    echo "<p class='text-red-600 text-sm'>ID de servicio inválido.</p>";
    exit;
}

// Obtener total de repuestos para paginación
$stmtTotal = $conn->prepare("
    SELECT COUNT(*) 
    FROM servicio_repuesto sr
    JOIN repuesto r ON sr.id_repuesto = r.id_repuesto
    WHERE sr.id_servicio = ?
");
$stmtTotal->execute([$idServicio]);
$total_registros = $stmtTotal->fetchColumn();
$total_paginas = ceil($total_registros / $por_pagina);

// Obtener los repuestos del servicio con límite
$stmt = $conn->prepare("
    SELECT 
        r.id_repuesto, r.nombre_repuesto, r.url,
        m.nombre_marca, mo.nombre_modelo, t.nombre_tipo,
        i.cantidad, i.stock_minimo, i.stock_maximo,
        r.sugerencia_mantenimiento,
        c.nombre_clasificacion,
        u.nombre_unidad_medida
    FROM servicio_repuesto sr
    JOIN repuesto r ON sr.id_repuesto = r.id_repuesto
    LEFT JOIN marca m ON r.id_marca = m.id_marca
    LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
    LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
    LEFT JOIN clasificacion c ON r.id_clasificacion = c.id_clasificacion
    LEFT JOIN unidad_medida u ON r.id_unidad_medida = u.id_unidad_medida
    LEFT JOIN inventario_repuesto i ON r.id_repuesto = i.id_repuesto
    WHERE sr.id_servicio = ?
    LIMIT $inicio, $por_pagina
");
$stmt->execute([$idServicio]);
$repuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar los repuestos
foreach ($repuestos as $row) {
    $id = (int)$row['id_repuesto'];
    $nombre = htmlspecialchars($row['nombre_repuesto']);
    $unidad = htmlspecialchars($row['nombre_unidad_medida'] ?? '');
    $clasificacion = htmlspecialchars($row['nombre_clasificacion'] ?? '');
    $imagen = htmlspecialchars($row['url'] ?? 'img/default.png');
    $marca = htmlspecialchars($row['nombre_marca'] ?? 'Sin marca');
    $modelo = htmlspecialchars($row['nombre_modelo'] ?? 'Sin modelo');
    $tipo = htmlspecialchars($row['nombre_tipo'] ?? 'Sin tipo');
    $disponible = (int)($row['cantidad'] ?? 0);
    $stockMin = (int)($row['stock_minimo'] ?? 0);
    $stockMax = (int)($row['stock_maximo'] ?? 0);

    echo "
    <div class='flex items-start gap-3 border-b py-2 px-1 hover:bg-gray-100 transition'>
        <img src='{$imagen}' class='w-14 h-14 object-cover rounded-md mt-1'>
        <div class='flex-1'>
            <p class='font-semibold'>{$nombre}</p>
            <p class='text-sm text-gray-600'>{$marca} / {$modelo} / {$tipo}</p>
            <p class='text-xs text-gray-500'>{$clasificacion} - {$unidad}</p>
            <p class='text-xs mt-1'>
                <span class='text-green-600'>Disponible: {$disponible}</span> |
                <span class='text-yellow-600'>Min: {$stockMin}</span> |
                <span class='text-blue-600'>Max: {$stockMax}</span>
            </p>
        </div>
        <button type='button'
            onclick=\"agregarRepuestoDesdeInventario(
                {$id}, 
                '{$nombre}', 
                '{$unidad}', 
                '{$clasificacion}', 
                '{$imagen}', 
                {$disponible}, 
                {$stockMin}, 
                {$stockMax}, 
                '{$marca}', 
                '{$modelo}', 
                '{$tipo}'
            )\"
            class='text-green-600 hover:text-green-800 mt-3'>
            <i class='fas fa-plus-circle text-xl'></i>
        </button>
    </div>";
}

// Paginación
if ($total_paginas > 1) {
    echo '<div class="flex justify-center mt-2">';
    for ($i = 1; $i <= $total_paginas; $i++) {
        $activo = $i == $pagina ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800';
        echo "<button type='button' onclick='cargarRepuestosServicio($i)' class='px-2 py-1 mx-1 rounded text-sm $activo'>$i</button>";
    }
    echo '</div>';
}
