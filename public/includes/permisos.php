<?php
function obtenerPermisos($idPerfil, $idSubmenu) {
    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Solo traer permisos que estén activos, del perfil y submenu también activos
    $query = $conexion->prepare("
        SELECT id_permiso 
        FROM perfil_permiso_submenu 
        WHERE id_perfil = ? 
          AND id_submenu = ? 
          AND id_status = 1
    ");
    
    $query->bind_param("ii", $idPerfil, $idSubmenu);
    $query->execute();
    $result = $query->get_result();
    $permisos = [];

    while ($row = $result->fetch_assoc()) {
        $permisos[] = (int) $row['id_permiso'];
    }

    $query->close();
    $conexion->close();

    return $permisos;
}

function obtenerPermisosMenu($idPerfil, $idMenu) {
    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Solo traer permisos que estén activos, del perfil y menú también activos
    $query = $conexion->prepare("
        SELECT id_permiso 
        FROM perfil_permiso_menu 
        WHERE id_perfil = ? 
          AND id_menu = ? 
          AND id_status = 1
    ");
    
    $query->bind_param("ii", $idPerfil, $idMenu);
    $query->execute();
    $result = $query->get_result();
    $permisos = [];

    while ($row = $result->fetch_assoc()) {
        $permisos[] = (int) $row['id_permiso'];
    }

    $query->close();
    $conexion->close();

    return $permisos;
}
?>