<?php
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
if ($conexion->connect_error) {
  die("Conexión fallida: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nombreFiltro = $conexion->real_escape_string($_POST["filterName"]);
  $criterios = $conexion->real_escape_string($_POST["filterCriteria"]); // Guardar criterios como JSON
  
  $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado) 
            VALUES ('$nombreFiltro', 'sede', '$criterios', NOW())";

  if ($conexion->query($query) === TRUE) {
    echo "Filtro guardado exitosamente.";
  } else {
    echo "Error: " . $conexion->error;
  }
}

$conexion->close();
?>