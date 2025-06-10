<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos enviados
$campo = $_POST['campo'];
$valor = $_POST['valor'];
$nacionalidad = $_POST['nacionalidad'] ?? ''; // Solo necesario para validar la cédula

// Definir la consulta según el campo
if ($campo === 'cedula') {
    // Validar cédula con nacionalidad
    $sql = "SELECT COUNT(*) AS existe FROM personas WHERE cedula = ? AND nacionalidad = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $valor, $nacionalidad);
} else {
    // Validar teléfono o correo
    $sql = "SELECT COUNT(*) AS existe FROM personas WHERE $campo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $valor);
}

// Ejecutar consulta
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

if ($fila['existe'] > 0) {
    echo "existe"; // Si el registro ya existe
} else {
    echo "disponible"; // Si el registro está disponible
}

// Cerrar conexión
$stmt->close();
$conexion->close();
?>