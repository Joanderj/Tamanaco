    <?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el id_perfil del usuario actual desde la sesión
$id_perfil = $_SESSION['id_perfil'];

// Menú actual (empresa.php -> id_menu = 9)
$menu_actual = 9;

// Verificar si el menú actual está inactivo o el perfil no tiene permisos
$sql_verificar_menu = "
    SELECT COUNT(*) AS permiso
    FROM menus m
    INNER JOIN perfil_menu pm ON m.id_menu = pm.id_menu
    WHERE m.id_menu = ? AND pm.id_perfil = ? AND m.id_status = 1 AND pm.id_status = 1
";
$stmt_verificar_menu = $conexion->prepare($sql_verificar_menu);
$stmt_verificar_menu->bind_param("ii", $menu_actual, $id_perfil);
$stmt_verificar_menu->execute();
$result_verificar_menu = $stmt_verificar_menu->get_result();
$permiso_menu = $result_verificar_menu->fetch_assoc();

// Submenú actual: sucursal (id_submenu = 7)
$submenu_actual = 7;

// Verificar si el submenú "sucursal" está activo y si el perfil tiene permisos
$sql_verificar_submenu = "
    SELECT COUNT(*) AS permiso
    FROM submenus s
    INNER JOIN perfil_submenu ps ON s.id_submenu = ps.id_submenu
    WHERE s.id_submenu = ? AND ps.id_perfil = ? AND s.id_status = 1 AND ps.id_status = 1
";
$stmt_verificar_submenu = $conexion->prepare($sql_verificar_submenu);
$stmt_verificar_submenu->bind_param("ii", $submenu_actual, $id_perfil);
$stmt_verificar_submenu->execute();
$result_verificar_submenu = $stmt_verificar_submenu->get_result();
$permiso_submenu = $result_verificar_submenu->fetch_assoc();

if ($permiso_submenu['permiso'] == 0) {
    // Si el submenú está inactivo o el perfil no tiene permisos, redirigir a dashboard.php
    header("Location: dashboard.php");
    exit();
}

// Consulta para obtener los menús principales (tipo_menu = 1) activos y permitidos
$sql_principal = "
    SELECT m.*
    FROM menus m
    INNER JOIN perfil_menu pm ON m.id_menu = pm.id_menu
    WHERE m.id_status = 1 AND pm.id_status = 1 AND pm.id_perfil = ? AND m.tipo_menu = 1
    ORDER BY m.id_menu
";
$stmt_principal = $conexion->prepare($sql_principal);
$stmt_principal->bind_param("i", $id_perfil);
$stmt_principal->execute();
$result_principal = $stmt_principal->get_result();

$menus_principal = [];
while ($menu = $result_principal->fetch_assoc()) {
    $menus_principal[] = $menu;
}

// Consulta para obtener los menús del usuario (tipo_menu = 2) activos y permitidos
$sql_usuario = "
    SELECT m.*
    FROM menus m
    INNER JOIN perfil_menu pm ON m.id_menu = pm.id_menu
    WHERE m.id_status = 1 AND pm.id_status = 1 AND pm.id_perfil = ? AND m.tipo_menu = 2
    ORDER BY m.id_menu
";
$stmt_usuario = $conexion->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $id_perfil);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

$menus_usuario = [];
while ($menu = $result_usuario->fetch_assoc()) {
    $menus_usuario[] = $menu;
}

// Consulta para obtener los submenús tipo 1 activos y permitidos
$sql_submenus_tipo_1 = "
    SELECT s.nombre_submenu, s.descripcion, s.url_submenu
    FROM submenus s
    INNER JOIN perfil_submenu ps ON s.id_submenu = ps.id_submenu
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 9
    ORDER BY s.id_submenu
";
$stmt_submenus_tipo_1 = $conexion->prepare($sql_submenus_tipo_1);
$stmt_submenus_tipo_1->bind_param("i", $id_perfil);
$stmt_submenus_tipo_1->execute();
$result_submenus_tipo_1 = $stmt_submenus_tipo_1->get_result();

$submenus_tipo_1 = [];
while ($submenu = $result_submenus_tipo_1->fetch_assoc()) {
    $submenus_tipo_1[] = $submenu;
}

