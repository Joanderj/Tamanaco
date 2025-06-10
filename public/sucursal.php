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
$pais = isset($_GET['pais']) ? $_GET['pais'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFinal = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';

// Clasificación
$orderBy = 'id_sucursal';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'fecha_asc':
            $orderBy = 'fecha_creacion ASC';
            break;
        case 'fecha_desc':
            $orderBy = 'fecha_creacion DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'nombre ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'nombre DESC';
            break;
        case 'numero_asc':
            $orderBy = 'id_sucursal ASC';
            break;
        case 'numero_desc':
            $orderBy = 'id_sucursal DESC';
            break;
    }
}

// Consulta total de elementos
$totalQuery = "SELECT COUNT(*) FROM sucursal WHERE 1=1";
$params = [];

if (!empty($nombre)) {
    $totalQuery .= " AND nombre LIKE ?";
    $params[] = '%' . $nombre . '%'; // Agregar wildcard para búsqueda
}

if (!empty($status)) {
    $totalQuery .= " AND id_status = ?";
    $params[] = $status;
}

if (!empty($estado)) {
    $totalQuery .= " AND estado_id_estado = ?";
    $params[] = $estado;
}

if (!empty($pais)) {
    $totalQuery .= " AND pais_id_pais = ?";
    $params[] = $pais;
}

// Agregar filtro por fechas
if (!empty($fechaInicio)) {
    $totalQuery .= " AND fecha_creacion >= ?";
    $params[] = $fechaInicio;
}
if (!empty($fechaFinal)) {
    $totalQuery .= " AND fecha_creacion <= ?";
    $params[] = $fechaFinal;
}

