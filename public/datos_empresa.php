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

 
$sql = "SELECT * FROM empresa WHERE id_empresa=1";
$result = $conexion->query($sql);
$empresa = $result->fetch_assoc();

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

// Submenú actual: Sede (id_submenu = 6)
$submenu_actual = 6;

// Verificar si el submenú "Sede" está activo y si el perfil tiene permisos
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
$nombreSede = isset($_GET['nombreSede']) ? $_GET['nombreSede'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sucursal = isset($_GET['sucursal']) ? $_GET['sucursal'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFinal = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';

// Clasificación
$orderBy = 'id_sede';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'fecha_asc':
            $orderBy = 'fecha_creacion ASC';
            break;
        case 'fecha_desc':
            $orderBy = 'fecha_creacion DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'nombre_sede ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'nombre_sede DESC';
            break;
        case 'numero_asc':
            $orderBy = 'id_sede ASC';
            break;
        case 'numero_desc':
            $orderBy = 'id_sede DESC';
            break;
    }
}

// Consulta total de elementos
$totalQuery = "SELECT COUNT(*) FROM sede WHERE 1=1";
$params = [];

if (!empty($nombreSede)) {
    $totalQuery .= " AND nombre_sede LIKE ?";
    $params[] = '%' . $nombreSede . '%'; // Agregar wildcard para búsqueda
}

if (!empty($status)) {
    $totalQuery .= " AND id_status = ?";
    $params[] = $status;
}

