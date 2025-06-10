<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Verificar campos requeridos
$campos_requeridos = ["nombre", "apellido", "email", "telefono", "fecha_nacimiento", "genero", "descripcion", "cargo", "pais", "estado", "id_persona"];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        echo json_encode(["status" => "error", "mensaje" => "Falta el campo: $campo"]);
        exit;
    }
}

// Capturar y limpiar los datos
$id_persona = intval($_POST['id_persona']);
$primer_nombre = $conexion->real_escape_string($_POST['nombre']);
$segundo_nombre = $conexion->real_escape_string($_POST['segundo_nombre'] ?? '');
$primer_apellido = $conexion->real_escape_string($_POST['apellido']);
$segundo_apellido = $conexion->real_escape_string($_POST['segundo_apellido'] ?? '');
$correo_electronico = $conexion->real_escape_string($_POST['email']);
$telefono = $conexion->real_escape_string($_POST['telefono']);
$fecha_nacimiento = $conexion->real_escape_string($_POST['fecha_nacimiento']);
$genero = $conexion->real_escape_string($_POST['genero']);
$direccion = $conexion->real_escape_string($_POST['descripcion']);
$id_cargo = intval($_POST['cargo']);
$pais_id = intval($_POST['pais']);
$estado_id = intval($_POST['estado']);

// Calcular edad
$fecha_actual = new DateTime();
$fecha_nac = new DateTime($fecha_nacimiento);
$edad = $fecha_nac->diff($fecha_actual)->y;

// Actualizar datos en la tabla personas (sin modificar cedula ni nacionalidad)
$queryActualizar = $conexion->prepare("
    UPDATE personas SET 
        primer_nombre = ?, 
        segundo_nombre = ?, 
        primer_apellido = ?, 
        segundo_apellido = ?, 
        correo_electronico = ?, 
        telefono = ?, 
        fecha_nacimiento = ?, 
        genero = ?, 
        direccion = ?, 
        id_cargo = ?, 
        pais_id = ?, 
        estado_id = ?
    WHERE id_persona = ?
");

$queryActualizar->bind_param(
    "ssssssssssiii",
    $primer_nombre,
    $segundo_nombre,
    $primer_apellido,
    $segundo_apellido,
    $correo_electronico,
    $telefono,
    $fecha_nacimiento,
    $genero,
    $direccion,
    $id_cargo,
    $pais_id,
    $estado_id,
    $id_persona
);

if ($queryActualizar->execute()) {
    echo json_encode([
        "status" => "success",
        "mensaje" => "Empleado actualizado correctamente",
        "edad" => $edad
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al actualizar empleado"
    ]);
}

$queryActualizar->close();
$conexion->close();
?>
