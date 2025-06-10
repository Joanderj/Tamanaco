<?php
$conn = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$id_perfil = $_POST['id_perfil'] ?? 1;
$id_usuario = $_POST['id_usuario'] ?? 1;
$id_status = 1;
$fecha_blog = date('Y-m-d');

$carpetaDestino = 'servidor_img/home/';
$nombreArchivo = $_FILES['imagen']['name'];
$rutaTemporal = $_FILES['imagen']['tmp_name'];
$rutaFinal = $carpetaDestino . basename($nombreArchivo);

// Opcional: Generar una URL accesible (ajusta si usas dominio o localhost)
$url = $rutaFinal;

if (!empty($nombreArchivo) && move_uploaded_file($rutaTemporal, $rutaFinal)) {
  $sql = "INSERT INTO blog (fecha_blog, titulo, descripcion, nombre_img, url, id_perfil, id_usuario, id_status)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssiii", $fecha_blog, $titulo, $descripcion, $nombreArchivo, $url, $id_perfil, $id_usuario, $id_status);

  if ($stmt->execute()) {
    echo "<script>alert('Publicación guardada exitosamente.'); window.location.href = 'blog_empresa.php';</script>";
  } else {
    echo "Error al guardar en base de datos: " . $stmt->error;
  }

  $stmt->close();
} else {
  echo "<script>
    alert('Error al subir la imagen.');
    window.location.href = 'blog_empresa.php';
  </script>";
}

$conn->close();
?>
