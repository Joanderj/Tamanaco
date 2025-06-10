<!-- recuperar_token.php -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificación de Código</title>
  <link href="../public/css/tailwind.min.css" rel="stylesheet">
  <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-2xl rounded-2xl w-full max-w-xl p-8 space-y-6 animate-fade-in">

  <!-- PASOS -->
  <div class="flex justify-between items-center text-sm font-semibold text-gray-500 uppercase tracking-wide border-b pb-3">
    <div class="flex-1 text-center">
      <div>Paso 1</div>
      <div class="text-gray-400">Verificación</div>
    </div>
    <div class="flex-1 text-center relative">
      <div class="text-indigo-600">Paso 2</div>
      <div class="font-bold text-indigo-700">Validación</div>
      <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-indigo-500 rounded-full"></div>
    </div>
    
    <div class="flex-1 text-center">
      <div>Paso 3</div>
      <div class="text-gray-400">Acceso</div>
    </div>
  </div>

  <!-- TÍTULO -->
  <div class="text-center">
    <h2 class="text-2xl font-extrabold text-gray-800">Ingresa el Código de Verificación</h2>
    <p class="text-sm text-gray-500 mt-1">Revisa tu correo electrónico para encontrar tu código de 6 dígitos</p>
  </div>

  <!-- FORMULARIO -->
  <form action="validar_token.php" method="POST" class="space-y-6">
    <div class="flex justify-between gap-2">
      <?php for ($i = 1; $i <= 6; $i++): ?>
        <input type="text" name="digit<?= $i ?>" maxlength="1" required
               class="w-12 h-12 text-center text-lg font-semibold border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      <?php endfor; ?>
    </div> 

    <button type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition-all duration-300">
      <i class="fas fa-paper-plane mr-2"></i> Verificar Código
    </button>
  </form>

</div>

<!-- ANIMACIÓN -->
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
