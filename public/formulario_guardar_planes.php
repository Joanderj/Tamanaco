<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfaz de Usuario</title>
    <link href="css/tailwind.min.css" rel="stylesheet">
    <link href="lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/flatpickr.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="css/main.min.css">
    <script src="js/chart.js"></script>
</head>
<body>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.min.css" />
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
  
    <h1 class="text-2xl font-bold text-center text-black mb-4">
        <i class="fas fa-calendar-check text-black mr-2"></i> Crear un plan de mantenimiento
    </h1>
    <div class="flex justify-between items-center pb-4 mb-4">
        <button class="flex items-center px-4 py-2">
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm">
                <i class="fas fa-info-circle"></i>
            </span>
            <span class="ml-2 text-gray-700">Información</span>
        </button>
        <hr class="border-t border-gray-300 w-full">
        <button class="flex items-center px-4 py-2">
        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm">
                <i class="fas fa-calendar-alt"></i>
            </span>
            <span class="ml-2 text-gray-700">Planificación</span>
        </button>
        <hr class="border-t border-gray-300 w-full">
        <button class="flex items-center px-4 py-2">
        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-sm">
                <i class="fas fa-check-circle"></i>
            </span>
            <span class="ml-2 text-gray-700">Validación</span>
        </button>
    </div>
    <form id="eventForm" class="space-y-4">
        <!-- Evento Section -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-calendar-alt text-black mr-2"></i> Evento
            </h2>
            <div>
                <label for="eventTitle" class="block font-semibold">Título del Evento:</label>
                <input type="text" id="eventTitle" name="eventTitle" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Ingrese el título del evento">
            </div>
        </div>

        <!-- Trigger Section -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-bolt text-black mr-2"></i> Trigger
            </h2>
            <div>
                <input type="checkbox" id="fixedDate" name="fixedDate">
                <label for="fixedDate" class="font-semibold">Fecha Fija</label>
                <p class="text-gray-600">Las tareas se repiten en unas fechas fijas en el calendario.</p>
            </div>
            <div>
                <input type="checkbox" id="afterCompletion" name="afterCompletion">
                <label for="afterCompletion" class="font-semibold">Al Terminar</label>
                <p class="text-gray-600">Las siguientes tareas se crean tras terminar la tarea activa, utilizando el periodo definitivo.</p>
            </div>
        </div>
        <h2 class="text-xl font-semibold flex items-center">
            <i class="fas fa-calendar-day text-black mr-2"></i> Fecha de comienzo
        </h2>
        <div>
            <label for="startDate" class="block font-semibold">Fecha de Comienzo:</label>
            <input type="date" id="startDate" name="startDate" class="w-full border border-gray-300 rounded-lg p-2">
        </div>
        <!-- Repetir Section -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-sync-alt text-black mr-2"></i> Repetir
            </h2>
            <div>
                <label for="frequency" class="block font-semibold">Frecuencia:</label>
                <input type="number" id="frequency" name="frequency" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Ingrese la frecuencia">
            </div>
            <div class="flex space-x-4">
                <label class="button-repeat py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                    <input type="checkbox" id="daysButton" class="hidden" onchange="toggleExclusiveCheckbox(this, 'days'); setRepeatMode('days')"> Días
                </label>
                <label class="button-repeat py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                    <input type="checkbox" id="weeksButton" class="hidden" onchange="toggleExclusiveCheckbox(this, 'weeks'); setRepeatMode('weeks')"> Semanas
                </label>
                <label class="button-repeat py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                    <input type="checkbox" id="monthsButton" class="hidden" onchange="toggleExclusiveCheckbox(this, 'months'); setRepeatMode('months')"> Meses
                </label>

                <script>
                    function toggleExclusiveCheckbox(checkbox, mode) {
                        const checkboxes = ['daysButton', 'weeksButton', 'monthsButton'];
                        checkboxes.forEach(id => {
                            const otherCheckbox = document.getElementById(id);
                            if (otherCheckbox !== checkbox) {
                                otherCheckbox.checked = false;
                                toggleButtonStyle(otherCheckbox, 'bg-blue-300', 'bg-gray-500');
                            }
                        });
                        toggleButtonStyle(checkbox, 'bg-blue-300', 'bg-gray-500');
                    }
                </script>
            </div>
            <div id="daysOptions" class="hidden">
                <p class="text-gray-600">Seleccione los días de la semana:</p>
                <div class="flex space-x-2">
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Lunes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Martes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Miércoles
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Jueves
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Viernes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Sábado
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')"> Domingo
                    </label>
                </div>

                <script>
                    function toggleButtonStyle(checkbox, activeClass, inactiveClass) {
                        const label = checkbox.parentElement;
                        if (checkbox.checked) {
                            label.classList.remove(inactiveClass);
                            label.classList.add(activeClass);
                        } else {
                            label.classList.remove(activeClass);
                            label.classList.add(inactiveClass);
                        }
                    }
                </script>
            </div>
            <div id="monthsOptions" class="hidden">
                <div class="flex items-center space-x-2">
                    <p class="text-gray-600">Seleccione el día del mes:</p>
                    <input type="checkbox" id="dayOfMonthCheckbox" class="ml-2" checked onchange="toggleMonthOptions('dayOfMonth')">
                </div>
                <input type="number" id="dayOfMonth" name="dayOfMonth" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Día del mes">

                <div class="flex items-center space-x-2 mt-4">
                    <p class="text-gray-600">Seleccione la semana del mes:</p>
                    <input type="checkbox" id="weekOfMonthCheckbox" class="ml-2" onchange="toggleMonthOptions('weekOfMonth')">
                </div>
                <select id="weekOfMonth" name="weekOfMonth" class="w-full border border-gray-300 rounded-lg p-2" disabled>
                    <option value="1">Primera semana</option>
                    <option value="2">Segunda semana</option>
                    <option value="3">Tercera semana</option>
                    <option value="4">Cuarta semana</option>
                </select>
                <p class="text-gray-600 mt-2">Seleccione el día de la semana:</p>
                <div class="flex space-x-2">
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Lunes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Martes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Miércoles
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Jueves
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Viernes
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Sábado
                    </label>
                    <label class="button-week py-2 px-4 rounded-lg bg-gray-500 text-white cursor-pointer">
                        <input type="checkbox" class="hidden" onchange="toggleButtonStyle(this, 'bg-blue-500', 'bg-gray-500')" disabled> Domingo
                    </label>
                </div>
            </div>

            <script>
                function toggleMonthOptions(option) {
                    const dayOfMonthInput = document.getElementById('dayOfMonth');
                    const weekOfMonthSelect = document.getElementById('weekOfMonth');
                    const weekCheckboxes = document.querySelectorAll('.button-week input');

                    if (option === 'dayOfMonth') {
                        document.getElementById('dayOfMonthCheckbox').checked = true;
                        document.getElementById('weekOfMonthCheckbox').checked = false;
                        dayOfMonthInput.disabled = false;
                        weekOfMonthSelect.disabled = true;
                        weekCheckboxes.forEach(checkbox => checkbox.disabled = true);
                    } else if (option === 'weekOfMonth') {
                        document.getElementById('dayOfMonthCheckbox').checked = false;
                        document.getElementById('weekOfMonthCheckbox').checked = true;
                        dayOfMonthInput.disabled = true;
                        weekOfMonthSelect.disabled = false;
                        weekCheckboxes.forEach(checkbox => checkbox.disabled = false);
                    }
                }
            </script>
            </div>
        
    </form>
    <main class="bg-white shadow-lg p-6 rounded-lg w-full max-w-4xl relative overflow-hidden">
        <div class="mb-6">
            <p class="text-gray-700 font-medium text-center">Calendario de Planes</p>
        </div>
        <div id="calendar" class="p-4 bg-gray-50 border border-gray-200 rounded-lg overflow-auto max-h-[600px]"></div>
    </main>
    <script src="js/main.min.js"></script>
    <script>
        let calendar;
        let repeatMode = 'days';

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: []
            });
            calendar.render();

            document.getElementById('eventForm').addEventListener('input', updateCalendar);
            document.getElementById('frequency').addEventListener('input', updateCalendar);
            document.getElementById('dayOfMonth').addEventListener('input', updateCalendar);
            document.getElementById('lastDayOfMonth').addEventListener('change', updateCalendar);
            document.getElementById('weekOfMonth').addEventListener('change', updateCalendar);
        });

        function setRepeatMode(mode) {
            repeatMode = mode;
            document.getElementById('daysOptions').classList.toggle('hidden', mode !== 'weeks');
            document.getElementById('monthsOptions').classList.toggle('hidden', mode !== 'months');
            updateCalendar();
        }

        function toggleDay(button, day) {
            button.classList.toggle('button-selected');
            updateCalendar();
        }

        function updateCalendar() {
            const title = document.getElementById('eventTitle').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const frequency = parseInt(document.getElementById('frequency').value) || 0;

            if (title && startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);

                calendar.removeAllEvents();

                if (repeatMode === 'days' && frequency > 0) {
                    for (let date = new Date(start); date <= end; date.setDate(date.getDate() + frequency)) {
                        calendar.addEvent({
                            title: title,
                            start: date.toISOString().split('T')[0],
                            backgroundColor: '#3B82F6'
                        });
                    }
                }

                if (repeatMode === 'weeks' && frequency > 0) {
                    const selectedDays = Array.from(document.querySelectorAll('.button-selected')).map(btn => btn.textContent);
                    for (let date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                        if (selectedDays.includes(date.toLocaleString('es-ES', { weekday: 'long' }))) {
                            calendar.addEvent({
                                title: title,
                                start: date.toISOString().split('T')[0],
                                backgroundColor: '#3B82F6'
                            });
                        }
                    }
                }

                if (repeatMode === 'months') {
                    const dayOfMonth = parseInt(document.getElementById('dayOfMonth').value) || 0;
                    const lastDayOfMonth = parseInt(document.getElementById('lastDayOfMonth').value) || 0;
                    const weekOfMonth = parseInt(document.getElementById('weekOfMonth').value) || 0;
                    const selectedDays = Array.from(document.querySelectorAll('.button-selected')).map(btn => btn.textContent);

                    for (let date = new Date(start); date <= end; date.setMonth(date.getMonth() + 1)) {
                        if (dayOfMonth > 0) {
                            date.setDate(dayOfMonth);
                            calendar.addEvent({
                                title: title,
                                start: date.toISOString().split('T')[0],
                                backgroundColor: '#3B82F6'
                            });
                        } else if (lastDayOfMonth > 0) {
                            date.setDate(lastDayOfMonth);
                            calendar.addEvent({
                                title: title,
                                start: date.toISOString().split('T')[0],
                                backgroundColor: '#3B82F6'
                            });
                        } else if (weekOfMonth > 0 && selectedDays.length > 0) {
                            const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);
                            let weekCount = 0;
                            for (let d = new Date(firstDayOfMonth); d.getMonth() === date.getMonth(); d.setDate(d.getDate() + 1)) {
                                if (selectedDays.includes(d.toLocaleString('es-ES', { weekday: 'long' }))) {
                                    weekCount++;
                                    if (weekCount === weekOfMonth) {
                                        calendar.addEvent({
                                            title: title,
                                            start: d.toISOString().split('T')[0],
                                            backgroundColor: '#3B82F6'
                                        });
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    </script>
</div>
</body>
</html>