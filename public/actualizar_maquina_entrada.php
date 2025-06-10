<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

// Comprobar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Obtener valores del formulario
$id_maquina = $_POST['maquina'];
$cantidad = 1; // Valor inicial al registrar
$id_sede = $_POST['sede'];
$codigo_unico = $_POST['codigo'];
$id_status = 1; // Estado inicial predeterminado

// Verificar si la máquina ya existe en el inventario de la sede
$query = "SELECT cantidad FROM inventario_maquina WHERE id_maquina = ? AND sede_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $id_maquina, $id_sede);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si ya existe, aumentar la cantidad en 1
    $row = $result->fetch_assoc();
    $nueva_cantidad = $row['cantidad'] + 1;
    $update_query = "UPDATE inventario_maquina SET cantidad = ? WHERE id_maquina = ? AND sede_id = ?";
    $update_stmt = $conexion->prepare($update_query);
    $update_stmt->bind_param("iii", $nueva_cantidad, $id_maquina, $id_sede);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    // Si no existe, insertar una nueva entrada con cantidad 1
    $insert_query = "INSERT INTO inventario_maquina (id_maquina, cantidad, sede_id) VALUES (?, ?, ?)";
    $insert_stmt = $conexion->prepare($insert_query);
    $insert_stmt->bind_param("iii", $id_maquina, $cantidad, $id_sede);
    $insert_stmt->execute();
    $insert_stmt->close();
}

// Registrar en la tabla maquina_unica (registro individual de máquina)
$insert_maquina_unica_query = "INSERT INTO maquina_unica (id_maquina, id_sede, codigounico, id_status) VALUES (?, ?, ?, ?)";
$maquina_unica_stmt = $conexion->prepare($insert_maquina_unica_query);
$maquina_unica_stmt->bind_param("iisi", $id_maquina, $id_sede, $codigo_unico, $id_status);
$maquina_unica_stmt->execute();

// Obtener el ID generado automáticamente para la máquina única
$id_maquina_unica = $conexion->insert_id;

$maquina_unica_stmt->close();

// Registrar en la tabla maquina_repuesto si se han enviado repuestos
if (isset($_POST['repuestos'])) {
    foreach ($_POST['repuestos'] as $id_repuesto) {
        $insert_repuesto_query = "INSERT INTO maquina_repuesto (id_maquina, id_repuesto, id_status) VALUES ( ?, ?, ?)";
        $repuesto_stmt = $conexion->prepare($insert_repuesto_query);
        $status_repuesto = 1; // Estado predeterminado
        $repuesto_stmt->bind_param("iii", $id_maquina_unica, $id_repuesto, $status_repuesto);
        $repuesto_stmt->execute();
        $repuesto_stmt->close();
    }
}

// Cerrar el statement principal
$stmt->close();

// Redirigir al usuario tras éxito
header("Location: inventario_maquina.php");
exit();
?>
