<div class="border border-gray-300 rounded-lg shadow-md p-4">
<!-- Encabezado Profesional para el Panel del Gerente -->
<div class="relative overflow-hidden rounded-3xl shadow-2xl border border-yellow-500 bg-gradient-to-br from-gray-900 via-gray-800 to-yellow-900 p-6 animate-fade-in-up">
    <!-- Efectos de iluminación -->
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-yellow-400 rounded-full opacity-30 blur-2xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-amber-500 rounded-full opacity-20 blur-3xl animate-pulse"></div>

    <div class="flex flex-col md:flex-row items-center justify-center gap-6 z-10 ">
        <!-- Icono central -->
        <div class="flex items-center justify-center bg-yellow-500 text-white rounded-full w-20 h-20 shadow-lg border-4 border-yellow-300">
            <i class="fas fa-user-tie text-4xl"></i>
        </div>

        <!-- Texto central -->
        <div class="text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-wider flex items-center justify-center gap-4">
                <i class="fas fa-crown text-yellow-400 drop-shadow-md"></i>
                Panel del <span class="text-yellow-400 drop-shadow-md">Gerente</span>
                <i class="fas fa-crown text-yellow-400 drop-shadow-md"></i>
            </h1>

            <p class="text-gray-200 mt-2 text-sm md:text-base font-medium">
                Supervisión ejecutiva. Aprueba decisiones clave sin modificar. Visión total.
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
  <!-- Solicitudes en Espera -->
  <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-300 rounded-3xl shadow-2xl p-6 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
    <div class="absolute top-4 right-4 bg-yellow-500 text-white rounded-full px-3 py-1 text-sm font-bold shadow-lg">
      EN ESPERA
    </div>

    <div class="flex items-center mb-4">
      <div class="bg-yellow-400 p-3 rounded-xl text-white shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
        </svg>
      </div>
      <h3 class="ml-4 text-2xl font-bold text-yellow-800">Solicitudes en Espera</h3>
    </div>

    <p class="text-yellow-700 mb-6 leading-relaxed">
      Todas las solicitudes pendientes de revisión o acción administrativa. Monitorea el flujo en tiempo real.
    </p>

    <a href="solicitudes_en_espera.php" class="inline-flex items-center font-semibold text-yellow-800 hover:underline">
      Ver detalles
      <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
    </a>
  </div>

  <!-- Historial de Gastos -->
  <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-300 rounded-3xl shadow-2xl p-6 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
    <div class="absolute top-4 right-4 bg-green-600 text-white rounded-full px-3 py-1 text-sm font-bold shadow-lg">
      FINANZAS
    </div>

    <div class="flex items-center mb-4">
      <div class="bg-green-500 p-3 rounded-xl text-white shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.567-3 3.5S10.343 15 12 15s3-1.567 3-3.5S13.657 8 12 8zM12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M6.05 17.95l-1.414-1.414m12.728 1.414l-1.414-1.414M6.05 6.05L4.636 7.464" />
        </svg>
      </div>
      <h3 class="ml-4 text-2xl font-bold text-green-800">Historial de Gastos</h3>
    </div>

    <p class="text-green-700 mb-6 leading-relaxed">
      Visualiza todos los movimientos financieros relacionados a compras, mantenimientos y solicitudes aprobadas.
    </p>

    <a href="historial_gastos.php" class="inline-flex items-center font-semibold text-green-800 hover:underline">
      Ver historial
      <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
      </svg>
    </a>
  </div>
</div>

<!-- Indicadores dinámicos -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
    
<?php
$mes_actual = date('Y-m');
$total_solicitudes_mes = 0;
$completadas_solicitudes_mes = 0;
$ejecucion_solicitudes_mes = 0;
$pendientes_solicitudes_mes = 0;
$preventivos_solicitudes_mes = 0;
$correctivos_solicitudes_mes = 0;
$porcentaje_solicitudes_completadas = 0;

if ($conexion) {
    $sql_mes = "
        SELECT id_tipo_solicitud, id_status, COUNT(*) as total 
        FROM solicitudes 
        WHERE DATE_FORMAT(fecha_solicitud, '%Y-%m') = '$mes_actual'
        GROUP BY id_tipo_solicitud, id_status
    ";
    $res_mes = mysqli_query($conexion, $sql_mes);
    if ($res_mes) {
        while ($row = mysqli_fetch_assoc($res_mes)) {
            $tipo = $row['id_tipo_solicitud'];
            $status = $row['id_status'];
            $cantidad = $row['total'];

            $total_solicitudes_mes += $cantidad;

            if ($status == 4) $completadas_solicitudes_mes += $cantidad;
            if ($status == 2) $ejecucion_solicitudes_mes += $cantidad;
            if ($status == 1) $pendientes_solicitudes_mes += $cantidad;

            if ($tipo == 1) $preventivos_solicitudes_mes += $cantidad;
            if ($tipo == 2) $correctivos_solicitudes_mes += $cantidad;
        }
    }

    if ($total_solicitudes_mes > 0) {
        $porcentaje_solicitudes_completadas = round(($completadas_solicitudes_mes / $total_solicitudes_mes) * 100);
    }
}
?>