// Consulta para obtener los submenús tipo 2 activos y permitidos para el menú actual
$sql_submenus_tipo_2 = "
    SELECT s.nombre_submenu, s.descripcion, s.url_submenu
    FROM submenus s
    INNER JOIN perfil_submenu ps ON s.id_submenu = ps.id_submenu
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 2 AND s.id_menu = ?
    ORDER BY s.id_submenu
";
$stmt_submenus_tipo_2 = $conexion->prepare($sql_submenus_tipo_2);
$stmt_submenus_tipo_2->bind_param("ii", $id_perfil, $menu_actual);
$stmt_submenus_tipo_2->execute();
$result_submenus_tipo_2 = $stmt_submenus_tipo_2->get_result();

$submenus_tipo_2 = [];
while ($submenu = $result_submenus_tipo_2->fetch_assoc()) {
    $submenus_tipo_2[] = $submenu;
}

// Variables para paginación
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Recuperar los filtros
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$minimo = isset($_GET['minimo']) ? $_GET['minimo'] : '';
$almacen = isset($_GET['almacen'])? $_GET['almacen'] : '';

// Clasificación
$orderBy = 'id_inventario_maquina';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'cantidad_asc':
            $orderBy = 'cantidad ASC';
            break;
        case 'cantidad_desc':
            $orderBy = 'cantidad DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'nombre_maquina ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'nombre_maquina DESC';
            break;
        case 'nombre_almacen_asc':
            $orderBy = 'sede_id ASC';
            break;
        case 'nombre_almacen_desc':
            $orderBy = 'sede_id DESC';
            break;
    }
}

// Consulta base reutilizable
$baseQuery = "
    SELECT 
        ip.id_inventario_maquina,
        m.nombre_maquina,
        ip.cantidad,
        se.nombre_sede,
        m.id_maquina,
        se.id_sede,
        ma.nombre_marca,
        (
            SELECT COUNT(*) 
            FROM maquina_unica mu 
            WHERE mu.id_maquina = ip.id_maquina 
              AND mu.id_sede = ip.sede_id 
              AND mu.id_status = 2
        ) AS cantidad_inactiva,
         (
            SELECT COUNT(*) 
            FROM maquina_unica mu 
            WHERE mu.id_maquina = ip.id_maquina 
              AND mu.id_sede = ip.sede_id 
              AND mu.id_status = 1
        ) AS cantidad_activa,
         (
            SELECT COUNT(*) 
            FROM maquina_unica mu 
            WHERE mu.id_maquina = ip.id_maquina 
              AND mu.id_sede = ip.sede_id 
              AND mu.id_status = 23
        ) AS cantidad_en_camino
    FROM inventario_maquina ip 
    JOIN maquina m ON ip.id_maquina = m.id_maquina
    JOIN marca ma ON m.id_marca = ma.id_marca
    JOIN sede se ON ip.sede_id = se.id_sede
    WHERE 1=1
";

// Construir condiciones dinámicas
$conditions = "";
$params = [];

if (!empty($nombre)) {
    $conditions .= " AND m.nombre_maquina LIKE ?";
    $params[] = '%' . $nombre . '%';
}

if (!empty($cantidad)) {
    $conditions .= " AND ip.cantidad = ?";
    $params[] = $cantidad;
}

if (!empty($sede)) {
    $conditions .= " AND se.nombre_sede = ?";
    $params[] = $sede;
}


// Consulta para total de items (paginación)
$totalQuery = "SELECT COUNT(*) FROM (" . $baseQuery . $conditions . ") AS total";
$totalStmt = $conexion->prepare($totalQuery);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $totalStmt->bind_param($types, ...$params);
}

if (!$totalStmt->execute()) {
    die("Error al contar items: " . $totalStmt->error);
}

$totalItems = $totalStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta principal con paginación
$query = $baseQuery . $conditions . " ORDER BY " . $orderBy . " LIMIT ?, ?";
$paramsPaginated = $params;
$paramsPaginated[] = $offset;
$paramsPaginated[] = $itemsPerPage;

$stmt = $conexion->prepare($query);

if (!empty($paramsPaginated)) {
    $types = str_repeat('s', count($paramsPaginated));
    $stmt->bind_param($types, ...$paramsPaginated);
}

