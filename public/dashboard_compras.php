<?php
// Conexión
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta de proveedores
$sql = "SELECT * FROM proveedor ORDER BY nombre_proveedor";
$result = $conexion->query($sql);
?>
<div class="border border-gray-300 rounded-lg shadow-md p-4">
    
    <!-- Encabezado Profesional para el Panel del Coordinador de Compras -->
<div class="relative overflow-hidden rounded-3xl shadow-2xl border border-green-600 bg-gradient-to-br from-gray-900 via-gray-800 to-green-900 p-6 animate-fade-in-up">
    <!-- Efectos visuales -->
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-green-500 rounded-full opacity-30 blur-2xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-emerald-600 rounded-full opacity-20 blur-3xl animate-pulse"></div>

    <!-- Contenido principal -->
    <div class="flex flex-col md:flex-row items-center justify-center gap-6 z-10 ">
        <!-- Icono representativo -->
        <div class="flex items-center justify-center bg-green-700 text-white rounded-full w-20 h-20 shadow-lg border-4 border-green-300">
            <i class="fas fa-shopping-cart text-4xl"></i>
        </div>

        <!-- Título y descripción -->
        <div class="text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-wider">
                 Panel del <span class="text-green-400 drop-shadow-md">Coordinador de Compras</span>
            </h1>
            <p class="text-gray-300 mt-2 text-sm md:text-base font-medium">
                Gestión estratégica de adquisiciones, control de proveedores y flujo eficiente de recursos.
            </p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
<div class="bg-gradient-to-br from-blue-50 to-white border border-blue-200 rounded-3xl shadow-md p-6 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
    <div class="flex items-center gap-4 mb-4">
        <div class="bg-blue-100 text-blue-600 p-4 rounded-full shadow-sm">
            <i class="fas fa-box-open text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Vincular Productos</h2>
            <p class="text-sm text-gray-600">Relaciona los productos que el proveedor ofrece.</p>
        </div>
    </div>

    <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-1 mb-4">
        <li>Asigna productos específicos</li>
        <li>Define precios de compra</li>
        <li>Organiza por categorías</li>
    </ul>

    <div class="mt-auto">
        <a href="vincular_proveedor_productos.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-xl text-sm transition">
            Vincular ahora <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>
<div class="bg-gradient-to-br from-green-50 to-white border border-green-200 rounded-3xl shadow-md p-6 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
    <div class="flex items-center gap-4 mb-4">
        <div class="bg-green-100 text-green-600 p-4 rounded-full shadow-sm">
            <i class="fas fa-cogs text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Vincular Repuestos</h2>
            <p class="text-sm text-gray-600">Asocia repuestos compatibles vendidos por el proveedor.</p>
        </div>
    </div>

    <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-1 mb-4">
        <li>Asigna Repuestos específicos</li>
        <li>Define precios de compra</li>
        <li>Organiza por categorías</li>
    </ul>

    <div class="mt-auto">
        <a href="vincular_proveedor_repuestos.php" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-xl text-sm transition">
            Vincular ahora <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>
<div class="bg-gradient-to-br from-purple-50 to-white border border-purple-200 rounded-3xl shadow-md p-6 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
    <div class="flex items-center gap-4 mb-4">
        <div class="bg-purple-100 text-purple-600 p-4 rounded-full shadow-sm">
            <i class="fas fa-tools text-2xl"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Vincular Herramientas</h2>
            <p class="text-sm text-gray-600">Relaciona herramientas suministradas por este proveedor.</p>
        </div>
    </div>

    <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside ml-1 mb-4">
        <li>Asigna Herramientas específicos</li>
        <li>Define precios de compra</li>
        <li>Organiza por categorías</li>
    </ul>

    <div class="mt-auto">
        <a href="vincular_proveedor_herramientas.php" class="inline-block bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-xl text-sm transition">
            Vincular ahora <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>

</div>
<!-- 🌐 Tarjetas de Proveedores Premium -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
    <?php while ($prov = $result->fetch_assoc()): ?>
        <div class="bg-white border border-gray-100 rounded-3xl shadow-lg p-6 relative overflow-hidden group transition-all duration-300 hover:shadow-2xl hover:-translate-y-1">
            
            <!-- Cinta de estado -->
            <div class="absolute top-4 right-4 text-xs px-3 py-1 rounded-full font-semibold tracking-wide
                <?= $prov['id_status'] == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                <?= $prov['id_status'] == 1 ? 'Activo' : 'Inactivo' ?>
            </div>

            <!-- Encabezado -->
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full group-hover:rotate-6 transition-transform duration-300">
                    <i class="fas fa-truck text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800 group-hover:text-yellow-600 transition-colors duration-300">
                    <?= htmlspecialchars($prov['nombre_proveedor']) ?>
                </h2>
            </div>

            <!-- Información de contacto -->
            <div class="space-y-2 text-sm text-gray-600">
                <p class="flex items-center gap-2">
                    <i class="fas fa-phone-alt text-indigo-500"></i>
                    <span><?= $prov['telefono'] ?></span>
                </p>
                <p class="flex items-center gap-2">
                    <i class="fas fa-envelope text-indigo-500"></i>
                    <span><?= $prov['email'] ?></span>
                </p>
                <p class="flex items-start gap-2">
                    <i class="fas fa-map-marker-alt text-indigo-500 mt-1"></i>
                    <span><?= $prov['direccion'] ?></span>
                </p>
            </div>

            <!-- Fechas -->
            <div class="mt-5 text-xs text-gray-400">
                <p><span class="font-medium">🗓️ Creado:</span> <?= date("d/m/Y", strtotime($prov['date_created'])) ?></p>
                <p><span class="font-medium">✏️ Actualizado:</span> <?= date("d/m/Y", strtotime($prov['date_updated'])) ?></p>
            </div>

            <!-- Decorativo: línea inferior animada -->
            <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-yellow-300 via-yellow-400 to-yellow-500 scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-500"></div>
        </div>
    <?php endwhile; ?>

    
</div>


