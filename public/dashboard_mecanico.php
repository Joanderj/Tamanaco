<div class="border border-gray-300 rounded-lg shadow-md p-4">
<!-- Encabezado Profesional para el Panel del Mecánico -->
<div class="relative overflow-hidden rounded-3xl shadow-2xl border border-blue-700 bg-gradient-to-br from-gray-900 via-gray-800 to-blue-900 p-6 animate-fade-in-up">
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-blue-500 rounded-full opacity-30 blur-2xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-indigo-600 rounded-full opacity-20 blur-3xl animate-pulse"></div>

    <div class="flex flex-col md:flex-row items-center justify-center gap-6 z-10 ">
        <!-- Icono central -->
        <div class="flex items-center justify-center bg-blue-700 text-white rounded-full w-20 h-20 shadow-lg border-4 border-blue-300">
            <i class="fas fa-tools text-4xl"></i>
        </div>

        <!-- Texto central -->
        <div class="text-center">
           <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-wider flex items-center justify-center gap-4">
    <i class="fas fa-toolbox  drop-shadow-md"></i>
    Panel de Control del <span class="text-blue-400 drop-shadow-md">Mecánico</span>
    <i class="fas fa-toolbox  drop-shadow-md"></i>
</h1>

            <p class="text-gray-300 mt-2 text-sm md:text-base font-medium">
                Visualiza, gestiona y controla cada tarea con precisión. Optimizado para el mantenimiento inteligente.
            </p>
        </div>
    </div>
</div>

    <!-- Contenido principal -->
    <div class="grid grid-cols-3 gap-6 mt-8">
        <?php
include 'db_connection.php';

try {
    $stmt_operativas = $conn->prepare("SELECT COUNT(*) as total FROM maquina_unica WHERE id_status = 1");
    $stmt_operativas->execute();
    $operativas = $stmt_operativas->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt_mantenimiento = $conn->prepare("SELECT COUNT(*) as total FROM maquina_unica WHERE id_status = 13");
    $stmt_mantenimiento->execute();
    $en_mantenimiento = $stmt_mantenimiento->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    $operativas = $en_mantenimiento = 0;
}
?>
<!-- Estado de Máquinas - Panel Mejorado -->
<div class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-700 text-white p-6 rounded-3xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
    
    <!-- Glow decorativo -->
    <div class="absolute -top-9 -left-9 w-32 h-32 bg-yellow-400 opacity-20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-orange-300 opacity-10 rounded-full blur-3xl animate-pulse"></div>

    <!-- Encabezado -->
    <div class="flex items-center gap-4 mb-4">
        <div class="flex justify-center items-center bg-white text-yellow-700 rounded-full w-14 h-14 shadow-md border-2 border-yellow-200">
            <i class="fas fa-cogs text-2xl animate-spin-slow"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold tracking-wide">Estados de las Máquinas</h2>
            <p class="text-sm text-yellow-100 italic">Supervisión en tiempo real</p>
        </div>
    </div>

    <!-- Contenido de estado -->
    <div class="grid grid-cols-2 gap-6 mt-4 text-center">
        <div class="bg-yellow-100 text-green-700 rounded-xl p-4 shadow-inner">
            <i class="fas fa-check-circle text-2xl mb-2"></i>
            <p class="text-sm font-medium">Operativas</p>
            <p class="text-2xl font-bold"><?= $operativas ?></p>
        </div>
        <div class="bg-yellow-100 text-red-600 rounded-xl p-4 shadow-inner">
            <i class="fas fa-tools text-2xl mb-2"></i>
            <p class="text-sm font-medium">Paradas</p>
            <p class="text-2xl font-bold"><?= $en_mantenimiento ?></p>
        </div>
    </div>

    <!-- Botón de acción -->
    <div class="mt-6 text-center">
        <button onclick="window.location.href='estado_maquina.php'"
            class="bg-white text-yellow-800 px-5 py-2 rounded-lg font-semibold shadow-md hover:bg-gray-100 transition-all">
            Ver detalles
        </button>
    </div>
</div>

<!-- Spin lento personalizado -->
<style>
    .animate-spin-slow {
        animation: spin 4s linear infinite;
    }
</style>


     <?php
