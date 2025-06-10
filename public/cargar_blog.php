<?php
// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Consulta los blogs activos (puedes ajustar si quieres mostrar solo los más recientes, por ejemplo con LIMIT)
$sql = "SELECT titulo, descripcion, nombre_img, fecha_blog FROM blog WHERE id_status = 1 ORDER BY fecha_blog DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    // Sanitiza los valores
    $titulo = htmlspecialchars($row['titulo']);
    $descripcion = htmlspecialchars($row['descripcion']);
    $imagen = !empty($row['nombre_img']) ? 'servidor_img/home/' . $row['nombre_img'] : 'img/default.jpg';
    $fecha = date("d/m/Y", strtotime($row['fecha_blog']));

    echo '
    <div class="mx-auto bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl p-6 transition-all duration-500 hover:shadow-indigo-300 border border-white/40 relative overflow-hidden scale-95 hover:scale-100">
      <div class="rounded-xl overflow-hidden mb-6 shadow-md">
        <img src="' . $imagen . '" alt="Imagen de portada" class="w-full h-auto max-h-[400px] object-cover transition duration-500 ease-in-out hover:scale-105">
      </div>
      <h4 class="text-2xl font-bold text-gray-800 mb-3 hover:text-indigo-600 transition duration-300">' . $titulo . '</h4>
      <p class="text-gray-700 text-base leading-relaxed">' . $descripcion . '</p>
      <div class="border-t pt-4 mt-6 flex justify-between text-gray-600 text-sm">
        <span class="text-xs text-gray-400">Publicado el ' . $fecha . '</span>
      </div>
    </div>
    ';
  }
} else {
  echo '<p class="text-center text-gray-500">No hay publicaciones disponibles.</p>';
}

$conn->close();
?>