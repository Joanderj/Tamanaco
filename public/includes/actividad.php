<?php
function registrar_actividad($conn, $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia) {
    $query = "
        INSERT INTO registro_actividades (
            id_usuario, accion, actividad, modulo, ip_address, dispositivo, estado, importancia
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);

    if (is_null($id_usuario)) {
        $id_usuario = null; // explícitamente null
        $stmt->bind_param("ssssssss", $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia);
    } else {
        $stmt->bind_param("isssssss", $id_usuario, $accion, $actividad, $modulo, $ip_address, $dispositivo, $estado, $importancia);
    }

    $stmt->execute();
    $stmt->close();
}
?>