<div class="bg-gradient-to-r from-indigo-900 to-purple-600 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition-all">
    <div class="flex items-center mb-6">
        <i class="fa fa-clipboard-list text-4xl text-white mr-4"></i>
        <h2 class="font-bold text-2xl tracking-wide">Resumen de Solicitudes del Mes</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-lg text-center">
        <div class="bg-indigo-700 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Total del mes</p>
            <span class="font-semibold text-white text-xl"><?= $total_solicitudes_mes ?></span>
        </div>
        <div class="bg-green-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Mantenimiento</p>
            <span class="font-semibold text-white text-xl"><?= $preventivos_solicitudes_mes ?></span>
        </div>
        <div class="bg-red-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Compras</p>
            <span class="font-semibold text-white text-xl"><?= $correctivos_solicitudes_mes ?></span>
        </div>
        <div class="bg-green-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Aprovadas</p>
            <span class="font-semibold text-white text-xl"><?= $completadas_solicitudes_mes ?></span>
        </div>
        <div class="bg-yellow-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Rechazadas</p>
            <span class="font-semibold text-white text-xl"><?= $ejecucion_solicitudes_mes ?></span>
        </div>
        <div class="bg-yellow-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Pendientes</p>
            <span class="font-semibold text-white text-xl"><?= $pendientes_solicitudes_mes ?></span>
        </div>
    </div>

    <div class="flex justify-center items-center mt-6">
        <div class="relative w-32 h-32">
            <svg class="absolute w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-300 stroke-current" stroke-width="4" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-green-400 stroke-current" stroke-width="4" fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="<?= $porcentaje_solicitudes_completadas ?>, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center font-bold">
                <?= $porcentaje_solicitudes_completadas ?>%
            </div>
        </div>
    </div>

    <div class="flex justify-center mt-6">
        <button onclick="window.location.href='reporte_solicitudes.php'" 
            class="bg-white text-indigo-700 px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-gray-100 transition-all">
            Ver detalles
        </button>
    </div>
</div>
<?php
$anio_actual = date('Y');
$total_solicitudes_anuales = 0;
$preventivos_solicitudes_anuales = 0;
$correctivos_solicitudes_anuales = 0;
$terminadas_solicitudes_anuales = 0;
$pendientes_solicitudes_anuales = 0;

if ($conexion) {
    $sql_anual = "
        SELECT id_tipo_solicitud, id_status, COUNT(*) as total
        FROM solicitudes
        WHERE YEAR(fecha_solicitud) = '$anio_actual'
        GROUP BY id_tipo_solicitud, id_status
    ";
    $res_anual = mysqli_query($conexion, $sql_anual);
    if ($res_anual) {
        while ($row = mysqli_fetch_assoc($res_anual)) {
            $tipo = $row['id_tipo_solicitud'];
            $status = $row['id_status'];
            $cantidad = $row['total'];

            $total_solicitudes_anuales += $cantidad;

            if ($tipo == 1) $preventivos_solicitudes_anuales += $cantidad;
            if ($tipo == 2) $correctivos_solicitudes_anuales += $cantidad;

            if ($status == 4) $terminadas_solicitudes_anuales += $cantidad;
            if ($status == 1) $pendientes_solicitudes_anuales += $cantidad;
        }
    }
}

$porcentaje_solicitudes_terminadas = $total_solicitudes_anuales > 0 ? round(($terminadas_solicitudes_anuales / $total_solicitudes_anuales) * 100) : 0;
?>

<div class="bg-gradient-to-r from-indigo-900 to-purple-600 text-white p-8 rounded-lg shadow-lg transform hover:scale-105 transition-all">
    <div class="flex items-center mb-6">
        <i class="fa fa-calendar-check text-4xl text-white mr-4"></i>
        <h2 class="font-bold text-2xl tracking-wide">Resumen Anual de Solicitudes (<?= $anio_actual ?>)</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 text-lg text-center">
        <div class="bg-indigo-700 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Total anuales</p>
            <span class="font-semibold text-white text-xl"><?= $total_solicitudes_anuales ?></span>
        </div>
        <div class="bg-green-600 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Mantenimiento</p>
            <span class="font-semibold text-white text-xl"><?= $preventivos_solicitudes_anuales ?></span>
        </div>
        <div class="bg-red-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Compras</p>
            <span class="font-semibold text-white text-xl"><?= $correctivos_solicitudes_anuales ?></span>
        </div>
        <div class="bg-green-400 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Aprovadas</p>
            <span class="font-semibold text-white text-xl"><?= $terminadas_solicitudes_anuales ?></span>
        </div>
        <div class="bg-yellow-500 p-4 rounded-lg shadow-md">
            <p class="text-sm font-medium">Pendientes</p>
            <span class="font-semibold text-white text-xl"><?= $pendientes_solicitudes_anuales ?></span>
        </div>
    </div>

    <div class="flex justify-center items-center mt-6">
        <div class="relative w-32 h-32">
            <svg class="absolute w-full h-full" viewBox="0 0 36 36">
                <path class="text-gray-300 stroke-current" stroke-width="4" fill="none"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="text-green-400 stroke-current" stroke-width="4" fill="none"
                    stroke-linecap="round"
                    stroke-dasharray="<?= $porcentaje_solicitudes_terminadas ?>, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center font-bold text-lg">
                <?= $porcentaje_solicitudes_terminadas ?>%
            </div>
        </div>
    </div>

    <div class="text-center mt-6">
        <button onclick="window.location.href='reporte_solicitudes_anual.php'" 
            class="bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:bg-purple-800 transition-all">
            Ver detalles anuales
        </button>
    </div>
</div>



</div>