if (!$stmt->execute()) {
    die("Error al obtener inventario: " . $stmt->error);
}

$result = $stmt->get_result();
$inventario = $result->fetch_all(MYSQLI_ASSOC);

// Guardar Filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_filter') {
    $filterName = $_POST['filterName'];
    
    // Crear criterios como un JSON
    $criterios = json_encode([
        'nombre' => $nombre,
        'status' => $status,
        'fechaInicio' => $fechaInicio,
        'fechaFinal' => $fechaFinal,
    ]);

    $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado, usuario_id_filtro) 
              VALUES (?, 'sucursal', ?, NOW(), ?)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssi", $filterName, $criterios, $id_perfil);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}

// Cargar Filtros Guardados
$sql_cargar_filtros = "
    SELECT nombre_filtro, criterios 
    FROM filtros_guardados 
    WHERE usuario_id_filtro = ?
";
$stmt_cargar_filtros = $conexion->prepare($sql_cargar_filtros);
$stmt_cargar_filtros->bind_param("i", $id_perfil);
$stmt_cargar_filtros->execute();
$result_cargar_filtros = $stmt_cargar_filtros->get_result();

$filtros_guardados = [];
while ($filtro = $result_cargar_filtros->fetch_assoc()) {
    $filtros_guardados[] = [
        'nombre_filtro' => $filtro['nombre_filtro'],
        'criterios' => json_decode($filtro['criterios'], true),
    ];
}

$stmt_cargar_filtros->close();

// Recuperar el filtro de estado
$id_status = isset($_GET['id_status']) ? (int)$_GET['id_status'] : 1; // Por defecto, mostrar "entregando"
$tipo=3;

// Consulta para obtener solicitudes con filtro de estado
$sql_solicitudes = "
    SELECT so.id_tipo_solicitud, so.fecha_solicitud,so.fecha_recibido,ts.nombre_tipo, ma.*
    FROM solicitudes so
    JOIN movimiento_producto ma ON ma.id_solicitud = so.id_solicitud
    JOIN tipos_solicitudes ts ON so.id_tipo_solicitud = ts.id_tipo_solicitud
    WHERE so.id_status = ? and so.id_tipo_solicitud=?
    ORDER BY so.fecha_solicitud DESC
";
$stmt_solicitudes = $conexion->prepare($sql_solicitudes);
$stmt_solicitudes->bind_param("ii", $id_status,$tipo);
$stmt_solicitudes->execute();
$result_solicitudes = $stmt_solicitudes->get_result();

$solicitudes = [];
while ($solicitud = $result_solicitudes->fetch_assoc()) {
    $solicitudes[] = $solicitud;
}


// Cerrar la conexión
$conexion->close();

// Aquí puedes pasar los filtros guardados a tu vista
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfaz de Usuario</title>
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
        <!--  CSS -->
        <link rel="stylesheet" href="../public/css/flatpickr.min.css">
        <link rel="stylesheet" href="../public/css/all.min.css">
        <link rel="stylesheet" href="../public/css/main.min.css">
       <!-- js -->
       <script src="../public/js/chart.js"></script>
    <style>
      
        .user-dropdown {
            display: none;
            position: absolute;
            background-color: rgb(255, 255, 255);
            border: 1px solid #ccc;
            padding: 10px;
            z-index: 10;
        }
        .user-icon:hover + .user-dropdown {
            display: block;
        }
        .sidebar {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
            z-index: 20;
        }
        .sidebar.active {
            display: block;
        }

        /* Mostrar el tooltip solo al pasar el cursor */
  .menu-item:hover .tooltip {
      display: block; /* Se hace visible al pasar el cursor */
  }
  /* Muestra el tooltip al pasar el cursor */
  .notifications-icon:hover .tooltip {
      display: block; /* Se hace visible */
  }
    </style>