if (!empty($sucursal)) {
    $totalQuery .= " AND id_sucursal_fija = ?";
    $params[] = $sucursal;
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
$query = "SELECT * FROM sede WHERE 1=1"; // 1=1 para facilitar la concatenación de condiciones

if (!empty($nombreSede)) {
    $query .= " AND nombre_sede LIKE ?";
}

if (!empty($status)) {
    $query .= " AND id_status = ?";
}

if (!empty($sucursal)) {
    $query .= " AND id_sucursal_fija = ?";
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
$sedes = $result->fetch_all(MYSQLI_ASSOC);

// Guardar Filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_filter') {
    $filterName = $_POST['filterName'];
    
    // Crear criterios como un JSON
    $criterios = json_encode([
        'nombreSede' => $nombreSede,
        'status' => $status,
        'sucursal' => $sucursal,
        'fechaInicio' => $fechaInicio,
        'fechaFinal' => $fechaFinal,
    ]);

    $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado, usuario_id_filtro) 
              VALUES (?, 'sede', ?, NOW(), ?)";
    
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


</div>
 <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
 <div class="container mx-auto max-w-4xl p-8 bg-white rounded-lg shadow-md">
  <!-- Título -->
  <div class="flex flex-col items-center mb-6">
    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
        <i class="fas fa-building text-3xl"></i>
    </div>
    <h2 class="text-3xl font-extrabold text-gray-800">Datos de la Empresa</h2>
    <p class="text-gray-600 mt-2 text-center">Consulta y actualiza los datos generales de la empresa.</p>
</div>

  
  <?php if (isset($_SESSION['mensaje'])) {
  echo "<p style='color: green;'>{$_SESSION['mensaje']}</p>";
  unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo
}

// Mostrar mensaje de error si existe
if (isset($_SESSION['error'])) {
  echo "<p style='color: red;'>{$_SESSION['error']}</p>";
  unset($_SESSION['error']); // Eliminar el error después de mostrarlo
}
?>

  <!-- Formulario -->
  <form action="guardar_cambios_empresa.php" method="POST" enctype="multipart/form-data">
  <div class="text-center mb-6">
        <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center">
            <!-- Input para cargar imagen -->
            <input type="file" id="imagen" name="nombre_imagen" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="mostrarCropper()"/>
            <!-- Previsualización -->
            <img id="imagen-preview" src="" alt="Previsualización de la imagen" class="absolute inset-0 w-full h-full object-cover rounded-lg hidden" />
            <div id="imagen-placeholder" class="text-center">
                <i class="fas fa-building text-3xl text-blue-500"></i> <!-- Ícono de engranajes -->
                <p class="text-blue-500 font-medium">Haga clic para subir una foto</p>
                <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
            </div>
        </div>
  </div>
    <!-- Nombre de la Empresa -->
    <div class="mb-4 relative">
      <label for="nombre" class="block font-medium text-gray-600">Nombre de la Empresa:</label>
      <input type="text" id="nombre" name="nombre" value="<?php echo $empresa['nombre']; ?>" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
      <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
        <i class="fas fa-pencil-alt">Editar </i>
      </button>
    </div>

    <!-- Letra y Número de RIF -->
    <div class="grid grid-cols-3 gap-4 mb-4">
      <div class="relative">
        <label for="letraRif" class="block font-medium text-gray-600">Letra del RIF:</label>
        <input type="text" id="letraRif" name="letrarif" value="<?php echo $empresa['rif']; ?>" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
      <div class="relative col-span-2">
        <label for="numeroRif" class="block font-medium text-gray-600">Número del RIF:</label>
        <input type="text" id="numeroRif" name="numerorif" value="<?php echo $empresa['numero_rif']; ?>" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
    </div>

    <!-- País y Estado -->
    <div class="grid grid-cols-2 gap-4 mb-4">
      <div class="relative">
        <label for="pais" class="block font-medium text-gray-600">País:</label>
        <select id="pais" name="pais" value='1' class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled>
        <option value="">Selecciona un pais</option>
            <?php
             // Conexión a la base de datos
             $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
             if ($conexion->connect_error) {
                 die("Conexión fallida: " . $conexion->connect_error);
             }
            $query = "SELECT * FROM pais";
            $resultado = $conexion->query($query);
            while ($fila = $resultado->fetch_assoc()) {
                echo "<option value='" . $fila['id'] . "'>" . $fila['paisnombre'] . "</option>";
            }
            ?>
        </select>
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
      <div class="relative">
        <label for="estado" class="block font-medium text-gray-600">Estado:</label>
        <select id="estado" name="estado" value='1' class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled>
        <option value="">Selecciona un Estado</option>
        </select>
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
    </div>

    <!-- Dirección -->
    <div class="mb-4 relative">
      <label for="direccion" class="block font-medium text-gray-600">Dirección:</label>
      <textarea id="direccion" name=direccion rows="3" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled><?php echo $empresa['direccion']; ?></textarea>
      <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
        <i class="fas fa-pencil-alt"></i>
      </button>
    </div>

    <!-- Tipo de Empresa -->
    <div class="mb-4 relative">
      <label for="tipoEmpresa" class="block font-medium text-gray-600">Tipo de Empresa:</label>
      <select id="tipoEmpresa" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled>
        <option>Servicios</option>
        <option>Comercial</option>
        <option>Manufactura</option>
      </select>
      <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500">
        <i class="fas fa-pencil-alt"></i>
      </button>
    </div>

    <!-- Botón Guardar -->
    <div class="flex justify-center mt-6">
    <button type="button" class="bg-gray-500 text-white px-6 py-3 rounded-lg shadow hover:bg-gray-600 transition duration-300 mr-4" onclick="window.history.back();">
      Regresar
    </button>
    <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
    Guardar Cambios
    </button>
    </div>
  </form>
</div>
</div>
<script>
  // Seleccionar todos los íconos de lápiz en el formulario
  const editButtons = document.querySelectorAll('.fa-pencil-alt');

  // Añadir un evento click a cada botón de edición
  editButtons.forEach(button => {
    button.addEventListener('click', (event) => {
      // Obtener el input, textarea o select relacionado con el botón
      const field = event.target.closest('div').querySelector('input, textarea, select');
      if (field) {
        // Desbloquear el campo
        field.disabled = false;

        // Estilizar el campo para indicar que está en modo edición
        field.classList.remove('bg-gray-100');
        field.classList.add('bg-white', 'border-blue-500', 'focus:ring', 'focus:ring-blue-300');
        field.focus(); // Poner el cursor automáticamente en el campo
      }
    });
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
    document.getElementById('imagen').onchange = function(evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('imagen-preview').src = URL.createObjectURL(file);
            document.getElementById('imagen-preview').classList.remove('hidden');
            document.getElementById('imagen-placeholder').classList.add('hidden');
        }
    };
</script>
</body>
</html>




<script>
 document.getElementById("pais").addEventListener("change", function () {
    let paisId = this.value;

    fetch("obtener_estados.php", {
        method: "POST",
        body: new URLSearchParams({ pais_id: paisId }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(response => response.text())  // Cambiar .json() a .text() para ver errores
    .then(data => {
        console.log(data);  // Muestra la respuesta en la consola para debug
        document.getElementById("estado").innerHTML = data;
    });
});
</script>