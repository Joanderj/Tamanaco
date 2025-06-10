<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
  die("Conexión fallida: " . $conexion->connect_error);
}

if (isset($_GET['id_filtro'])) {
  $idFiltro = intval($_GET['id_filtro']);
  $query = "DELETE FROM filtros_guardados WHERE id_filtro = $idFiltro";

  if ($conexion->query($query) === TRUE) {
    echo "Filtro eliminado correctamente.";
  } else {
    echo "Error: " . $conexion->error;
  }
}

$conexion->close();
?>