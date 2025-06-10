<?php
// buscar_repuestos.php
require_once 'db_connection.php';

$buscar = $_GET['buscar'] ?? '';
$pagina = (int)($_GET['pagina'] ?? 1);
$por_pagina = 5;
$inicio = ($pagina - 1) * $por_pagina;

$stmt = $conn->prepare("
    SELECT r.id_repuesto, r.nombre_repuesto, r.url,
           m.nombre_marca, mo.nombre_modelo, t.nombre_tipo,
           i.cantidad, i.stock_minimo, i.stock_maximo
    FROM repuesto r
    LEFT JOIN marca m ON r.id_marca = m.id_marca
    LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
    LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
    LEFT JOIN inventario_repuesto i ON r.id_repuesto = i.id_repuesto
    WHERE r.nombre_repuesto LIKE :buscar
    LIMIT $inicio, $por_pagina
");

$paramBuscar = "%$buscar%";
$stmt->bindParam(':buscar', $paramBuscar, PDO::PARAM_STR);
$stmt->execute();
$repuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
$total = $conn->prepare("SELECT COUNT(*) FROM repuesto WHERE nombre_repuesto LIKE :buscar");
$total->bindParam(':buscar', $paramBuscar, PDO::PARAM_STR);
$total->execute();
$total_paginas = ceil($total->fetchColumn() / $por_pagina);

echo '<div class="flex justify-center mt-2">';
for ($i = 1; $i <= $total_paginas; $i++) {
    $activo = $i == $pagina ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800';
    echo "<button type='button' onclick='cargarRepuestos($i)' class='px-2 py-1 mx-1 rounded text-sm $activo'>$i</button>";
}
echo '</div>';