</head>
<header style="background-color: rgb(14, 113, 174);" class="flex items-center justify-between p-4 bg-[rgb(14,113,174)] shadow text-white">
        <!-- Botón de menú lateral y logo -->
        <div class="flex items-center">
            <div class="menu-toggle cursor-pointer text-xl mr-4" onclick="toggleSidebar()">☰</div>
            <div class="logo flex-shrink-0">
                <img src="../public/img/logo2.png" alt="Logo Tamanaco" class="h-6 max-w-[100px] w-auto object-contain sm:h-8 sm:max-w-[120px]">
            </div>
            <div class="company-name text-white ml-2 font-bold text-lg">Tamanaco</div>
        </div>

        <!-- Menú de Navegación -->
        <nav class="absolute inset-x-0 top-0 flex justify-center space-x-6 mt-6">
            <?php foreach ($menus_principal as $menu): ?>
                <a href="<?php echo htmlspecialchars($menu['url_menu']); ?>" class="menu-item relative flex items-center space-x-2 hover:text-gray-300">
                    <i class="fa fa-<?php echo htmlspecialchars($menu['nombre_menu'] == 'Inicio' ? 'home' : ($menu['nombre_menu'] == 'Empleado' ? 'user' : ($menu['nombre_menu'] == 'Inventario' ? 'box' : ($menu['nombre_menu'] == 'Mantenimiento' ? 'tools' : ($menu['nombre_menu'] == 'Reporte' ? 'chart-bar' : 'tasks'))))); ?> text-xl"></i>
                    <span class="md:block hidden"><?php echo htmlspecialchars($menu['nombre_menu']); ?></span>
                    <!-- Tooltip -->
                    <div class="tooltip hidden absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white px-3 py-1 rounded text-sm z-30">
                        <?php echo htmlspecialchars($menu['descripcion']); ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </nav>
 
<div  class="flex items-center space-x-6">
    <div class="h-6 w-px bg-white"></div>
  <!-- Ícono de Notificaciones -->
  <div class="relative notifications-menu">
    <div class="notifications-icon cursor-pointer text-xl flex items-center space-x-2" onclick="toggleNotifications()">
        <i class="fa fa-bell"></i>
        <i class="fa fa-caret-down"></i> <!-- Flecha hacia abajo -->
         <!-- Tooltip -->
         <div class="tooltip hidden absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white px-3 py-1 rounded text-sm z-30">
            Notificaciones
        </div>
        <!-- Línea vertical al lado del icono de notificaciones -->
      

    </div>

      <!-- Menú desplegable de Notificaciones -->
      <div id="notifications-dropdown" class="hidden absolute right-0 mt-4 bg-white shadow-xl p-5 border border-gray-300 rounded-xl w-72 z-20 transition-all duration-300">
        <!-- Título -->
        <p class="text-gray-700 font-bold text-center mb-3">Notificaciones:</p>
        <hr class="border-gray-200 mb-3">
      
        <!-- Lista de Notificaciones -->
        <ul class="space-y-3">
          <li class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-all duration-200 cursor-pointer">
            <i class="fa fa-bell text-yellow-500 text-xl"></i>
            <span class="text-gray-800 font-medium">Notificación 1</span>
          </li>
          <li class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-all duration-200 cursor-pointer">
            <i class="fa fa-bell text-yellow-500 text-xl"></i>
            <span class="text-gray-800 font-medium">Notificación 2</span>
          </li>
          <li class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-all duration-200 cursor-pointer">
            <i class="fa fa-bell text-yellow-500 text-xl"></i>
            <span class="text-gray-800 font-medium">Notificación 3</span>
          </li>
        </ul>
      </div>
  </div>

  <!-- Ícono de Usuario -->
  <div class="relative user-menu">
    <div class="user-icon cursor-pointer text-xl flex items-center space-x-2" onclick="toggleUserOptions()">
        <i class="fa fa-user-circle"></i>
        <i class="fa fa-caret-down"></i> <!-- Flecha hacia abajo -->
    </div>

      <!-- Menú desplegable de Usuario -->