// Preparar y ejecutar la consulta total
$totalStmt = $conexion->prepare($totalQuery);
if (!empty($params)) {
    $totalStmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$totalStmt->execute();
$totalItems = $totalStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta de datos
$query = "SELECT * FROM sucursal WHERE 1=1"; // 1=1 para facilitar la concatenación de condiciones

if (!empty($nombre)) {
    $query .= " AND nombre LIKE ?";
}

if (!empty($status)) {
    $query .= " AND id_status = ?";
}

if (!empty($estado)) {
    $query .= " AND estado_id_estado = ?";
}

if (!empty($pais)) {
    $query .= " AND pais_id_pais = ?";
}

// Agregar filtro por fechas
if (!empty($fechaInicio)) {
    $query .= " AND fecha_creacion >= ?";
}

if (!empty($fechaFinal)) {
    $query .= " AND fecha_creacion <= ?";
}

// Agregar ordenamiento y límites
$query .= " ORDER BY $orderBy LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$sucursals = $result->fetch_all(MYSQLI_ASSOC);

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

// Cerrar la conexión
$conexion->close();

// Aquí puedes pasar los filtros guardados a tu vista
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Sucursal</title>
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
   <?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener datos del usuario
$usuario = $_SESSION['username'];
$query = $conexion->prepare("SELECT nombre_imagen, url FROM usuarios WHERE usuario = ?");
$query->bind_param("s", $usuario);
$query->execute();
$query->bind_result($nombre_imagen, $url_imagen);
$query->fetch();
$query->close();
$conexion->close();

// Si no tiene imagen, usar una por defecto
if (empty($url_imagen)) {
    $url_imagen = "servidor_img/perfil/default.jpg"; // Imagen por defecto
}
?>

<!-- Mostrar imagen de perfil -->
<div class="flex items-center justify-center mb-4">
    <?php if (!empty($url_imagen)): ?>
        <img src="<?php echo htmlspecialchars($url_imagen); ?>" alt="<?php echo htmlspecialchars($nombre_imagen); ?>" class="w-28 h-28 rounded-full border-2 border-blue-500 shadow-xl">
    <?php else: ?>
        <span>Sin Imagen</span>
    <?php endif; ?>
</div>

<!-- Mostrar nombre de usuario -->
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

<!-- Contenedor principal -->


<div class="p-6 bg-gray-50 rounded shadow-md">
<!-- Contenedor para los botones superiores -->
<div class="flex justify-start items-center space-x-4 mb-4">
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
    <?php foreach ($submenus_tipo_1 as $submenu): ?>
        <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
            <div class="flex flex-col items-center">
                <button class="<?php 
                    echo $submenu['nombre_submenu'] === 'Datos' ? 'bg-red-500 hover:bg-red-600' :
                         ($submenu['nombre_submenu'] === 'Contacto' ? 'bg-purple-500 hover:bg-purple-600' :
                         ($submenu['nombre_submenu'] === 'Redes Sociales' ? 'bg-green-500 hover:bg-green-600' :
                         ($submenu['nombre_submenu'] === 'Sobre Nosotros' ? 'bg-yellow-500 hover:bg-yellow-600' :
                         ($submenu['nombre_submenu'] === 'Blog' ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-500 hover:bg-gray-600')))); 
                ?> text-white w-16 h-16 rounded-full flex items-center justify-center">
                    <i class="<?php 
                        echo $submenu['nombre_submenu'] === 'Datos' ? 'fas fa-database' :
                             ($submenu['nombre_submenu'] === 'Contacto' ? 'fas fa-envelope' :
                             ($submenu['nombre_submenu'] === 'Redes Sociales' ? 'fas fa-share-alt' :
                             ($submenu['nombre_submenu'] === 'Sobre Nosotros' ? 'fas fa-info-circle' :
                             ($submenu['nombre_submenu'] === 'Blog' ? 'fas fa-blog' : 'fas fa-tasks')))); 
                    ?> text-xl"></i>
                </button>
                <span class="text-gray-700 text-sm mt-2"><?php echo htmlspecialchars($submenu['nombre_submenu']); ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<div class="container mx-auto px-4 py-6">
<!-- Modal para mostrar información de la marca -->
<div id="modalver" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden">
        <!-- Encabezado del modal -->
        <div class="px-6 py-4 flex justify-between items-center bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 text-white">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-tags mr-2"></i> Detalles de la Sucursal
            </h2>
            <button onclick="closeModal()" class="text-white text-lg hover:text-red-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Contenido del modal -->
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4">
                <i class="fas fa-store text-indigo-500 text-2xl"></i>
                <p class="text-lg font-medium text-gray-800">
                    <strong>Nombre de la Sucursal:</strong> <span id="sucursal"></span>
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-flag text-indigo-500 text-2xl"></i>
                <p class="text-lg font-medium text-gray-800">
                    <strong>Pais Perteneciente:</strong> <span id="pais"></span>
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-map text-indigo-500 text-2xl"></i>
                <p class="text-lg font-medium text-gray-800">
                    <strong>Estado Perteneciente:</strong> <span id="estado"></span>
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-map-marker-alt text-indigo-500 text-2xl"></i>
                <p class="text-lg font-medium text-gray-800">
                    <strong>Direccion de la Sucursal:</strong> <span id="direccion"></span>
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <i class="fas fa-calendar-alt text-indigo-500 text-2xl"></i>
                <p class="text-lg font-medium text-gray-800">
                    <strong>Fecha y  hora de Creacion:</strong> <span id="fecha"></span>
                </p>
            </div>
            <p class="mt-4 text-lg font-semibold text-gray-700">
                Sede Perteneciente:
            </p>
            <ul id="Relacionados" class="grid grid-cols-2 gap-2 mt-2">
                <!-- Modelos dinámicos -->
            </ul>
        </div>
        <!-- Pie del modal -->
        <div class="px-6 py-4 bg-gray-100 flex flex-col items-center space-y-4 rounded-lg shadow-sm">
    <div class="flex flex-row gap-3 justify-center items-center">
        <!-- Animación con diferentes retrasos -->
        <div class="w-4 h-4 rounded-full bg-blue-500 bounce-delay-0"></div>
        <div class="w-4 h-4 rounded-full bg-green-500 bounce-delay-1"></div>
        <div class="w-4 h-4 rounded-full bg-yellow-500 bounce-delay-2"></div>
    </div>
</div>
    </div>
</div>
</div>


</div>
 <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">

    <!-- Botones superiores -->
    <div class="flex justify-between items-center mb-4">
      <button onclick="window.location.href='empresa.php'" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
        <i class="fas fa-arrow-left"></i> Regresar
      </button>
      <div class="space-x-2">
        <button  onclick="window.location.href='formulario_sucursal.php'" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
          <i class="fas fa-user-plus"></i> Registrar
        </button>
        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
          <i class="fas fa-print"></i> Imprimir
        </button>
      </div>
    </div>
  <!-- Filtros -->
<div class="flex items-center space-x-4 mb-4 border border-gray-300 p-2 rounded-lg">
   <!-- Input estilizado -->
<input 
  type="text" 
  id="searchInput" 
  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
  placeholder="Buscar en la tabla..." 
/>



    <!-- Botón Filtrar -->
    <button 
      id="filterButton" 
      class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 flex items-center" 
      onclick="toggleFilterForm()">
      <i class="fas fa-filter mr-2"></i> Filtrar
    </button>
</div>
<!-- Formulario para filtros -->
<div id="filterForm" class="hidden mt-2 bg-white border border-gray-300 rounded shadow-lg p-4">
   <!-- Sección para mostrar filtros guardados -->
   <div id="savedFilters" class="mt-4">
        <h4 class="text-lg font-bold mb-2 text-indigo-500">Filtros Guardados</h4>
        <ul id="filtersList" class="list-disc pl-5"></ul>
    </div>
    <h3 class="text-lg font-bold mb-4 text-indigo-500">Opciones de Filtro</h3>
    <form id="filterOptionsForm">
        <!-- Campo: Nombre de sucursal -->
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 mb-2">Nombre de sucursal</label>
            <input 
              type="text" 
              id="nombre" 
              class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
              placeholder="Escribe el nombre de la sucursal..."
            />
        </div>

<!-- Campo: Status y pais Juntos -->
<div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Status -->
            <div>
                <label for="pais" class="block text-gray-700 mb-2">Pais</label>
                <select 
                  id="pais" 
                  name="pais"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona un pais</option>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

                    // Comprobar conexión
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Consultar las pais
                    $query = "SELECT id, paisnombre FROM pais ORDER BY paisnombre ASC";
                    $result = $conexion->query($query);

                    // Generar opciones dinámicamente
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = htmlspecialchars($row["id"]);
                            $nombre = htmlspecialchars($row["paisnombre"]);
                            echo "<option value='$id'>$nombre</option>";
                        }
                    } else {
                        echo "<option value=''>No hay paises disponibles</option>";
                    }

                    // Cerrar conexión
                    $conexion->close();
                    ?>
                </select>
            </div>
            <!-- pais -->
            <div>
                <label for="estado" class="block text-gray-700 mb-2">Estado</label>
                <select 
                  id="estado" 
                  name="estado"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona un Estado</option>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

                    // Comprobar conexión
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Consultar las pais
                    $query = "SELECT id, estadonombre FROM estado ORDER BY estadonombre ASC";
                    $result = $conexion->query($query);

                    // Generar opciones dinámicamente
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idestado = htmlspecialchars($row["id"]);
                            $nombreestado = htmlspecialchars($row["estadonombre"]);
                            echo "<option value='$idestado'>$nombreestado</option>";
                        }
                    } else {
                        echo "<option value=''>No hay estados disponibles</option>";
                    }

                    // Cerrar conexión
                    $conexion->close();
                    ?>
                </select>
            </div>
        </div>

        <!-- Campo: Status y pais Juntos -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Status -->
            <div>
                <label for="status" class="block text-gray-700 mb-2">Status</label>
                <select 
                  id="status" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona una opción</option>
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>
            </div>
            <!-- sede -->
            <div>
                <label for="sede" class="block text-gray-700 mb-2">Sede</label>
                <select 
                  id="sede" 
                  name="sede"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona una Sede</option>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

                    // Comprobar conexión
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Consultar las pais
                    $query = "SELECT id_sede, nombre_sede FROM sede";
                    $result = $conexion->query($query);

                    // Generar opciones dinámicamente
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idsede = htmlspecialchars($row["id_sede"]);
                            $nombresede = htmlspecialchars($row["nombre_sede"]);
                            echo "<option value='$idsede'>$nombresede</option>";
                        }
                    } else {
                        echo "<option value=''>No hay sede disponibles</option>";
                    }

                    // Cerrar conexión
                    $conexion->close();
                    ?>
                </select>
            </div>
        </div>

        <!-- Campo: Fechas Juntas -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Fecha Inicio -->
            <div>
                <label for="fechaInicio" class="block text-gray-700 mb-2">Fecha Inicio</label>
                <input 
                  type="date" 
                  id="fechaInicio" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                />
            </div>
            <!-- Fecha Final -->
            <div>
                <label for="fechaFinal" class="block text-gray-700 mb-2">Fecha Final</label>
                <input 
                  type="date" 
                  id="fechaFinal" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                />
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between mt-4">
            <div class="flex space-x-2">
                <!-- Botón Aplicar Filtro -->
                <button 
                  type="button" 
                  class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600"
                  onclick="applyFilters()"
                >
                    Aplicar Filtro
                </button>
                <!-- Botón Borrar Filtro -->
                <button 
                  type="reset" 
                  class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-50"
                >
                    Borrar Filtro
                </button>
            </div>
            <!-- Botón Guardar Filtro -->
            <button 
              type="button" 
              class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50"
              onclick="showSaveFilterModal()"
            >
                Guardar Filtro
            </button>
        </div>
    </form>
