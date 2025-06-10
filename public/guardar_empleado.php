<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(["status" => "error", "mensaje" => "Error de conexión: " . $conexion->connect_error]));
}

// Lista de campos requeridos
$campos_requeridos = ["cedula", "nacionalidad", "nombre", "apellido", "email", "telefono", "fecha_nacimiento", "genero", "descripcion", "cargo", "pais", "estado"];

foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        echo json_encode(["status" => "error", "mensaje" => "Falta el campo: $campo"]);
        exit;
    }
}

// Capturar y limpiar los datos
$cedula = $conexion->real_escape_string($_POST['cedula']);
$nacionalidad = $conexion->real_escape_string($_POST['nacionalidad']);
$primer_nombre = $conexion->real_escape_string($_POST['nombre']);
$segundo_nombre = $conexion->real_escape_string($_POST['segundo_nombre'] ?? ''); // Opcional
$primer_apellido = $conexion->real_escape_string($_POST['apellido']);
$segundo_apellido = $conexion->real_escape_string($_POST['segundo_apellido'] ?? ''); // Opcional
$correo_electronico = $conexion->real_escape_string($_POST['email']);
$telefono = $conexion->real_escape_string($_POST['telefono']);
$fecha_nacimiento = $conexion->real_escape_string($_POST['fecha_nacimiento']);
$genero = $conexion->real_escape_string($_POST['genero']);
$direccion = $conexion->real_escape_string($_POST['descripcion']);
$id_cargo = intval($_POST['cargo']);
$id_status = 1; // Valor automático
$pais_id = intval($_POST['pais']);
$estado_id = intval($_POST['estado']);
$fecha_creacion = date("Y-m-d H:i:s");

// Verificar si la cédula ya existe
$queryVerificar = $conexion->prepare("SELECT COUNT(*) FROM personas WHERE cedula = ?");
$queryVerificar->bind_param("s", $cedula);
$queryVerificar->execute();
$queryVerificar->bind_result($existe);
$queryVerificar->fetch();
$queryVerificar->close();

if ($existe > 0) {
    echo json_encode(["status" => "error", "mensaje" => "Esta cédula ya está registrada"]);
    exit;
}

// Insertar datos en la tabla personas
$queryInsertar = $conexion->prepare("INSERT INTO personas (cedula, nacionalidad, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo_electronico, telefono, fecha_nacimiento, genero, direccion, id_cargo, id_status, pais_id, estado_id, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$queryInsertar->bind_param("sssssssssssiiiss", $cedula, $nacionalidad, $primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $correo_electronico, $telefono, $fecha_nacimiento, $genero, $direccion, $id_cargo, $id_status, $pais_id, $estado_id, $fecha_creacion);

if ($queryInsertar->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Empleado guardado correctamente"]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar empleado"]);
}

$queryInsertar->close();
$conexion->close();
?>