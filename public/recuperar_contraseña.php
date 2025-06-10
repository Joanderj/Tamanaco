<?php
session_start();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificación de Identidad</title>
  <link href="../public/css/tailwind.min.css" rel="stylesheet">
  <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-2xl rounded-2xl w-full max-w-xl p-8 space-y-6 animate-fade-in">
  
  <!-- PASOS -->
  <div class="flex justify-between items-center text-sm font-semibold text-gray-500 uppercase tracking-wide border-b pb-3">
    <div class="flex-1 text-center relative">
      <div class="text-indigo-600">Paso 1</div>
      <div class="font-bold text-indigo-700">Verificación</div>
      <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-indigo-500 rounded-full"></div>
    </div>
    <div class="flex-1 text-center">
      <div>Paso 2</div>
      <div class="text-gray-400">Validación</div>
    </div>
    <div class="flex-1 text-center">
      <div>Paso 3</div>
      <div class="text-gray-400">Acceso</div>
    </div>
  </div>

  <!-- TITULO -->
  <div class="text-center">
    <h2 class="text-2xl font-extrabold text-gray-800">Verifica tu Identidad</h2>
    <p class="text-sm text-gray-500 mt-1">Ingresa tu nacionalidad, cédula y nombre de usuario</p>
  </div>

  <!-- FORMULARIO -->
  <form action="validar_verificacion.php" method="POST" class="space-y-4">
    <div class="flex gap-4">
      <div class="w-1/3">
        <label for="nacionalidad" class="block text-sm font-medium text-gray-700 mb-1">Nacionalidad</label>
        <select name="nacionalidad" id="nacionalidad" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
          <option value="V">V</option>
          <option value="E">E</option>
        </select>
      </div>
      <div class="w-2/3">
        <label for="cedula" class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
        <input type="text" name="cedula" id="cedula" maxlength="10"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
               placeholder="Ej: 12345678" required>
      </div>
    </div>
    <div>
      <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario</label>
      <div class="relative">
        <input type="text" name="usuario" id="usuario"
               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pr-10"
               placeholder="Ej: jdoe25" required>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
          <i class="fas fa-user text-gray-400"></i>
        </div>
      </div>
    </div>
    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-all duration-300">
      <i class="fas fa-check-circle mr-2"></i> Verificar
    </button>
  </form>

  <?php if ($error): ?>
    <div class="text-red-600 text-sm text-center font-semibold mt-2">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

</div>

<style>
  .animate-fade-in {
    animation: fadeIn 0.5s ease-out;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
</body>
</html>