</div>

<!-- Modal para guardar filtro -->
<div id="saveFilterModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
    <div class="bg-white rounded-lg p-6">
        <h3 class="text-lg font-bold mb-4">Nombre del Filtro</h3>
        <input type="text" id="filterName" class="border border-gray-300 rounded p-2 w-full" placeholder="Escribe un nombre para el filtro..." />
        <div class="flex justify-end mt-4">
            <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600" onclick="saveFilter()">Guardar</button>
            <button class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-50" onclick="closeSaveFilterModal()">Cancelar</button>
        </div>
    </div>
</div>


<!-- Contenedor principal con línea alrededor -->
<div class="border border-gray-300 rounded-lg shadow-md p-4">
    <!-- Encabezado encima de la tabla -->
    <div class="flex justify-between items-center mb-4 border-b border-gray-300 pb-2">
        <h1 class="text-xl font-semibold text-gray-700">Lista de sucursal</h1>
        <div class="relative">
            <select id="clasificacion" 
                    onchange="window.location.href='?sort=' + this.value + '&itemsPerPage=<?php echo $itemsPerPage; ?>&page=1'" 
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center appearance-none">
                <option value="" disabled selected>Clasificar</option>
                <option value="normal">Normal</option>
                <option value="fecha_asc">Por fecha más antigua</option>
                <option value="fecha_desc">Por fecha más reciente</option>
                <option value="nombre_asc">Por nombre (A-Z)</option>
                <option value="nombre_desc">Por nombre (Z-A)</option>
                <option value="numero_asc">Por número más bajo</option>
                <option value="numero_desc">Por número más alto</option>
            </select>
            <i class="fas fa-sort absolute right-4 top-3 text-gray-700 pointer-events-none"></i>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full border-collapse border border-gray-300 shadow-lg rounded-lg">
            <thead class="bg-indigo-500 text-white">
                <tr>
                    <th class="px-6 py-3 text-left font-medium">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-hashtag"></i>
                            <span>ID</span>
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left font-medium">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-store"></i>
                            <span>Nombre de la Sucursal</span>
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left font-medium">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Direccion</span>
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left font-medium">
                        <span class="flex items-center space-x-2">
                            <i class="fas fa-tools"></i>
                            <span>Acciones</span>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody id="dataTable" class="bg-white divide-y divide-gray-200">
                <?php foreach ($sucursals as $sucursal): ?>
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-4"><?php echo $sucursal['id_sucursal']; ?></td>
                    <td class="px-6 py-4"><?php echo $sucursal['nombre']; ?></td>
                    <td class="px-6 py-4"><?php echo $sucursal['direccion']; ?></td>
                    <td class="px-6 py-4 flex space-x-2">
                    <button 
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        onclick="showModal(<?php echo $sucursal['id_sucursal']; ?>)">
        <i class="fas fa-eye"></i> Ver
    </button>
    <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" 
    onclick="window.location.href='formulario_modificar_sucursal.php?id_sucursal=<?php echo $sucursal['id_sucursal']; ?>'">
    <i class="fas fa-edit"></i> Modificar
