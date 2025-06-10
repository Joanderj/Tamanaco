<?php
// Configuración de conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se recibió un país válido
if (!empty($_POST['pais_id'])) {
    $pais_id = intval($_POST['pais_id']); // Asegurar que sea un número entero

    // Preparar consulta para evitar inyección SQL
    $query = $conexion->prepare("SELECT id, estadonombre FROM estado WHERE ubicacionpaisid = ?");
    $query->bind_param("i", $pais_id);
    $query->execute();
    $resultado = $query->get_result();

    echo '<option value="">Seleccionar</option>';
    while ($fila = $resultado->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($fila['id']) . "'>" . htmlspecialchars($fila['estadonombre']) . "</option>";
    }

    $query->close();
}

$conexion->close();
?>