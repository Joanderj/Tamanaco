<?php
include 'db_connection.php'; // Asegúrate de que esta conexión use PDO

// Validar si se seleccionó un perfil
$id_perfil = isset($_POST['id_perfil']) ? intval($_POST['id_perfil']) : null;
$menus = isset($_POST['menu']) ? $_POST['menu'] : []; // Permisos de menús
$submenus = isset($_POST['submenu']) ? $_POST['submenu'] : []; // Permisos de submenús

if (!$id_perfil) {
    echo "Error: No se seleccionó un perfil.";
    exit;
}

try {
    // Iniciar transacción para garantizar la integridad de los datos
    $conn->beginTransaction();

    // 1. Inactivar todos los permisos existentes para este perfil
    $inactivate_all = "UPDATE perfil_permiso_submenu SET id_status = 2 WHERE id_perfil = ?";
    $stmt = $conn->prepare($inactivate_all);
    $stmt->execute([$id_perfil]);

    $inactivate_all_menu = "UPDATE perfil_permiso_menu SET id_status = 2 WHERE id_perfil = ?";
    $stmt = $conn->prepare($inactivate_all_menu);
    $stmt->execute([$id_perfil]);

    $inactivate_all_perfil_menu = "UPDATE perfil_menu SET id_status = 2 WHERE id_perfil = ?";
    $stmt = $conn->prepare($inactivate_all_perfil_menu);
    $stmt->execute([$id_perfil]);

    $inactivate_all_perfil_submenu = "UPDATE perfil_submenu SET id_status = 2 WHERE id_perfil = ?";
    $stmt = $conn->prepare($inactivate_all_perfil_submenu);
    $stmt->execute([$id_perfil]);

    // 2. Actualizar Permisos Específicos en Menús y Submenús
    $active_menus = [];

    foreach ($menus as $id_menu => $permissions) {
        $id_menu = intval($id_menu);
        $menu_has_active_permission = false;

        foreach ($permissions as $id_permiso => $status) {
            $id_permiso = intval($id_permiso);
            $permiso_status = $status == 1 ? 1 : 2;
            if ($permiso_status == 1) {
                $menu_has_active_permission = true;
            }
            $query_permiso_menu = "INSERT INTO perfil_permiso_menu (id_perfil, id_menu, id_permiso, id_status)
                                   VALUES (?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE id_status = ?";
            $stmt = $conn->prepare($query_permiso_menu);
            $stmt->execute([$id_perfil, $id_menu, $id_permiso, $permiso_status, $permiso_status]);
        }

        if ($menu_has_active_permission) {
            $active_menus[] = $id_menu;
        }
    }

    foreach ($submenus as $id_submenu => $permissions) {
        $id_submenu = intval($id_submenu);
        $submenu_has_active_permission = false;

        foreach ($permissions as $id_permiso => $status) {
            if ($id_permiso !== 'active') {
                $id_permiso = intval($id_permiso);
                $permiso_status = $status == 1 ? 1 : 2;
                if ($permiso_status == 1) {
                    $submenu_has_active_permission = true;
                }
                $query_permiso_submenu = "INSERT INTO perfil_permiso_submenu (id_perfil, id_submenu, id_permiso, id_status)
                                          VALUES (?, ?, ?, ?)
                                          ON DUPLICATE KEY UPDATE id_status = ?";
                $stmt = $conn->prepare($query_permiso_submenu);
                $stmt->execute([$id_perfil, $id_submenu, $id_permiso, $permiso_status, $permiso_status]);
            }
        }

        if ($submenu_has_active_permission) {
            // Activar el submenú
            $query_submenu = "INSERT INTO perfil_submenu (id_perfil, id_submenu, id_status)
                              VALUES (?, ?, 1)
                              ON DUPLICATE KEY UPDATE id_status = 1";
            $stmt = $conn->prepare($query_submenu);
            $stmt->execute([$id_perfil, $id_submenu]);

            // Obtener y activar el menú padre
            $query_get_menu = "SELECT id_menu FROM submenus WHERE id_submenu = ?";
            $stmt = $conn->prepare($query_get_menu);
            $stmt->execute([$id_submenu]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_menu = $row['id_menu'];
            
            if (!in_array($id_menu, $active_menus)) {
                $active_menus[] = $id_menu;
            }
        }
    }

    // Activar los menús que tienen permisos activos o submenús activos
    foreach ($active_menus as $id_menu) {
        $query_menu = "INSERT INTO perfil_menu (id_perfil, id_menu, id_status)
                       VALUES (?, ?, 1)
                       ON DUPLICATE KEY UPDATE id_status = 1";
        $stmt = $conn->prepare($query_menu);
        $stmt->execute([$id_perfil, $id_menu]);
    }

    // Confirmar la transacción
    $conn->commit();
    echo "Permisos actualizados exitosamente.";
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    echo "Error al actualizar permisos: " . $e->getMessage();
}

// Cerrar la conexión
$conn = null;
?>
