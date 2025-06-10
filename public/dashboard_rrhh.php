<?php
// Conexión
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta individual por cargo
$sql = "
    SELECT 
        c.id_cargo,
        c.nombre_cargo,
        COUNT(p.id_persona) AS total_personas,
        SUM(CASE WHEN p.id_status = 1 THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN p.id_status != 1 THEN 1 ELSE 0 END) AS inactivas
    FROM cargo c
    LEFT JOIN personas p ON c.id_cargo = p.id_cargo
    GROUP BY c.id_cargo, c.nombre_cargo
    ORDER BY c.nombre_cargo
";

$result = $conexion->query($sql);

// Consulta global
$sql_global = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN id_status = 1 THEN 1 ELSE 0 END) AS activas,
        SUM(CASE WHEN id_status != 1 THEN 1 ELSE 0 END) AS inactivas
    FROM personas
";
$res_global = $conexion->query($sql_global)->fetch_assoc();
?>
<div class="border border-gray-300 rounded-lg shadow-md p-4">
    <!-- Encabezado para el Panel de Recursos Humanos -->
<div class="relative overflow-hidden rounded-3xl shadow-2xl border border-purple-700 bg-gradient-to-br from-gray-900 via-gray-800 to-purple-900 p-6 animate-fade-in-up">
    <!-- Efectos decorativos -->
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-purple-500 rounded-full opacity-30 blur-2xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-600 rounded-full opacity-20 blur-3xl animate-pulse"></div>

    <!-- Contenido principal -->
    <div class="flex flex-col md:flex-row items-center justify-center gap-6 z-10 ">
        <!-- Icono representativo -->
        <div class="flex items-center justify-center bg-purple-700 text-white rounded-full w-20 h-20 shadow-lg border-4 border-purple-300">
            <i class="fas fa-users-cog text-4xl"></i>
        </div>

        <!-- Título y subtítulo -->
        <div class="text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-wider">
                 Panel de <span class="text-purple-400 drop-shadow-md">Recursos Humanos</span>
            </h1>
            <p class="text-gray-300 mt-2 text-sm md:text-base font-medium">
                Supervisión de personal, control de asistencia, gestión de cargos y desarrollo humano.
            </p>
        </div>
    </div>
</div>
<hr class="my-6 border-gray-100">
<!-- 🔷 Resumen global -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <!-- Total Empleados -->
    <div class="bg-white rounded-2xl p-6 shadow-xl border border-blue-100 hover:shadow-2xl transition duration-300 group">
        <div class="flex items-center gap-5">
            <div class="bg-blue-50 text-blue-600 p-4 rounded-full group-hover:scale-110 transform transition">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-1">Total de Empleados</h3>
                <p class="text-gray-500 text-sm"><?= $res_global['total'] ?> personas</p>
            </div>
        </div>
    </div>

    <!-- Empleados Activos -->
    <div class="bg-white rounded-2xl p-6 shadow-xl border border-green-100 hover:shadow-2xl transition duration-300 group">
        <div class="flex items-center gap-5">
            <div class="bg-green-50 text-green-600 p-4 rounded-full group-hover:scale-110 transform transition">
                <i class="fas fa-user-check text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-1">Activos</h3>
                <p class="text-gray-500 text-sm"><?= $res_global['activas'] ?> empleados</p>
            </div>
        </div>
    </div>

    <!-- Empleados Inactivos -->
    <div class="bg-white rounded-2xl p-6 shadow-xl border border-red-100 hover:shadow-2xl transition duration-300 group">
        <div class="flex items-center gap-5">
            <div class="bg-red-50 text-red-600 p-4 rounded-full group-hover:scale-110 transform transition">
                <i class="fas fa-user-times text-2xl"></i>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-1">Inactivos</h3>
                <p class="text-gray-500 text-sm"><?= $res_global['inactivas'] ?> empleados</p>
            </div>
        </div>
    </div>
</div>

<!-- 🧩 Cartas de Cargos - Versión Premium -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-md hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
            <!-- Encabezado con ícono -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full group-hover:rotate-6 transition-transform">
                        <i class="fas fa-briefcase text-2xl"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 group-hover:text-indigo-700 transition">
                        <?= htmlspecialchars($row['nombre_cargo']) ?>
                    </h2>
                </div>
            </div>

            <!-- Contenido del cargo -->
            <div class="space-y-2 mt-2 text-sm text-gray-600">
                <div class="flex justify-between items-center">
                    <span class="font-medium">Total</span>
                    <span class="text-gray-800 font-semibold"><?= $row['total_personas'] ?> personas</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-green-600">Activos</span>
                    <span class="text-green-700 font-semibold"><?= $row['activas'] ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium text-red-500">Inactivos</span>
                    <span class="text-red-600 font-semibold"><?= $row['inactivas'] ?></span>
                </div>
            </div>

            <!-- Línea inferior animada -->
            <div class="mt-5 h-1 w-full bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-full opacity-30 group-hover:opacity-80 transition-all duration-300"></div>
        </div>
    <?php endwhile; ?>
</div>


