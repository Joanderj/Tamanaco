<?php
// Configuración de la conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}

// Obtener los datos enviados por el formulario
$nombreModelo = $_POST['nombre_modelo']; // Nombre del modelo
$anio = date("Y"); // Año actual
$status = 1; // Status predeterminado
$fechaCreacion = date("Y-m-d H:i:s"); // Fecha de creación actual

// Validar si el nombre del modelo ya existe
$resultado = $conexion->query("SELECT id_modelo FROM modelo WHERE nombre_modelo = '$nombreModelo'");
if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El modelo ya existe.']);
    exit;
}

// Insertar el nuevo modelo en la tabla
$query = "INSERT INTO modelo (nombre_modelo, año, id_status, fecha_creacion) VALUES ('$nombreModelo', '$anio', '$status', '$fechaCreacion')";
if ($conexion->query($query)) {
    // Retornar datos del modelo recién insertado
    $idModelo = $conexion->insert_id;
    echo json_encode([
        'success' => true,
        'id_modelo' => $idModelo,
        'nombre_modelo' => $nombreModelo,
        'año' => $anio,
        'status' => $status,
        'fecha_creacion' => $fechaCreacion
    ]);
} else {
    // Retornar un mensaje de error si la inserción falla
    echo json_encode(['success' => false, 'message' => 'Error al guardar el modelo.']);
}

// Cerrar la conexión
$conexion->close();
?>