include 'db_connection.php';
date_default_timezone_set('America/Caracas');

// Calcular el inicio (lunes) y fin (domingo) de la semana actual
$hoy = new DateTime();
$inicio_semana = $hoy->modify('monday this week')->format('Y-m-d');
$fin_semana = (new DateTime($inicio_semana))->modify('sunday this week')->format('Y-m-d');

$pendientes = 0;
$ejecucion = 0;
$terminados = 0;
$tareas_semana = [];

if ($conexion) {
    // Contar tareas por estado durante la semana
    $sql_contador = "
        SELECT status_id, COUNT(*) as total 
        FROM tareas 
        WHERE fecha_inicio BETWEEN '$inicio_semana' AND '$fin_semana'
        GROUP BY status_id
    ";
    $res_contador = mysqli_query($conexion, $sql_contador);
    if ($res_contador) {
        while ($fila = mysqli_fetch_assoc($res_contador)) {
            switch ($fila['status_id']) {
                case 1: $pendientes = $fila['total']; break;
                case 5: $ejecucion = $fila['total']; break;
                case 7: $terminados = $fila['total']; break;
            }
        }
    }

    // Obtener todas las tareas de la semana
    $sql_tareas = "
        SELECT * FROM tareas 
        WHERE fecha_inicio BETWEEN '$inicio_semana' AND '$fin_semana'
        ORDER BY fecha_inicio ASC
    ";
    $res_tareas = mysqli_query($conexion, $sql_tareas);
    if ($res_tareas) {
        while ($tarea = mysqli_fetch_assoc($res_tareas)) {
            $tareas_semana[] = $tarea;
        }
    }
} else {
    echo "<p class='text-red-600 font-bold'>No se pudo conectar a la base de datos.</p>";
}
?>
<!-- Órdenes de Mantenimiento - Panel Profesional -->
<div class="relative bg-gradient-to-br from-green-600 via-green-500 to-green-700 text-white p-6 rounded-3xl shadow-xl transform transition-transform hover:scale-105 hover:shadow-2xl overflow-hidden">

    <!-- Brillo decorativo -->
    <div class="absolute -top-9 -left-9 w-32 h-32 bg-green-400 opacity-20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-lime-300 opacity-10 rounded-full blur-3xl animate-pulse"></div>

    <!-- Encabezado -->
    <div class="flex items-center gap-4 mb-5">
        <div class="flex justify-center items-center bg-white text-green-700 rounded-full w-14 h-14 shadow-md border-2 border-green-300">
            <i class="fas fa-tools text-2xl animate-wiggle-slow"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold tracking-wide">Tareas de la Semana</h2>
            <p class="text-sm text-green-100 italic">Supervisión de operaciones actuales</p>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-3 gap-4 text-center text-sm mt-2">
        <div class="bg-green-100 text-yellow-700 rounded-xl p-3 shadow-inner">
            <i class="fas fa-spinner text-lg mb-1"></i>
            <p class="font-medium">En ejecución</p>
            <p class="text-2xl font-extrabold"><?= $ejecucion ?></p>
        </div>
        <div class="bg-green-100 text-red-700 rounded-xl p-3 shadow-inner">
            <i class="fas fa-check-circle text-lg mb-1"></i>
            <p class="font-medium">Terminados</p>
            <p class="text-2xl font-extrabold"><?= $terminados ?></p>
        </div>
        <div class="bg-green-100 text-yellow-700 rounded-xl p-3 shadow-inner">
            <i class="fas fa-clock text-lg mb-1"></i>
            <p class="font-medium">Pendientes</p>
            <p class="text-2xl font-extrabold"><?= $pendientes ?></p>
        </div>
    </div>

    <!-- Botón -->
    <div class="mt-6 text-center">
        <button onclick="window.location.href='mantenimiento_hoy.php'"
            class="bg-white text-green-800 px-5 py-2 rounded-lg font-semibold shadow-md hover:bg-gray-100 transition-all">
            Ver mantenimiento
        </button>
    </div>
</div>

<!-- Wiggle lento personalizado -->
<style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(-5deg); }
        50% { transform: rotate(5deg); }
    }

    .animate-wiggle-slow {
        animation: wiggle 3s ease-in-out infinite;
    }
