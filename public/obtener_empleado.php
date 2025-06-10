<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Obtener el ID del empleado seleccionado
if (!empty($_POST['id_empleado'])) {
    $id_empleado = intval($_POST['id_empleado']);

    // Consulta para obtener los datos del empleado
    $query = $conexion->prepare("SELECT nacionalidad, cedula, primer_nombre, primer_apellido FROM personas WHERE id_persona = ?");
    $query->bind_param("i", $id_empleado);
    $query->execute();
    $resultado = $query->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        // Concatenar la nacionalidad con la cédula antes de enviarlo
        $fila['cedula'] = $fila['nacionalidad'] . "-" . $fila['cedula'];
        echo json_encode(["status" => "success", "datos" => $fila]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "No se encontró el empleado"]);
    }

    $query->close();
} else {
    echo json_encode(["status" => "error", "mensaje" => "ID de empleado no recibido"]);
}

$conexion->close();
?>