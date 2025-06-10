<?php
include 'db_connection.php';

// Verificar si se ha recibido el ID del perfil
$id_perfil = isset($_GET['id_perfil']) ? intval($_GET['id_perfil']) : 0;

if ($id_perfil <= 0) {
    echo "<p>Error: No se especificó un perfil válido.</p>";
    exit;
}

// **1. Obtener los permisos configurados para menús**
$query_menus = "
    SELECT 
        m.id_menu, 
        m.nombre_menu, 
        p.id_permiso, 
        p.nombre_permiso, 
        COALESCE(ppm.id_status, 2) AS id_status
    FROM menus m
    JOIN permiso_menu pm ON m.id_menu = pm.id_menu
    JOIN permisos p ON pm.id_permiso = p.id_permiso
    LEFT JOIN perfil_permiso_menu ppm 
        ON ppm.id_menu = m.id_menu 
        AND ppm.id_permiso = p.id_permiso 
        AND ppm.id_perfil = :id_perfil
    ORDER BY m.id_menu, p.id_permiso
";

$stmt_menus = $conn->prepare($query_menus);
$stmt_menus->bindParam(':id_perfil', $id_perfil, PDO::PARAM_INT);
$stmt_menus->execute();
$menus_result = $stmt_menus->fetchAll(PDO::FETCH_ASSOC);

// **2. Obtener los permisos configurados para submenús**
$query_submenus = "
    SELECT 
        sm.id_submenu, 
        sm.nombre_submenu, 
        sm.descripcion, 
        m.nombre_menu, 
        p.id_permiso, 
        p.nombre_permiso, 
        COALESCE(pps.id_status, 2) AS id_status
    FROM submenus sm
    JOIN menus m ON sm.id_menu = m.id_menu
    JOIN permiso_submenu ps ON sm.id_submenu = ps.id_submenu
    JOIN permisos p ON ps.id_permiso = p.id_permiso
    LEFT JOIN perfil_permiso_submenu pps 
        ON pps.id_submenu = sm.id_submenu 
        AND pps.id_permiso = p.id_permiso 
        AND pps.id_perfil = :id_perfil
    ORDER BY sm.id_submenu, p.id_permiso
";

$stmt_submenus = $conn->prepare($query_submenus);
$stmt_submenus->bindParam(':id_perfil', $id_perfil, PDO::PARAM_INT);
$stmt_submenus->execute();
$submenus_result = $stmt_submenus->fetchAll(PDO::FETCH_ASSOC);
?>
<div id="menu-container" class="">
    <h2 class="text-2xl font-bold mb-4 text-blue-600 flex items-center">
        <i class="fas fa-bars mr-2"></i> Menús
    </h2>
    <?php
    $current_menu = null;

    foreach ($menus_result as $menu) {
        if ($current_menu !== $menu['id_menu']) {
            if ($current_menu !== null) {
                echo "</div>";
            }
            $current_menu = $menu['id_menu'];
            echo "<div class='menu-section mb-6'>";
            echo "<h3 class='text-xl font-semibold mb-2 flex items-center text-gray-700'>
                    <i class='fas fa-folder mr-2 text-yellow-500'></i>{$menu['nombre_menu']}
                  </h3>";
            echo "<div class='flex flex-wrap items-center space-x-4'>";
        }
        
        // Verificar qué icono corresponde a cada permiso
        $icon = '';
        switch ($menu['nombre_permiso']) {
            case 'Ver':
                $icon = 'fas fa-eye text-blue-500';
                break;
            case 'Registrar':
                $icon = 'fas fa-plus-circle text-green-500';
                break;
            case 'Modificar':
                $icon = 'fas fa-pen text-yellow-500';
                break;
            case 'Editar':
                $icon = 'fas fa-edit text-purple-500';
                break;
            case 'Consultar':
                $icon = 'fas fa-search text-red-500';
                break;
            default:
                $icon = 'fas fa-question text-gray-500';
        }

        // Checkbox con icono
        $checked = ($menu['id_status'] == 1) ? "checked" : "";
        echo "
            <label class='flex items-center py-2 px-3 bg-white border rounded-lg shadow-sm hover:bg-blue-50 transition duration-200'>
                <i class='$icon mr-2'></i>
                <input type='checkbox' class='form-checkbox h-5 w-5 text-blue-600 mr-2' name='menu[{$menu['id_menu']}][{$menu['id_permiso']}]' value='1' $checked>
                <span class='text-gray-600'>{$menu['nombre_permiso']}</span>
            </label>
        ";
    }

    if ($current_menu !== null) {
        echo "</div></div>";
    }
    ?>
</div>

<div id="submenu-container" class="">
    <h2 class="text-2xl font-bold mb-4 text-blue-600 flex items-center">
        <i class="fas fa-list mr-2"></i> Submenús
    </h2>
    <?php
    $current_submenu = null;

    foreach ($submenus_result as $submenu) {
        if ($current_submenu !== $submenu['id_submenu']) {
            if ($current_submenu !== null) {
                echo "</div>";
            }
            $current_submenu = $submenu['id_submenu'];
            echo "<div class='submenu-section mb-6'>";
            echo "<h3 class='text-xl font-semibold mb-2 flex items-center text-gray-700'>
                    <i class='fas fa-folder-open mr-2 text-green-500'></i>{$submenu['nombre_submenu']} (Menú: {$submenu['nombre_menu']})
                  </h3>";
            echo "<div class='flex flex-wrap items-center space-x-4'>";
        }
        
        // Verificar qué icono corresponde a cada permiso
        $icon = '';
        switch ($submenu['nombre_permiso']) {
            case 'Ver':
                $icon = 'fas fa-eye text-blue-500';
                break;
            case 'Registrar':
                $icon = 'fas fa-plus-circle text-green-500';
                break;
            case 'Modificar':
                $icon = 'fas fa-pen text-yellow-500';
                break;
            case 'Editar':
                $icon = 'fas fa-edit text-purple-500';
                break;
            case 'Consultar':
                $icon = 'fas fa-search text-red-500';
                break;
            default:
                $icon = 'fas fa-question text-gray-500';
        }

        // Checkbox con icono
        $checked = ($submenu['id_status'] == 1) ? "checked" : "";
        echo "
            <label class='flex items-center py-2 px-3 bg-white border rounded-lg shadow-sm hover:bg-blue-50 transition duration-200'>
                <i class='$icon mr-2'></i>
                <input type='checkbox' class='form-checkbox h-5 w-5 text-green-500 mr-2' name='submenu[{$submenu['id_submenu']}][{$submenu['id_permiso']}]' value='1' $checked>
                <span class='text-gray-600'>{$submenu['nombre_permiso']}</span>
            </label>
        ";
    }

    if ($current_submenu !== null) {
        echo "</div></div>";
    }
    ?>
</div>
