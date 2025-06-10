<div class="border border-gray-300 rounded-lg shadow-md p-4">
<!-- Encabezado Profesional para el Panel del Asistente -->
<div class="relative overflow-hidden rounded-3xl shadow-2xl border border-teal-600 bg-gradient-to-br from-gray-900 via-gray-800 to-teal-900 p-6 animate-fade-in-up">
    <div class="absolute -top-10 -left-10 w-32 h-32 bg-teal-400 rounded-full opacity-30 blur-2xl animate-pulse"></div>
    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-cyan-500 rounded-full opacity-20 blur-3xl animate-pulse"></div>

    <div class="flex flex-col md:flex-row items-center justify-center gap-6 z-10">
        <!-- Icono central -->
        <div class="flex items-center justify-center bg-teal-600 text-white rounded-full w-20 h-20 shadow-lg border-4 border-teal-300">
            <i class="fas fa-user-cog text-4xl"></i>
        </div>

        <!-- Texto central -->
        <div class="text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white tracking-wider flex items-center justify-center gap-4">
                <i class="fas fa-robot drop-shadow-md"></i>
                Panel del <span class="text-teal-300 drop-shadow-md">Asistente</span>
                <i class="fas fa-robot drop-shadow-md"></i>
            </h1>

            <p class="text-gray-300 mt-2 text-sm md:text-base font-medium max-w-xl mx-auto">
                Gestiona tareas, supervisa registros y apoya los procesos operativos según tus permisos asignados. Este panel adapta sus funciones a tu rol en el sistema.
            </p>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 p-6 font-sans animate-fade-in">
  <style>
    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(20px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .fade-in-up {
      animation: fadeInUp 0.8s ease-out forwards;
    }
  </style>



    <!-- Card 1: Personas -->
    <div class="bg-white rounded-2xl shadow-lg p-6 fade-in-up">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Personas</h2>
        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 20h5v-2a4 4 0 00-5-4H7a4 4 0 00-5 4v2h5m4-10a4 4 0 110-8 4 4 0 010 8z"/>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900">15</p>
      <p class="text-gray-500 mt-2">Usuarios registrados</p>
    </div>

    <!-- Card 2: Mantenimientos -->
    <div class="bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-100">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Mantenimientos</h2>
        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.75 3v2.25M14.25 3v2.25M3 7.5h18M4.5 7.5v11.25A2.25 2.25 0 006.75 21h10.5a2.25 2.25 0 002.25-2.25V7.5M9 12h.008v.008H9V12zM15 12h.008v.008H15V12z"/>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900">28</p>
      <p class="text-gray-500 mt-2">Tareas de mantenimiento</p>
    </div>

    <!-- Card 3: Proveedores -->
    <div class="bg-white rounded-2xl shadow-lg p-6 fade-in-up delay-200">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Proveedores</h2>
        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zm-6.5 8a5.5 5.5 0 0111 0v1.25a.75.75 0 01-.75.75H9.25a.75.75 0 01-.75-.75V15z"/>
        </svg>
      </div>
      <p class="text-4xl font-bold text-gray-900">7</p>
      <p class="text-gray-500 mt-2">Proveedores activos</p>
    </div>

  </div>
