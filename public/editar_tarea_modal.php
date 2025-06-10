<?php
include 'db_connection.php';

$id_tarea = $_GET['id'] ?? null;
$tarea = [];

if ($id_tarea) {
    $stmt = $conn->prepare("SELECT * FROM tareas WHERE id_tarea = ?");
    $stmt->execute([$id_tarea]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form id="formTarea" method="POST" action="actualizar_tarea.php">
    <input type="hidden" name="id_tarea" value="<?= $tarea['id_tarea'] ?>">

    <!-- TIPO MANTENIMIENTO -->
    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white mb-4">
        <label class="absolute -top-3 left-4 bg-white px-2 text-blue-600 text-sm font-semibold">Tipo de Mantenimiento</label>
        <select id="tipo_mantenimiento" name="tipo_mantenimiento" class="w-full border border-blue-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
            <option value="" disabled>Seleccione el tipo de mantenimiento</option>
            <?php
            $stmt = $conn->query("SELECT id_tipo, nombre_tipo FROM tipo_mantenimiento");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = $row['id_tipo'] == $tarea['tipo_mantenimiento_id'] ? 'selected' : '';
                echo "<option value='{$row['id_tipo']}' $selected>{$row['nombre_tipo']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- IMPORTANCIA -->
    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white mb-4">
        <label class="absolute -top-3 left-4 bg-white px-2 text-blue-600 text-sm font-semibold">Nivel de importancia</label>
        <select id="id_importancia" name="id_importancia" class="w-full border border-blue-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
            <option value="" disabled>Seleccione el nivel de importancia</option>
            <?php
            $stmt = $conn->query("SELECT id_importancia, nivel FROM prioridad");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = $row['id_importancia'] == $tarea['id_importancia'] ? 'selected' : '';
                echo "<option value='{$row['id_importancia']}' $selected>{$row['nivel']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- TITULO -->
    <div class="relative w-full mt-4">
        <label for="titulo_mantenimiento" class="block text-lg font-semibold text-gray-700">
            <i class="fas fa-wrench text-blue-600 mr-2"></i> Título del Mantenimiento
        </label>
        <input type="text" id="titulo_mantenimiento" name="titulo_mantenimiento" value="<?= htmlspecialchars($tarea['titulo_tarea']) ?>"
            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 shadow-sm"
            placeholder="Escribe el título del mantenimiento aquí...">
    </div>

    <!-- DESCRIPCIÓN -->
    <div id="contenedorDescripcionMantenimiento" class="mb-6 mt-4">
        <label for="descripcion_tarea" class="block text-lg font-semibold flex items-center">
            <i class="fas fa-tools text-blue-600 mr-2"></i> Descripción del Mantenimiento:
        </label>
        <textarea id="descripcion_tarea" name="descripcion_tarea" rows="5"
            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 shadow-sm"
            placeholder="Describe el mantenimiento aquí..."><?= htmlspecialchars($tarea['descripcion_tarea'] ?? '') ?></textarea>
    </div>

    <!-- TIEMPOS -->
    <div class="grid grid-cols-2 gap-6 mt-3">
        <div class="relative w-full">
            <label class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">Tiempo Programado</label>
            <input type="number" id="horas_mantenimiento" name="horas_mantenimiento" min="0" max="9999"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
                value="<?= intval($tarea['tiempo_programado']) ?>">
            <span class="absolute right-3 top-3 text-gray-600 text-sm">Horas</span>
        </div>
        <div class="relative w-full">
            <label class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">Paro Máquina</label>
            <input type="number" id="horas_parada" name="horas_parada" min="0" max="9999"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-red-500"
                value="<?= intval($tarea['tiempo_paro_maquina']) ?>">
            <span class="absolute right-3 top-3 text-gray-600 text-sm">Horas</span>
        </div>
    </div>

    <!-- PLANIFICACIÓN -->
    <div class="grid grid-cols-2 gap-4 mt-6">
        <div>
            <label class="block text-md font-medium text-gray-700">Fecha de inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" class="w-full border border-gray-300 rounded-md p-3" value="<?= $tarea['fecha_inicio'] ?>">
        </div>
        <div>
            <label class="block text-md font-medium text-gray-700">Hora de inicio:</label>
            <input type="time" id="hora_inicio" name="hora_inicio" class="w-full border border-gray-300 rounded-md p-3" value="<?= $tarea['hora_inicio'] ?>">
        </div>
        <div>
            <label class="block text-md font-medium text-gray-700">Fecha de fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" class="w-full border border-gray-300 rounded-md p-3" value="<?= $tarea['fecha_fin'] ?>">
        </div>
        <div>
            <label class="block text-md font-medium text-gray-700">Hora de fin:</label>
            <input type="time" id="hora_fin" name="hora_fin" class="w-full border border-gray-300 rounded-md p-3" value="<?= $tarea['hora_fin'] ?>">
        </div>
    </div>
    <!-- BOTÓN CERRAR MODAL -->
    <div class="mt-4 text-left">
        <button type="button" onclick="window.parent.postMessage('cerrarModal', '*');" class="bg-gray-400 text-white font-semibold px-6 py-2 rounded-lg hover:bg-gray-500 shadow">
            Cerrar
        </button>
    </div>
    <!-- SUBMIT -->
    <div class="mt-6 text-right">
        <button type="submit" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 shadow">Actualizar Tarea</button>
    </div>
</form>
