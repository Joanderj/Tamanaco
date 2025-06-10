<?php
// Configuración de la conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']));
}

// Obtener los datos enviados por el formulario
$nombreMarca = $_POST['nombre_marca']; // Nombre de la marca
$status = 1; // Status predeterminado
$fechaCreacion = date("Y-m-d H:i:s"); // Fecha de creación actual

// Validar si el nombre de la marca ya existe
$resultado = $conexion->query("SELECT id_marca FROM marca WHERE nombre_marca = '$nombreMarca'");
if ($resultado->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'La marca ya existe.']);
    exit;
}

// Insertar la nueva marca en la tabla
$query = "INSERT INTO marca (nombre_marca, id_status, fecha_creacion) VALUES ('$nombreMarca', '$status', '$fechaCreacion')";
if ($conexion->query($query)) {
    // Retornar datos de la marca recién insertada
    $idMarca = $conexion->insert_id;
    echo json_encode([
        'success' => true,
        'id_marca' => $idMarca,
        'nombre_marca' => $nombreMarca,
        'status' => $status,
        'fecha_creacion' => $fechaCreacion
    ]);
} else {
    // Retornar un mensaje de error si la inserción falla
    echo json_encode(['success' => false, 'message' => 'Error al guardar la marca.']);
}

// Cerrar la conexión
$conexion->close();
?>