</button>
                        <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 flex items-center justify-between">
        <div class="flex-1"></div>
        <div class="flex space-x-2">
            <a href="?page=<?php echo max(1, $currentPage - 1); ?>&itemsPerPage=<?php echo $itemsPerPage; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>" class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Anterior
            </a>

            <?php
            // Mostrar números de página
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo "<button class='bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500'>$i</button>";
                } elseif ($i <= 3 || $i > $totalPages - 3 || abs($currentPage - $i) < 2) {
                    echo "<a href='?page=$i&itemsPerPage=$itemsPerPage&sort=" . (isset($_GET['sort']) ? $_GET['sort'] : '') . "' class='border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500'>$i</a>";
                } elseif ($i == 4 && $currentPage > 5) {
                    echo "<span class='text-gray-500'>...</span>";
                }
            }
            ?>

            <a href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&itemsPerPage=<?php echo $itemsPerPage; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>" class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Siguiente
            </a>
        </div>

        <!-- Elementos por página a la derecha -->
        <div class="flex items-center space-x-2 flex-1 justify-end">
            <label for="itemsPerPage" class="text-gray-600">Elementos por página:</label>
            <select id="itemsPerPage" onchange="window.location.href='?itemsPerPage=' + this.value + '&page=1&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>'" class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50">
                <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $itemsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                <option value="500" <?php echo $itemsPerPage == 500 ? 'selected' : ''; ?>>500</option>
                <option value="1000" <?php echo $itemsPerPage == 1000 ? 'selected' : ''; ?>>1000</option>
            </select>
            <span class="text-gray-600">Mostrando <?php echo $offset + 1; ?>-<?php echo min($offset + $itemsPerPage, $totalItems); ?> de <?php echo $totalItems; ?></span>
        </div>
    </div>
