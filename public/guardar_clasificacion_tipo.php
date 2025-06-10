<?php
// Configuración de la conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar la conexión
if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Obtener los datos enviados por el formulario
$nombreClasificacion = trim($_POST['nombre_clasificacion']); // Nombre de la clasificación
$abreviaturaClasificacion = trim($_POST['abreviatura_clasificacion']); // Abreviatura de la clasificación
$fechaCreacion = date("Y-m-d H:i:s"); // Fecha de creación actual
$id_status = 1; // Status predeterminado, suponiendo que 1 es válido

try {
    // Validar si el nombre de la clasificación ya existe
    $stmtNombre = $conexion->prepare("SELECT id_clasificacion FROM clasificacion WHERE nombre_clasificacion = ?");
    $stmtNombre->bind_param("s", $nombreClasificacion);
    $stmtNombre->execute();
    $resultadoNombre = $stmtNombre->get_result();

    if ($resultadoNombre->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El nombre de la clasificación ya existe.']);
        $stmtNombre->close();
        $conexion->close();
        exit;
    }
    $stmtNombre->close();

    // Validar si la abreviatura ya existe
    $stmtAbreviatura = $conexion->prepare("SELECT id_clasificacion FROM clasificacion WHERE abreviacion_clasificacion = ?");
    $stmtAbreviatura->bind_param("s", $abreviaturaClasificacion);
    $stmtAbreviatura->execute();
    $resultadoAbreviatura = $stmtAbreviatura->get_result();

    if ($resultadoAbreviatura->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'La abreviatura ya existe.']);
        $stmtAbreviatura->close();
        $conexion->close();
        exit;
    }
    $stmtAbreviatura->close();

    // Insertar la nueva clasificación en la tabla
    $stmtInsert = $conexion->prepare("INSERT INTO clasificacion (nombre_clasificacion, abreviacion_clasificacion, fecha_creacion, id_status) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("sssi", $nombreClasificacion, $abreviaturaClasificacion, $fechaCreacion, $id_status);

    if ($stmtInsert->execute()) {
        // Retornar datos de la clasificación recién insertada
        $idClasificacion = $stmtInsert->insert_id;
        echo json_encode([
            'success' => true,
            'id_clasificacion' => $idClasificacion,
            'nombre_clasificacion' => $nombreClasificacion,
            'abreviacion_clasificacion' => $abreviaturaClasificacion,
            'fecha_creacion' => $fechaCreacion,
            'id_status' => $id_status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la clasificación.']);
    }
    $stmtInsert->close();
} catch (Exception $e) {
    // Manejo de errores generales
    echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    // Cerrar la conexión
    $conexion->close();
}
?>