</style>




      <!-- Calendario de Mantenimiento - Diseño Premium -->
<div class="relative bg-gradient-to-br from-indigo-600 via-indigo-500 to-indigo-700 text-white p-6 rounded-3xl shadow-xl transform transition-transform hover:scale-105 hover:shadow-2xl overflow-hidden">

    <!-- Brillos decorativos -->
    <div class="absolute -top-10 left-1/2 w-32 h-32 bg-indigo-400 opacity-20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-10 right-10 w-28 h-28 bg-purple-300 opacity-10 rounded-full blur-2xl animate-pulse"></div>

    <!-- Encabezado -->
    <div class="flex items-center gap-4 mb-5">
        <div class="flex justify-center items-center bg-white text-indigo-700 rounded-full w-14 h-14 shadow-md border-2 border-indigo-300">
            <i class="fas fa-calendar-alt text-2xl animate-pulse-fast"></i>
        </div>
        <div>
            <h2 class="text-2xl font-bold tracking-wide">Calendario de Mantenimiento</h2>
            <p class="text-sm text-indigo-100 italic">Próximas tareas programadas</p>
        </div>
    </div>

    <!-- Descripción -->
    <div class="mt-2 text-sm text-indigo-100">
        Consulta fácilmente los mantenimientos agendados y planifica tus recursos con antelación.
    </div>

    <!-- Botón -->
    <div class="mt-6 text-center">
        <button onclick="window.location.href='calendario.php'"
            class="bg-white text-indigo-800 px-5 py-2 rounded-lg font-semibold shadow-md hover:bg-gray-100 transition-all">
            Ver calendario
        </button>
    </div>
</div>

<!-- Animación personalizada -->
<style>
    .animate-pulse-fast {
        animation: pulse 1.2s infinite;
    }
</style>


    </div>
    
<!-- Indicadores dinámicos -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

<?php
$mes_actual = date('Y-m');
$total_mes = 0;
$completadas_mes = 0;
$ejecucion_mes = 0;
$pendientes_mes = 0;
$preventivos_mes = 0;
$correctivos_mes = 0;
$porcentaje_completado = 0;

if ($conexion) {
    $sql_mes = "
        SELECT tipo_mantenimiento_id, status_id, COUNT(*) as total 
        FROM tareas 
        WHERE DATE_FORMAT(fecha_inicio, '%Y-%m') = '$mes_actual'
        GROUP BY tipo_mantenimiento_id, status_id
    ";
    $res_mes = mysqli_query($conexion, $sql_mes);
    if ($res_mes) {
        while ($row = mysqli_fetch_assoc($res_mes)) {
            $tipo = $row['tipo_mantenimiento_id'];
            $status = $row['status_id'];
            $cantidad = $row['total'];

            $total_mes += $cantidad;

            if ($status == 7) $completadas_mes += $cantidad;
            if ($status == 5) $ejecucion_mes += $cantidad;
            if ($status == 1) $pendientes_mes += $cantidad;

            if ($tipo == 1) $preventivos_mes += $cantidad;
            if ($tipo == 2) $correctivos_mes += $cantidad;
        }
    }

    if ($total_mes > 0) {
        $porcentaje_completado = round(($completadas_mes / $total_mes) * 100);
    }
}
?>