</div>




  </div>
  <script>
   // Mostrar/ocultar el formulario de filtros
function toggleFilterForm() {
    const form = document.getElementById('filterForm');
    form.classList.toggle('hidden');
}

// Función para aplicar filtros
function applyFilters() {
    const nombre = document.getElementById('nombre').value;
    const pais = document.getElementById('pais').value;
    const estado = document.getElementById('estado').value;
    const status = document.getElementById('status').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFinal = document.getElementById('fechaFinal').value;

    // Redirigir a la misma página con los filtros aplicados
    window.location.href = `?nombre=${encodeURIComponent(nombre)}&status=${status}&pais=${pais}&estado=${estado}&fechaInicio=${fechaInicio}&fechaFinal=${fechaFinal}`;
}

// Función para filtrar la tabla en tiempo real
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('dataTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let rowContainsFilter = false;

        for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
                const cellValue = cells[j].textContent || cells[j].innerText;
                if (cellValue.toLowerCase().indexOf(filter) > -1) {
                    rowContainsFilter = true;
                    break;
                }
            }
        }

        if (rowContainsFilter) {
            rows[i].style.display = ""; // Mostrar la fila
        } else {
            rows[i].style.display = "none"; // Ocultar la fila
        }
    }
}

// Asignar evento al campo de búsqueda
document.getElementById('searchInput').addEventListener('keyup', filterTable);

  </script>
  <script>
  // Captura el evento de presionar una tecla
  document.getElementById('searchInput').addEventListener('keypress', function(event) {
    // Verifica si la tecla presionada es "Enter"
    if (event.key === 'Enter') {
      // Obtén el valor del input
      const searchValue = this.value;
      // Redirige a la misma página con el parámetro de búsqueda
      window.location.href = window.location.pathname + '?nombre=' + encodeURIComponent(searchValue);
    }
  });
</script>
  <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }
  
    function toggleNotifications() {
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        notificationsDropdown.classList.toggle('hidden');
    }
  
    function toggleUserOptions() {
        const userDropdown = document.getElementById('user-dropdown');
        userDropdown.classList.toggle('hidden');
    }
  </script>
  <script>
    function showModal(valor) {
        fetch(`getsucursal_sede.php?id_sucursal=${valor}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('sucursal').textContent = data.nombre;
                document.getElementById('fecha').textContent = data.creacion;
                document.getElementById('direccion').textContent = data.direccion;
                document.getElementById('pais').textContent = data.pais;
                document.getElementById('estado').textContent = data.estado;

                const Relacionados = document.getElementById('Relacionados');
                Relacionados.innerHTML = ''; // Limpiar contenido previo

                data.sucursal.forEach((modelo, index) => {
                    // Crear un elemento <li> para cada modelo relacionado
                    const liModal = document.createElement('li');
                    liModal.className = "flex items-center space-x-2 bg-gray-100 p-2 rounded shadow-md";

                    // Agregar número del modelo
                    const numero = document.createElement('span');
                    numero.className = "text-indigo-500 font-bold";
                    numero.textContent = `${index + 1}.`;

                    // Agregar ícono decorativo
                    const icono = document.createElement('i');
                    icono.className = "fas fa-map-marker-alt text-indigo-500";

                    // Agregar nombre del modelo
                    const nombre = document.createElement('span');
                    nombre.className = "text-gray-700 font-medium";
                    nombre.textContent = modelo;

                    // Agregar los elementos al <li>
                    liModal.appendChild(numero);
                    liModal.appendChild(icono);
                    liModal.appendChild(nombre);

                    // Agregar el <li> al <ul> de modelos relacionados
                    Relacionados.appendChild(liModal);
                });

                const modal = document.getElementById('modalver');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            })
            .catch(error => console.error('Error al obtener los datos:', error));
    }

    function closeModal() {
        const modal = document.getElementById('modalver');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
</body>
</html>



