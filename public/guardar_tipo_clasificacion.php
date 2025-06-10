
<?php
// Configuración de la conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}

// Obtener los datos enviados por el formulario
$nombreTipo = $_POST['nombre_tipo']; // Nombre del tipo
$status = 1; // Status predeterminado
$fechaCreacion = date("Y-m-d H:i:s"); // Fecha de creación actual

// Validar si el nombre del tipo ya existe
$resultado = $conexion->query("SELECT id_tipo FROM tipo WHERE nombre_tipo = '$nombreTipo'");
if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El tipo ya existe.']);
    exit;
}

// Insertar el nuevo tipo en la tabla
$query = "INSERT INTO tipo (nombre_tipo, id_status, fecha_creacion) VALUES ('$nombreTipo', '$status', '$fechaCreacion')";
if ($conexion->query($query)) {
    // Retornar datos del tipo recién insertado
    $idTipo = $conexion->insert_id;
    echo json_encode([
        'success' => true,
        'id_tipo' => $idTipo,
        'nombre_tipo' => $nombreTipo,
        'status' => $status,
        'fecha_creacion' => $fechaCreacion
    ]);
} else {
    // Retornar un mensaje de error si la inserción falla
    echo json_encode(['success' => false, 'message' => 'Error al guardar el tipo.']);
}

// Cerrar la conexión
$conexion->close();
?>