<div id="user-dropdown" class="hidden absolute right-0 mt-4 bg-white shadow-lg p-6 border rounded-lg w-64 z-10">
    <!-- Título con foto de perfil -->
    <div class="flex items-center justify-center mb-4">
        <img src="../public/img/about-1.jpg" alt="Foto de Perfil" class="w-16 h-16 rounded-full border-4 border-gray-300 shadow-lg">
    </div>
    <span class="block text-center text-lg font-semibold text-gray-700 mb-4">
        <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>
    </span>
    <hr class="border-gray-200 mb-4">

    <!-- Botones dinámicos -->
    <?php foreach ($menus_usuario as $menu): ?>
        <?php if ($menu['id_menu'] == 7): ?>
            <!-- Configuración con estilo amarillo -->
            <div>
                <a href="<?php echo htmlspecialchars($menu['url_menu']); ?>" class="flex items-center justify-center space-x-3 py-3 text-yellow-600 font-medium border border-yellow-500 rounded hover:bg-yellow-100 transition duration-200">
                    <i class="fa fa-cog"></i> <span><?php echo htmlspecialchars($menu['nombre_menu']); ?></span>
                </a>
            </div>
            <hr class="border-gray-200 my-4">
        <?php else: ?>
            <!-- Otros botones -->
            <ul class="space-y-3">
                <li>
                    <a href="<?php echo htmlspecialchars($menu['url_menu']); ?>" class="flex items-center justify-center space-x-3 py-2 text-gray-700 font-medium border rounded hover:bg-gray-100 hover:text-gray-900 transition duration-200">
                        <i class="fa <?php echo $menu['id_menu'] == 8 ? 'fa-user-circle' : 'fa-building'; ?>"></i>
                        <span><?php echo htmlspecialchars($menu['nombre_menu']); ?></span>
                    </a>
                </li>
                <?php if ($menu['id_menu'] == 8): ?>
                    <hr class="border-gray-300 my-2">
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    <?php endforeach; ?>

    <hr class="border-gray-200 my-4">

    <!-- Botón final: Salir -->
    <div>
        <a href="salir.php" class="flex items-center justify-center space-x-3 py-3 text-red-600 font-medium border border-red-500 rounded hover:bg-red-100 transition duration-200">
            <i class="fa fa-sign-out-alt"></i> <span>Salir</span>
        </a>
    </div>
</div>

</header>

<!-- Menú lateral -->
<div class="sidebar" id="sidebar">
<nav class="flex flex-col p-4 max-w-[300px]">
  <!-- Título del menú con fondo personalizado -->
  <h2 style="background-color: rgb(14, 113, 174);" class="text-lg font-bold text-white mb-4 flex items-center p-4 bg-[rgb(14,113,174)] w-full rounded-t-lg">
    <i class="fa fa-building mr-2"></i> Empresa:
    <!-- Botón de cierre como icono en la esquina superior derecha -->
    <button class="text-white text-xl ml-auto cursor-pointer hover:text-red-300" onclick="toggleSidebar()">
      <i class="fa fa-times"></i>
    </button>
  </h2>
  
  <!-- Submenús generados dinámicamente -->
 <?php foreach ($submenus_tipo_2 as $submenu): ?>
    <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
      <i class="<?php 
          echo $submenu['nombre_submenu'] === 'Sede' ? 'fa fa-map-marker-alt' :
               ($submenu['nombre_submenu'] === 'Sucursal' ? 'fa fa-store' :
               ($submenu['nombre_submenu'] === 'Almacén' ? 'fa fa-warehouse' :
               ($submenu['nombre_submenu'] === 'Planta' ? 'fa fa-industry' :
               ($submenu['nombre_submenu'] === 'Artículo' ? 'fa fa-box-open' : 'fa fa-question-circle')))); 
      ?> mr-2"></i> 
      <?php echo htmlspecialchars($submenu['nombre_submenu']); ?>
    </a>
<?php endforeach; ?>
</nav>
</div>
<hr>


   <section class="p-4">
    <h2 class="text-2xl font-bold mb-4">Lista de Entrega</h2>
    
 <!-- Formulario de filtro -->
<form method="GET" class="mb-4">
    <label for="id_status" class="mr-2">Filtrar por estado:</label>
    <select name="id_status" id="id_status" class="border rounded p-2">
        <option value="1" <?php echo isset($id_status) && $id_status == 1 ? 'selected' : ''; ?>>Entregando</option>
        <option value="2" <?php echo isset($id_status) && $id_status == 2 ? 'selected' : ''; ?>>Entregado</option>
    </select>
    <button type="submit" class="ml-2 bg-blue-500 text-white p-2 rounded">Filtrar</button>
</form>

