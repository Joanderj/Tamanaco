 <?php
// Aquí puedes conectar a la base de datos si no está incluido automáticamente
// include('conexion.php');

// Simulación de datos — reemplaza esto con tu consulta real
$herramientas = [
    ['codigo' => 'H001', 'nombre' => 'Llave inglesa', 'cantidad' => 12, 'estado' => 'Operativa'],
    ['codigo' => 'H002', 'nombre' => 'Destornillador', 'cantidad' => 34, 'estado' => 'Operativa'],
    ['codigo' => 'H003', 'nombre' => 'Taladro', 'cantidad' => 5, 'estado' => 'En reparación'],
];
?>

<div class="bg-white p-4 rounded-lg shadow-md">
  <div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-semibold text-gray-700">
      <i class="fas fa-wrench text-red-500 mr-2"></i> Inventario de Herramientas
    </h3>
    <div class="space-x-2">
      <button onclick="location.href='formulario_inventario_herramienta.php'"
              class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-2 rounded-md shadow text-sm">
        <i class="fas fa-edit mr-1"></i> Actualizar Inventario
      </button>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 border">Código</th>
          <th class="px-4 py-2 border">Nombre</th>
          <th class="px-4 py-2 border">Cantidad</th>
          <th class="px-4 py-2 border">Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($herramientas as $h) : ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 border"><?= htmlspecialchars($h['codigo']) ?></td>
            <td class="px-4 py-2 border"><?= htmlspecialchars($h['nombre']) ?></td>
            <td class="px-4 py-2 border"><?= htmlspecialchars($h['cantidad']) ?></td>
            <td class="px-4 py-2 border"><?= htmlspecialchars($h['estado']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