<div class="bg-gradient-to-r from-gray-900 to-blue-600 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition-all">
    <div class="flex items-center mb-6">
        <i class="fa fa-calendar-alt text-4xl text-white mr-4"></i>
        <h2 class="font-bold text-2xl tracking-wide">Resumen de Tareas del Mes</h2>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-lg text-center">
        <div class="bg-blue-700 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Total del mes</p>
            <span class="font-semibold text-white text-xl"><?= $total_mes ?></span>
        </div>
        <div class="bg-green-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Preventivos</p>
            <span class="font-semibold text-white text-xl"><?= $preventivos_mes ?></span>
        </div>
        <div class="bg-red-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Correctivos</p>
            <span class="font-semibold text-white text-xl"><?= $correctivos_mes ?></span>
        </div>
        <div class="bg-green-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Completadas</p>
            <span class="font-semibold text-white text-xl"><?= $completadas_mes ?></span>
        </div>
        <div class="bg-yellow-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">En ejecución</p>
            <span class="font-semibold text-white text-xl"><?= $ejecucion_mes ?></span>
        </div>
        <div class="bg-yellow-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Pendientes</p>
            <span class="font-semibold text-white text-xl"><?= $pendientes_mes ?></span>
        </div>
    </div>

    <!-- Gráfico circular de progreso -->
    <div class="flex justify-center items-center mt-6">
        <div class="relative w-32 h-32">
            <svg class="absolute w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-300 stroke-current" stroke-width="4" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-green-400 stroke-current" stroke-width="4" fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="<?= $porcentaje_completado ?>, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center font-bold ">
                <?= $porcentaje_completado ?>%
            </div>
        </div>
    </div>

    <div class="flex justify-center mt-6">
        <button onclick="window.location.href='reporte_tareas.php'" 
            class="bg-white text-blue-700 px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-gray-100 transition-all">
            Ver detalles
        </button>
    </div>
</div>

<?php
$anio_actual = date('Y');
$total_anuales = 0;
$preventivos_anuales = 0;
$correctivos_anuales = 0;
$anuales_terminadas = 0;
$anuales_pendientes = 0;

// Asegúrate de tener conexión activa en $conexion
if ($conexion) {
    $sql_anual = "
        SELECT tipo_mantenimiento_id, status_id, COUNT(*) as total
        FROM tareas
        WHERE YEAR(fecha_inicio) = '$anio_actual'
        GROUP BY tipo_mantenimiento_id, status_id
    ";
    $res_anual = mysqli_query($conexion, $sql_anual);
    if ($res_anual) {
        while ($row = mysqli_fetch_assoc($res_anual)) {
            $tipo = $row['tipo_mantenimiento_id'];
            $status = $row['status_id'];
            $cantidad = $row['total'];

            $total_anuales += $cantidad;

            if ($tipo == 1) $preventivos_anuales += $cantidad;
            if ($tipo == 2) $correctivos_anuales += $cantidad;

            if ($status == 7) $anuales_terminadas += $cantidad;
            if ($status == 1) $anuales_pendientes += $cantidad;
        }
    }
}

// Cálculo de porcentaje con verificación para evitar división por cero
$porcentaje_terminadas = $total_anuales > 0 ? round(($anuales_terminadas / $total_anuales) * 100) : 0;
?>
<!-- Card visual para resumen anual -->
<div class="bg-gradient-to-r from-gray-900 to-blue-600 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition-all">
    <div class="flex items-center mb-6">
        <i class="fa fa-chart-line text-4xl text-white mr-4"></i>
        <h2 class="font-bold text-2xl tracking-wide">Resumen Anual de Tareas (<?= $anio_actual ?>)</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-lg text-center">
        <div class="bg-blue-700 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Total de tareas</p>
            <span class="font-semibold text-white text-xl"><?= $total_anuales ?></span>
        </div>
        <div class="bg-green-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Preventivos</p>
            <span class="font-semibold text-white text-xl"><?= $preventivos_anuales ?></span>
        </div>
        <div class="bg-red-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Correctivos</p>
            <span class="font-semibold text-white text-xl"><?= $correctivos_anuales ?></span>
        </div>
        <div class="bg-green-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Terminadas</p>
            <span class="font-semibold text-white text-xl"><?= $anuales_terminadas ?></span>
        </div>
        <div class="bg-yellow-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Pendientes</p>
            <span class="font-semibold text-white text-xl"><?= $anuales_pendientes ?></span>
        </div>
    </div>

    <!-- Gráfico circular -->
    <div class="flex justify-center items-center mt-6">
        <div class="relative w-32 h-32">
            <svg class="absolute w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-300 stroke-current" stroke-width="4" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-green-400 stroke-current" stroke-width="4" fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="<?= $porcentaje_terminadas ?>, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center font-bold text-lg">
                <?= $porcentaje_terminadas ?>%
            </div>
        </div>
    </div>

    <div class="text-center mt-6">
        <button onclick="window.location.href='reporte_anual.php'" 
            class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-blue-700 hover:scale-105 transition-all">
            Ver detalles anuales
        </button>
    </div>
</div>


</div>