<table class="min-w-full bg-white border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="py-2 px-4 border">ID</th>
            <th class="py-2 px-4 border">Fecha de Entrega/Recibida</th>
            <th class="py-2 px-4 border">Detalles de Movimiento</th>
            <th class="py-2 px-4 border">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($solicitudes as $solicitud): ?>
            <tr>
                <td class="py-2 px-4 border"><?php echo htmlspecialchars($solicitud['id_solicitud']); ?></td>
                <td class="py-2 px-4 border"><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?>/<?php echo htmlspecialchars($solicitud['fecha_recibido']); ?></td>
                <td class="py-2 px-4 border">
                    <p>ID Movimiento: <?php echo htmlspecialchars($solicitud['nombre_tipo']); ?></p>
                    <p>Descripción: <?php echo htmlspecialchars($solicitud['descripcion']); ?></p>
                </td>
                <td class="py-2 px-4 border">
                    <button class="bg-green-500 text-white p-2 rounded" 
                            onclick="verDetalles(<?php echo htmlspecialchars($solicitud['id_producto']); ?>, <?php echo htmlspecialchars($solicitud['id_solicitud']); ?>)">
                        Ver Entrega
                    </button>
                    
                    <!-- Formulario para actualizar inventario -->
                    <?php if (isset($id_status) && $id_status == 1): // Solo mostrar si el estado es "Entregando" ?>
                    <form action="actualizar_inventario_producto_entrega.php" method="POST" class="inline-block">
                        <input type="hidden" name="producto" value="<?php echo htmlspecialchars($solicitud['id_producto']); ?>">
                        <input type="hidden" name="solicitud" value="<?php echo htmlspecialchars($solicitud['id_solicitud']); ?>">
                        <input type="hidden" name="almacen" value="<?php echo htmlspecialchars($solicitud['id_almacen_destino']); ?>">
                        <input type="hidden" name="cantidad" value="<?php echo htmlspecialchars($solicitud['cantidad']); ?>">
                        <button type="submit" class="ml-2 bg-green-500 text-white p-2 rounded">Confirmar la Entrega</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Botón Volver -->
<div class="mt-4">
    <a href="inventario_producto.php" class="bg-gray-500 text-white p-2 rounded">Volver</a>
</div>


<!-- Modal -->
<div id="modalDetalles" class="modal hidden">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h2>Detalles de la Entrega</h2>
        <div id="contenidoModal"></div>
    </div>
</div>

<script>
function verDetalles(id_producto, id_solicitud) {
    // Realizar una petición AJAX para obtener los datos
    fetch(`ver_detalles_entrega_producto.php?id_solicitud=${id_solicitud}&id_producto=${id_producto}`)
        .then(response => response.json())
        .then(data => {
            // Verificar que se devuelvan datos
            if (data.length > 0) {
                const contenido = `
                    <p>Fecha de Entrega: ${data[0].fecha_solicitud}</p>
                    <p>Fecha Recibido: ${data[0].fecha_recibido}</p>
                    <p>Tipo de Solicitud: ${data[0].nombre_tipo}</p>
                    <p>Nombre de producto: ${data[0].nombre_producto}</p>
                     <p>Marca: ${data[0].nombre_marca}</p>
                      <p>Modelo: ${data[0].nombre_modelo}</p>
                    <p>Almacen Origen: ${data[0].origen}</p>
                    <p>Almacen Destino: ${data[0].destino}</p>
                    <p>Detalles del Movimiento:</p>
                    <ul>
                        ${data.map(item => `<li>${item.descripcion}</li>`).join('')}
                    </ul>
                `;
                document.getElementById('contenidoModal').innerHTML = contenido;

                // Mostrar el modal
                document.getElementById('modalDetalles').classList.remove('hidden');
            } else {
                alert('No se encontraron detalles para esta entrega.');
            }
        })
        .catch(error => {
            console.error('Error al obtener los datos:', error);
            alert('No se pudieron cargar los detalles. Intenta de nuevo más tarde.');
        });
}

function cerrarModal() {
    // Ocultar el modal
    document.getElementById('modalDetalles').classList.add('hidden');
}
</script>

<style>
.modal {
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    width: 300px;
}

.close {
    cursor: pointer;
    float: right;
    font-size: 20px;
}
.hidden {
    display: none;
}
</style>


<!-- ... (código existente) ... -->
</body>
</html>
