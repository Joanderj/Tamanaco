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
$menu_actual = 1;

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

// Submenú actual: Sede (id_submenu = 8)
$submenu_actual = 11;

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
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 7
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
// Variables para paginación
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Recuperar los filtros
$nombreMarca = isset($_GET['nombreMarca']) ? $_GET['nombreMarca'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$modeloSeleccionado = isset($_GET['modelo']) ? $_GET['modelo'] : ''; // Modelo filtrado
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFinal = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';

// Clasificación
$orderBy = 'id_marca';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'fecha_asc':
            $orderBy = 'fecha_creacion ASC';
            break;
        case 'fecha_desc':
            $orderBy = 'fecha_creacion DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'nombre_marca ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'nombre_marca DESC';
            break;
        case 'numero_asc':
            $orderBy = 'id_marca ASC';
            break;
        case 'numero_desc':
            $orderBy = 'id_marca DESC';
            break;
    }
}

// Consulta total de elementos (sin duplicados)
$totalQuery = "
    SELECT COUNT(DISTINCT m.id_marca) 
    FROM marca m
    INNER JOIN marca_modelo mm ON m.id_marca = mm.id_marca
    WHERE 1=1
";
$params = [];

// Agregar filtros
if (!empty($modeloSeleccionado)) {
    $totalQuery .= " AND mm.id_modelo = ?";
    $params[] = $modeloSeleccionado;
}
if (!empty($nombreMarca)) {
    $totalQuery .= " AND m.nombre_marca LIKE ?";
    $params[] = '%' . $nombreMarca . '%';
}
if (!empty($status)) {
    $totalQuery .= " AND m.id_status = ?";
    $params[] = $status;
}
if (!empty($fechaInicio)) {
    $totalQuery .= " AND m.fecha_creacion >= ?";
    $params[] = $fechaInicio;
}
if (!empty($fechaFinal)) {
    $totalQuery .= " AND m.fecha_creacion <= ?";
    $params[] = $fechaFinal;
}

// Ejecutar consulta total
$totalStmt = $conexion->prepare($totalQuery);
if (!empty($params)) {
    $totalStmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$totalStmt->execute();
$totalItems = $totalStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta principal de datos (marcas únicas con paginación)
$query = "
    SELECT DISTINCT m.id_marca, m.nombre_marca, m.fecha_creacion, m.id_status
    FROM marca m
    INNER JOIN marca_modelo mm ON m.id_marca = mm.id_marca
    WHERE 1=1
";

// Agregar filtros
if (!empty($modeloSeleccionado)) {
    $query .= " AND mm.id_modelo = ?";
}
if (!empty($nombreMarca)) {
    $query .= " AND m.nombre_marca LIKE ?";
}
if (!empty($status)) {
    $query .= " AND m.id_status = ?";
}
if (!empty($fechaInicio)) {
    $query .= " AND m.fecha_creacion >= ?";
}
if (!empty($fechaFinal)) {
    $query .= " AND m.fecha_creacion <= ?";
}

// Agregar ordenamiento y límites
$query .= " ORDER BY $orderBy LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Preparar y ejecutar la consulta de datos
$stmt = $conexion->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$marcas = $result->fetch_all(MYSQLI_ASSOC);
$conexion->close();
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
        <link href="css/quill.snow.css" rel="stylesheet">
       <!-- js -->
       <script src="../public/js/chart.js"></script>
       <style>
        /* Animación personalizada */
        .card {
            width: 200px;
            height: 280px;
            background: #fff;
            border-top-right-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            box-shadow: 0 14px 26px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease-out;
            text-decoration: none;
            margin: 0 auto;
        }

        .card:hover {
            transform: translateY(-5px) scale(1.005) translateZ(0);
            box-shadow: 0 24px 36px rgba(0, 0, 0, 0.11),
            0 24px 46px var(--box-shadow-color);
        }

        .card:hover .overlay {
            transform: scale(4) translateZ(0);
        }

        .card:hover .circle {
            border-color: var(--bg-color-light);
            background: var(--bg-color);
        }

        .card:hover .circle:after {
            background: var(--bg-color-light);
        }

        .card:hover p {
            color: var(--text-color-hover);
        }

        .card p {
            font-size: 17px;
            color: #4c5656;
            margin-top: 20px;
            z-index: 1000;
            transition: color 0.3s ease-out;
        }

        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease-out;
        }
        .circle i {
            font-size: 40px;
            color: white;
            position: relative;
            z-index: 10;
            transition: color 0.3s ease-out;
        }
        .circle:after {
            content: "";
            width: 90px;
            height: 90px;
            display: block;
            position: absolute;
            background: var(--bg-color);
            border-radius: 50%;
            top: 5px;
            left: 5px;
            transition: opacity 0.3s ease-out;
        }

        .overlay {
            width: 90px;
            position: absolute;
            height: 90px;
            border-radius: 50%;
            background: var(--bg-color);
            top: 50px;
            left: 50px;
            z-index: 0;
            transition: transform 0.3s ease-out;
        }

        /* Colores personalizados por tipo */
        .marca { --bg-color: #ceb2fc; --bg-color-light: #f0e7ff; --text-color-hover: #fff; --box-shadow-color: rgba(206, 178, 252, 0.48); }
        .modelo { --bg-color: #a5d8ff; --bg-color-light: #d6f2ff; --text-color-hover: #fff; --box-shadow-color: rgba(165, 216, 255, 0.48); }
        .tipo { --bg-color: #ffd700; --bg-color-light: #fffacd; --text-color-hover: #fff; --box-shadow-color: rgba(255, 215, 0, 0.48); }
        .clasificacion { --bg-color: #ffa07a; --bg-color-light: #ffdab9; --text-color-hover: #fff; --box-shadow-color: rgba(255, 160, 122, 0.48); }
        .producto { --bg-color: #ff7373; --bg-color-light: #ffb6b6; --text-color-hover: #fff; --box-shadow-color: rgba(255, 115, 115, 0.48); }
        .maquina { --bg-color: #98fb98; --bg-color-light: #d3fadb; --text-color-hover: #fff; --box-shadow-color: rgba(152, 251, 152, 0.48); }
        .repuesto { --bg-color: #6a5acd; --bg-color-light: #e6e6fa; --text-color-hover: #fff; --box-shadow-color: rgba(106, 90, 205, 0.48); }
        .proveedor { --bg-color: #ffa500; --bg-color-light: #ffd580; --text-color-hover: #fff; --box-shadow-color: rgba(255, 165, 0, 0.48); }
        .servicio { --bg-color: #ff69b4; --bg-color-light: #ffb6c1; --text-color-hover: #fff; --box-shadow-color: rgba(255, 105, 180, 0.48); }
        .cargo { --bg-color: #c0c0c0; --bg-color-light: #dcdcdc; --text-color-hover: #fff; --box-shadow-color: rgba(192, 192, 192, 0.48); }
    </style>

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

  /* Reutilizamos la animación 'bounce' de Tailwind */
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Sin retraso */
.bounce-delay-0 {
    animation: bounce 1s infinite;
}

/* Retraso de 0.3s */
.bounce-delay-1 {
    animation: bounce 1s infinite;
    animation-delay: 0.3s;
}

/* Retraso de 0.6s */
.bounce-delay-2 {
    animation: bounce 1s infinite;
    animation-delay: 0.6s;
}
    </style>
</head>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Leer el mensaje de éxito desde la sesión
$mensaje_exito = isset($_SESSION['mensaje_exito']) ? $_SESSION['mensaje_exito'] : "";

// Limpiar el mensaje de éxito después de mostrarlo
unset($_SESSION['mensaje_exito']);
?>

<!-- Mostrar mensaje de éxito si existe -->
<?php if (!empty($mensaje_exito)): ?>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-green-100 p-4 rounded-full shadow-lg animate-pulse">
                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-bold text-green-600 mb-2">¡Éxito!</h2>
                <p class="text-gray-700"><?= htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" 
                    class="absolute top-2 right-2 bg-green-500 hover:bg-green-600 text-white rounded-full p-2 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>
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
            <i class="fa fa-cogs mr-2"></i> Configuración:
            <!-- Botón de cierre como icono en la esquina superior derecha -->
            <button class="text-white text-xl ml-auto cursor-pointer hover:text-red-300" onclick="toggleSidebar()">
                <i class="fa fa-times"></i>
            </button>
        </h2>
        <nav>
            <?php 
            foreach ($submenus_tipo_1 as $submenu): 
                // Define un ícono para cada submenú basado en el nombre
                $icono = 'fas fa-link'; // Ícono por defecto
                switch ($submenu['nombre_submenu']) {
                    case 'Marca':
                        $icono = 'fas fa-tags';
                        break;
                    case 'Modelo':
                        $icono = 'fas fa-shapes';
                        break;
                    case 'Tipo':
                        $icono = 'fas fa-cube';
                        break;
                    case 'Clasificacion':
                        $icono = 'fas fa-list-alt';
                        break;
                    case 'Producto':
                        $icono = 'fas fa-box';
                        break;
                    case 'Máquina':
                        $icono = 'fas fa-industry';
                        break;
                    case 'Repuesto':
                        $icono = 'fas fa-cogs';
                        break;
                    case 'Proveedor':
                        $icono = 'fas fa-truck';
                        break;
                    case 'Servicio':
                        $icono = 'fas fa-concierge-bell';
                        break;
                    case 'Cargo':
                        $icono = 'fas fa-user-tie';
                        break;
                }
            ?>
                <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
                    <i class="<?php echo htmlspecialchars($icono); ?> mr-2"></i> <?php echo htmlspecialchars($submenu['nombre_submenu']); ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </nav>
</div>
        
</div>

<hr>
<div class="p-6 bg-gray-50 ">
  <?php
include 'db_connection.php';

$id_tarea = intval($_GET['id'] ?? 0);

// Mostrar mensaje si fue guardado
if (isset($_GET['msg']) && $_GET['msg'] === 'guardado') {
    echo "
    <div class='mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-md'>
        <div class='flex items-center space-x-3'>
            <i class='fas fa-check-circle text-green-600 text-xl'></i>
            <div>
                <p class='font-semibold'>¡Tarea registrada con éxito!</p>
                <p class='text-sm text-green-700'>ID generado: <span class='font-mono bg-green-100 px-2 py-0.5 rounded'>$id_tarea</span></p>
            </div>
        </div>
    </div>";
}

// Consulta principal de la tarea, incluyendo más información de la máquina y la sede
$stmt = $conn->prepare("
    SELECT 
        t.*, 
        m.nombre_maquina, 
        m.url, 
        mo.nombre_modelo, 
        ma.nombre_marca, 
        tm.nombre_tipo, 
        tmto.nombre_tipo AS nombre_tipo_mantenimiento, 
        s.nombre_sede, 
        mu.CodigoUnico
    FROM tareas t
    LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
    LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
    LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN marca ma ON m.id_marca = ma.id_marca
    LEFT JOIN tipo tm ON m.id_tipo = tm.id_tipo
    LEFT JOIN tipo_mantenimiento tmto ON t.tipo_mantenimiento_id = tmto.id_tipo
    LEFT JOIN sede s ON mu.id_sede = s.id_sede
    WHERE t.id_tarea = ?
");
$stmt->execute([$id_tarea]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    echo "<div class='text-red-600 font-semibold'>Tarea no encontrada.</div>";
    exit;
}

// Función reutilizable para obtener datos relacionados
function getRelacionados($conn, $tabla, $campo_tarea, $id_tarea) {
    $stmt = $conn->prepare("SELECT * FROM $tabla WHERE $campo_tarea = ?");
    $stmt->execute([$id_tarea]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener herramientas relacionadas con cantidad y status
$stmt = $conn->prepare("
    SELECT h.*, ht.cantidad, s.nombre_status 
    FROM herramienta_tarea ht
    JOIN herramientas h ON ht.herramienta_id = h.id_herramienta
    LEFT JOIN status s ON ht.status_id = s.id_status
    WHERE ht.tarea_id = ?
");
$stmt->execute([$id_tarea]);
$herramientas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos relacionados
$stmt = $conn->prepare("
    SELECT p.*, pt.cantidad, s.nombre_status 
    FROM producto_tarea pt
    JOIN producto p ON pt.producto_id = p.id_producto
    LEFT JOIN status s ON pt.status_id = s.id_status
    WHERE pt.tarea_id = ?
");
$stmt->execute([$id_tarea]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener repuestos relacionados
$stmt = $conn->prepare("
    SELECT r.*, rt.cantidad, s.nombre_status 
    FROM repuesto_tarea rt
    JOIN repuesto r ON rt.repuesto_id = r.id_repuesto
    LEFT JOIN status s ON rt.status_id = s.id_status
    WHERE rt.tarea_id = ?
");
$stmt->execute([$id_tarea]);
$repuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Barra de acciones superior -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8 bg-white border border-gray-200  shadow-lg px-6 py-4">
    <!-- Encabezado de la barra de acciones -->
    <div class="flex items-center gap-4">
        <span class="text-lg font-bold text-gray-800">
            <i class="fas fa-tasks text-blue-600 mr-2"></i>
            Tarea
        </span>
        <span class="ml-4 text-sm font-semibold text-gray-600">
     
            <span class="inline-block px-2 py-1 rounded 
                <?php
                    $status = isset($tarea['id_status']) ? intval($tarea['id_status']) : 0;
                    // Puedes personalizar los colores según tus status
                    if ($status === 1) echo 'bg-green-100 text-green-700';
                    elseif ($status === 2) echo 'bg-yellow-100 text-yellow-700';
                    elseif ($status === 3) echo 'bg-red-100 text-red-700';
                    else echo 'bg-gray-200 text-gray-700';
                ?>">
                <?= htmlspecialchars($tarea['nombre_status'] ?? 'Desconocido') ?>
            </span>
        </span>
    </div>
    <!-- Botones de Acción compactos -->
    <div class="flex flex-wrap gap-2">
        <a href="tareas.php"
           class="flex items-center gap-1 bg-gray-700 hover:bg-gray-800 text-white px-3 py-2 rounded-lg shadow text-xs font-medium transition duration-200">
            <i class="fas fa-arrow-left"></i>
            Regresar
        </a>
        <a href="formulario_finalizar_mantenimiento.php?id_tarea=<?= intval($_GET['id']) ?>"
            class="flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg shadow text-xs font-medium transition duration-200">
             <i class="fas fa-flag-checkered"></i>
             Finalizar mantenimiento
        </a>
        <?php if (!empty($_GET['id'])): ?>
            <a href="comprar_tarea.php?id_tarea=<?= intval($_GET['id']) ?>"
               class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg shadow text-xs font-medium transition duration-200">
                <i class="fas fa-shopping-cart"></i>
                Comprar pendientes
            </a>
        <?php endif; ?>
        <a href="exportar_tarea_excel.php?id_tarea=<?= intval($_GET['id']) ?>"
           class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg shadow text-xs font-medium transition duration-200">
            <i class="fas fa-file-excel"></i>
            Excel
        </a>
        <a href="exportar_tarea_pdf.php?id_tarea=<?= intval($_GET['id']) ?>"
           class="flex items-center gap-1 bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg shadow text-xs font-medium transition duration-200">
            <i class="fas fa-file-pdf"></i>
            PDF
        </a>
    </div>
    </div>
</div>
<div class="max-w-7xl mx-auto p-6">
<div class="flex flex-col md:flex-row gap-8">

    <!-- Columna Izquierda: Información de la Tarea -->
    <div class="w-full md:w-2/3 bg-white border border-gray-200  shadow-lg p-8 space-y-6">

        <!-- Identificador Único -->
        <div class="flex items-center gap-3 justify-between">
            <div class="flex items-center gap-3">
            <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-id-badge text-blue-600 text-xl"></i>
            </div>
            <div>
                <span class="block text-xs text-gray-500 font-semibold uppercase">ID Único</span>
                <span class="text-lg font-bold text-gray-800">#<?= $tarea['id_tarea'] ?></span>
            </div>
            </div>
            <!-- Botón de editar tarea -->
            <button 
            type="button"
            onclick="abrirModalEditarTarea(<?= $tarea['id_tarea'] ?>)"
            class="ml-auto bg-yellow-100 hover:bg-yellow-200 rounded-full p-3 shadow transition"
            title="Editar tarea"
            >
            <i class="fas fa-pencil-alt text-yellow-600 text-3xl"></i>
            </button>
        </div>

        <!-- Modal editar tarea -->
        <div id="modalEditarTarea" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden overflow-y-auto">
            <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl relative my-10 max-h-[90vh] flex flex-col">
            <button onclick="cerrarModalEditarTarea()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl z-20">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-bold mb-4 flex items-center gap-2 sticky top-0 bg-white z-10 pb-2">
                <i class="fas fa-pencil-alt text-yellow-600"></i> Editar Tarea
            </h3>
            <div id="contenidoEditarTarea" class="overflow-y-auto flex-1 pr-2">
                <!-- Aquí se carga el formulario de edición vía AJAX -->
            </div>
            </div>
        </div>
        <script>
        function abrirModalEditarTarea(id) {
            document.getElementById('modalEditarTarea').classList.remove('hidden');
            fetch('editar_tarea_modal.php?id=' + id)
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenidoEditarTarea').innerHTML = html;
            });
        }
        function cerrarModalEditarTarea() {
            document.getElementById('modalEditarTarea').classList.add('hidden');
            document.getElementById('contenidoEditarTarea').innerHTML = '';
        }

        window.addEventListener('message', function(event) {
            if (event.data === 'cerrarModal') {
                cerrarModalEditarTarea();
            }
        });
        </script>

        <!-- Fechas Planificadas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-calendar-alt mr-1 text-green-600"></i>Fecha Inicio
                </label>
                <input type="date" value="<?= $tarea['fecha_inicio'] ?>" disabled
                    class="block w-full  border border-gray-200 bg-gray-50 text-gray-700 px-4 py-2 shadow-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-calendar-check mr-1 text-green-600"></i>Fecha Fin
                </label>
                <input type="date" value="<?= $tarea['fecha_fin'] ?>" disabled
                    class="block w-full -lg border border-gray-200 bg-gray-50 text-gray-700 px-4 py-2 shadow-sm focus:outline-none">
            </div>
        </div>

        <!-- Tipo, Categoría, Costo, Tiempo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-tools mr-1 text-indigo-600"></i>Tipo de mantenimiento
                </label>
                <div class="bg-indigo-50  px-3 py-2 text-indigo-700 font-medium shadow-inner">
                    <?= htmlspecialchars($tarea['nombre_tipo_mantenimiento']) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-layer-group mr-1 text-indigo-600"></i>Categoría
                </label>
                <div class="bg-indigo-50  px-3 py-2 text-indigo-700 font-medium shadow-inner">
                    <?= htmlspecialchars($tarea['categoria_mantenimiento']) ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-clock mr-1 text-yellow-600"></i>Tiempo programado
                </label>
                <div class="bg-yellow-50  px-3 py-2 text-yellow-700 font-medium shadow-inner">
                    <?= htmlspecialchars($tarea['tiempo_programado']) ?> min
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">
                    <i class="fas fa-dollar-sign mr-1 text-green-600"></i>Costo estimado
                </label>
                <div class="bg-green-50  px-3 py-2 text-green-700 font-medium shadow-inner">
                    $<?= number_format($tarea['costo'], 2) ?>
                </div>
            </div>
        </div>

        <!-- Título -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
                <i class="fas fa-heading mr-1 text-orange-600"></i>Título
            </label>
            <input type="text" class="block w-full  border border-gray-200 bg-gray-50 text-gray-700 px-4 py-2 shadow-sm focus:outline-none"
                value="<?= htmlspecialchars($tarea['titulo_tarea']) ?>" disabled>
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">
            <i class="fas fa-align-left mr-1 text-orange-600"></i>Descripción
            </label>
            <div class="mt-2 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200  px-5 py-4 shadow-inner text-gray-800 text-base leading-relaxed transition-all duration-200 hover:shadow-lg hover:from-blue-100 hover:to-blue-200">
            <span class="block whitespace-pre-line">
                <?= strip_tags($tarea['descripcion_tarea'], '<br>') ?>
            </span>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Información de la Máquina -->
    <div class="w-full md:w-1/3 bg-gradient-to-br from-blue-50 to-white border border-blue-200  shadow-xl p-8 space-y-6">

        <!-- Encabezado con icono y nombre -->
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-blue-100 rounded-full p-3 shadow">
                <i class="fas fa-cogs text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-blue-800">Máquina</h2>
                <span class="text-xs text-gray-500">Información detallada</span>
            </div>
        </div>

        <!-- Imagen y datos principales -->
        <div class="flex flex-col items-center gap-4">
            <div class="w-28 h-28 rounded-xl overflow-hidden shadow-lg border-2 border-blue-200 bg-white flex items-center justify-center">
                <?php if (!empty($tarea['url'])): ?>
                    <img src="<?= htmlspecialchars($tarea['url']) ?>" alt="Máquina" class="object-cover w-full h-full">
                <?php else: ?>
                    <i class="fas fa-cogs text-5xl text-blue-200"></i>
                <?php endif; ?>
            </div>
            <div class="w-full">
                <div class="mb-2">
                    <span class="block text-xs text-gray-500 font-semibold uppercase">Nombre</span>
                    <span class="text-base font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-tag text-blue-400"></i>
                        <?= htmlspecialchars($tarea['nombre_maquina']) ?>
                    </span>
                </div>
                <div class="mb-2">
                    <span class="block text-xs text-gray-500 font-semibold uppercase">Modelo</span>
                    <span class="text-base font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-cube text-indigo-400"></i>
                        <?= htmlspecialchars($tarea['nombre_modelo']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase">Marca</span>
                    <span class="text-base font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-industry text-purple-400"></i>
                        <?= htmlspecialchars($tarea['nombre_marca']) ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase">Tipo</span>
                    <span class="text-base font-medium text-gray-700 flex items-center gap-2">
                        <i class="fas fa-cube text-blue-400"></i>
                        <?= htmlspecialchars($tarea['nombre_tipo']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Ubicación -->
        <div class="mt-4">
            <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Ubicación</span>
            <div class="flex items-center gap-2 text-blue-700 font-medium">
                <i class="fas fa-map-marker-alt text-red-500"></i>
                <?= htmlspecialchars($tarea['nombre_sede']) ?>
            </div>
        </div>

        <!-- Código Único (si existe) -->
        <?php if (!empty($tarea['CodigoUnico'])): ?>
        <div class="mt-2">
            <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Código Único</span>
            <div class="flex items-center gap-2 text-gray-700">
                <i class="fas fa-barcode text-gray-400"></i>
                <span class="font-mono bg-blue-100 px-2 py-1 rounded text-blue-800"><?= htmlspecialchars($tarea['CodigoUnico']) ?></span>
            </div>
        </div>
        <?php endif; ?>
        <!-- Tiempo de Paro Programado -->
        <div class="mt-4">
            <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Tiempo de Paro de la maquina</span>
            <div class="flex items-center gap-2">
                <i class="fas fa-stopwatch text-red-500"></i>
                <span class="text-base font-bold text-red-700">
                    <?= htmlspecialchars($tarea['tiempo_paro_programado'] ?? 'No especificado') ?> 
                </span>
            </div>
        </div>
    </div>

  </div>
</div>
<div class="max-w-6xl mx-auto p-6 bg-white border border-gray-200 shadow-lg  mt-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-boxes text-blue-600"></i> Materiales de la tarea
        </h2>
        <button 
            type="button" 
            onclick="abrirModalMateriales()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow flex items-center"
            title="Agregar Materiales">
            <i class="fas fa-plus text-lg"></i>
        </button>
    </div>
    <!-- Modal para agregar materiales (cargado dinámicamente desde agregar_insumos.php) -->
    <div id="modalMateriales" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg relative my-10 max-h-[90vh] flex flex-col">
            <!-- Botón para cerrar el modal -->
            <button onclick="cerrarModalMateriales()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl z-20">
                <i class="fas fa-times"></i>
            </button>
            <div id="contenidoModalMateriales" class="overflow-y-auto flex-1">
                <!-- El contenido se cargará vía AJAX -->
                <div class="flex justify-center items-center h-40">
                    <span class="text-gray-500">Cargando...</span>
                </div>
            </div>
        </div>
    </div>
    <script>
    function abrirModalMateriales() {
        document.getElementById('modalMateriales').classList.remove('hidden');
        // Obtener la id de la tarea desde el input oculto
        var idTarea = document.querySelector('input[name="id_tarea"]').value;
        // Cargar el contenido del modal desde agregar_insumos.php pasando la id de la tarea
        fetch('agregar_insumos.php?id_tarea=' + encodeURIComponent(idTarea))
            .then(res => res.text())
            .then(html => {
                document.getElementById('contenidoModalMateriales').innerHTML = html;
            });
    }
    function cerrarModalMateriales() {
        document.getElementById('modalMateriales').classList.add('hidden');
        document.getElementById('contenidoModalMateriales').innerHTML = '<div class="flex justify-center items-center h-40"><span class="text-gray-500">Cargando...</span></div>';
    }
    </script>
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-100 rounded-full p-3 shadow">
                    <i class="fas fa-tools text-indigo-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Herramientas Utilizadas</h2>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 bg-gradient-to-br from-indigo-50 to-white">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-indigo-600 text-white">
                    <tr>
                        <th class="p-4 font-semibold">Imagen</th>
                        <th class="p-4 font-semibold">Nombre</th>
                        <th class="p-4 font-semibold">Cantidad</th>
                        <th class="p-4 font-semibold">Estatus</th>
                        <th class="p-4 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($herramientas as $h): ?>
                    <tr class="border-b last:border-b-0 hover:bg-indigo-50 transition">
                        <td class="p-4">
                            <?php if (!empty($h['url'])): ?>
                                <div class="w-16 h-16 rounded-lg overflow-hidden border-2 border-indigo-200 bg-white flex items-center justify-center shadow">
                                    <img src="<?= htmlspecialchars($h['url']); ?>" alt="<?= htmlspecialchars($h['nombre_imagen']); ?>" class="object-cover w-full h-full">
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded-lg border-2 border-gray-200">
                                    <i class="fas fa-tools text-gray-300 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($h['nombre_herramienta']) ?></td>
                        <td class="p-4">
                            <span class="inline-block bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full shadow">
                                <?= htmlspecialchars($h['cantidad']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php
                                $status = strtolower($h['nombre_status']);
                                $color = 'bg-gray-200 text-gray-700';
                                if (strpos($status, 'pendiente') !== false) $color = 'bg-yellow-100 text-yellow-700';
                                elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) $color = 'bg-green-100 text-green-700';
                                elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) $color = 'bg-red-100 text-red-700';
                            ?>
                            <span class="inline-block px-3 py-1 rounded-full font-semibold <?= $color ?>">
                                <?= htmlspecialchars($h['nombre_status']) ?>
                            </span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <!-- Ver -->
                            <button type="button" title="Ver" onclick="window.open('ver_herramienta.php?id=<?= $h['id_herramienta'] ?>', '_blank')"
                                class="text-blue-600 hover:text-blue-800 text-2xl">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Editar -->
                            <button type="button" title="Editar" onclick="abrirModalEditarHerramienta(<?= $h['id_herramienta'] ?>)"
                                class="text-yellow-600 hover:text-yellow-800 text-2xl">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($herramientas)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">No se han registrado herramientas para esta tarea.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modals para editar y eliminar herramienta -->
            <div id="modalEditarHerramienta" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg relative">
                    <button onclick="cerrarModalEditarHerramienta()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-pencil-alt text-yellow-600"></i> Editar Herramienta
                    </h3>
                    <div id="contenidoEditarHerramienta">
                        <!-- Aquí se carga el formulario de edición vía AJAX -->
                    </div>
                </div>
            </div>
            <div id="modalEliminarHerramienta" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm relative text-center">
                    <button onclick="cerrarModalEliminarHerramienta()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2 justify-center">
                        <i class="fas fa-trash-alt text-red-600"></i> Eliminar Herramienta
                    </h3>
                    <div id="contenidoEliminarHerramienta">
                        <!-- Aquí se carga el mensaje de confirmación vía AJAX -->
                    </div>
                </div>
            </div>
            <script>
            function abrirModalEditarHerramienta(id) {
                document.getElementById('modalEditarHerramienta').classList.remove('hidden');
                // Puedes cargar el formulario de edición vía AJAX aquí
                fetch('editar_herramienta_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEditarHerramienta').innerHTML = html;
                    });
            }
            function cerrarModalEditarHerramienta() {
                document.getElementById('modalEditarHerramienta').classList.add('hidden');
                document.getElementById('contenidoEditarHerramienta').innerHTML = '';
            }
            function abrirModalEliminarHerramienta(id) {
                document.getElementById('modalEliminarHerramienta').classList.remove('hidden');
                // Puedes cargar el mensaje de confirmación vía AJAX aquí
                fetch('eliminar_herramienta_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEliminarHerramienta').innerHTML = html;
                    });
            }
            function cerrarModalEliminarHerramienta() {
                document.getElementById('modalEliminarHerramienta').classList.add('hidden');
                document.getElementById('contenidoEliminarHerramienta').innerHTML = '';
            }
            </script>
        </div>
    </div>
    <!-- PRODUCTOS -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 rounded-full p-3 shadow">
                    <i class="fas fa-box-open text-blue-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Productos Utilizados</h2>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-white">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="p-4 font-semibold">Imagen</th>
                        <th class="p-4 font-semibold">Nombre</th>
                        <th class="p-4 font-semibold">Cantidad</th>
                        <th class="p-4 font-semibold">Estatus</th>
                        <th class="p-4 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr class="border-b last:border-b-0 hover:bg-blue-50 transition">
                        <td class="p-4">
                            <?php if (!empty($p['url'])): ?>
                                <div class="w-16 h-16 rounded-lg overflow-hidden border-2 border-blue-200 bg-white flex items-center justify-center shadow">
                                    <img src="<?= htmlspecialchars($p['url']); ?>" alt="<?= htmlspecialchars($p['nombre_imagen']); ?>" class="object-cover w-full h-full">
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded-lg border-2 border-gray-200">
                                    <i class="fas fa-box-open text-gray-300 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($p['nombre_producto']) ?></td>
                        <td class="p-4">
                            <span class="inline-block bg-blue-100 text-blue-700 font-semibold px-3 py-1 rounded-full shadow">
                                <?= htmlspecialchars($p['cantidad']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php
                                $status = strtolower($p['nombre_status']);
                                $color = 'bg-gray-200 text-gray-700';
                                if (strpos($status, 'pendiente') !== false) $color = 'bg-yellow-100 text-yellow-700';
                                elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) $color = 'bg-green-100 text-green-700';
                                elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) $color = 'bg-red-100 text-red-700';
                            ?>
                            <span class="inline-block px-3 py-1 rounded-full font-semibold <?= $color ?>">
                                <?= htmlspecialchars($p['nombre_status']) ?>
                            </span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <!-- Ver -->
                            <button type="button" title="Ver" onclick="window.open('ver_producto.php?id=<?= $p['id_producto'] ?>', '_blank')"
                                class="text-blue-600 hover:text-blue-800 text-2xl">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Editar -->
                            <button type="button" title="Editar" onclick="abrirModalEditarProducto(<?= $p['id_producto'] ?>)"
                                class="text-yellow-600 hover:text-yellow-800 text-2xl">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                         
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">No se han registrado productos para esta tarea.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modals para editar y eliminar producto -->
            <div id="modalEditarProducto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg relative">
                    <button onclick="cerrarModalEditarProducto()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-pencil-alt text-yellow-600"></i> Editar Producto
                    </h3>
                    <div id="contenidoEditarProducto">
                        <!-- Aquí se carga el formulario de edición vía AJAX -->
                    </div>
                </div>
            </div>
            <div id="modalEliminarProducto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm relative text-center">
                    <button onclick="cerrarModalEliminarProducto()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                  
                    <div id="contenidoEliminarProducto">
                        <!-- Aquí se carga el mensaje de confirmación vía AJAX -->
                    </div>
                </div>
            </div>
            <script>
            function abrirModalEditarProducto(id) {
                document.getElementById('modalEditarProducto').classList.remove('hidden');
                fetch('editar_producto_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEditarProducto').innerHTML = html;
                    });
            }
            function cerrarModalEditarProducto() {
                document.getElementById('modalEditarProducto').classList.add('hidden');
                document.getElementById('contenidoEditarProducto').innerHTML = '';
            }
            function abrirModalEliminarProducto(id) {
                document.getElementById('modalEliminarProducto').classList.remove('hidden');
                fetch('eliminar_producto_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEliminarProducto').innerHTML = html;
                    });
            }
            function cerrarModalEliminarProducto() {
                document.getElementById('modalEliminarProducto').classList.add('hidden');
                document.getElementById('contenidoEliminarProducto').innerHTML = '';
            }
            </script>
        </div>
    </div>
    <!-- REPUESTOS -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 rounded-full p-3 shadow">
                    <i class="fas fa-cogs text-green-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Repuestos Utilizados</h2>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl shadow-lg border border-gray-200 bg-gradient-to-br from-green-50 to-white">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-green-600 text-white">
                    <tr>
                        <th class="p-4 font-semibold">Imagen</th>
                        <th class="p-4 font-semibold">Nombre</th>
                        <th class="p-4 font-semibold">Cantidad</th>
                        <th class="p-4 font-semibold">Estatus</th>
                        <th class="p-4 font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($repuestos as $r): ?>
                    <tr class="border-b last:border-b-0 hover:bg-green-50 transition">
                        <td class="p-4">
                            <?php if (!empty($r['url'])): ?>
                                <div class="w-16 h-16 rounded-lg overflow-hidden border-2 border-green-200 bg-white flex items-center justify-center shadow">
                                    <img src="<?= htmlspecialchars($r['url']); ?>" alt="<?= htmlspecialchars($r['nombre_imagen']); ?>" class="object-cover w-full h-full">
                                </div>
                            <?php else: ?>
                                <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded-lg border-2 border-gray-200">
                                    <i class="fas fa-cogs text-gray-300 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($r['nombre_repuesto']) ?></td>
                        <td class="p-4">
                            <span class="inline-block bg-green-100 text-green-700 font-semibold px-3 py-1 rounded-full shadow">
                                <?= htmlspecialchars($r['cantidad']) ?>
                            </span>
                        </td>
                        <td class="p-4">
                            <?php
                                $status = strtolower($r['nombre_status']);
                                $color = 'bg-gray-200 text-gray-700';
                                if (strpos($status, 'pendiente') !== false) $color = 'bg-yellow-100 text-yellow-700';
                                elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) $color = 'bg-green-100 text-green-700';
                                elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) $color = 'bg-red-100 text-red-700';
                            ?>
                            <span class="inline-block px-3 py-1 rounded-full font-semibold <?= $color ?>">
                                <?= htmlspecialchars($r['nombre_status']) ?>
                            </span>
                        </td>
                        <td class="p-4 flex gap-2">
                            <!-- Ver -->
                            <button type="button" title="Ver" onclick="window.open('ver_repuesto.php?id=<?= $r['id_repuesto'] ?>', '_blank')"
                                class="text-blue-600 hover:text-blue-800 text-2xl">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Editar -->
                            <button type="button" title="Editar" onclick="abrirModalEditarRepuesto(<?= $r['id_repuesto'] ?>)"
                                class="text-yellow-600 hover:text-yellow-800 text-2xl">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                           
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($repuestos)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">No se han registrado repuestos para esta tarea.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Modals para editar y eliminar repuesto -->
            <div id="modalEditarRepuesto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-lg relative">
                    <button onclick="cerrarModalEditarRepuesto()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-pencil-alt text-yellow-600"></i> Editar Repuesto
                    </h3>
                    <div id="contenidoEditarRepuesto">
                        <!-- Aquí se carga el formulario de edición vía AJAX -->
                    </div>
                </div>
            </div>
            <div id="modalEliminarRepuesto" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-sm relative text-center">
                    <button onclick="cerrarModalEliminarRepuesto()" class="absolute top-3 right-3 text-gray-500 hover:text-red-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2 justify-center">
                        <i class="fas fa-trash-alt text-red-600"></i> Eliminar Repuesto
                    </h3>
                    <div id="contenidoEliminarRepuesto">
                        <!-- Aquí se carga el mensaje de confirmación vía AJAX -->
                    </div>
                </div>
            </div>
            <script>
            function abrirModalEditarRepuesto(id) {
                document.getElementById('modalEditarRepuesto').classList.remove('hidden');
                fetch('editar_repuesto_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEditarRepuesto').innerHTML = html;
                    });
            }
            function cerrarModalEditarRepuesto() {
                document.getElementById('modalEditarRepuesto').classList.add('hidden');
                document.getElementById('contenidoEditarRepuesto').innerHTML = '';
            }
            function abrirModalEliminarRepuesto(id) {
                document.getElementById('modalEliminarRepuesto').classList.remove('hidden');
                fetch('eliminar_repuesto_modal.php?id=' + id)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('contenidoEliminarRepuesto').innerHTML = html;
                    });
            }
            function cerrarModalEliminarRepuesto() {
                document.getElementById('modalEliminarRepuesto').classList.add('hidden');
                document.getElementById('contenidoEliminarRepuesto').innerHTML = '';
            }
            </script>
        </div>
    </div>
    </div>
<div class="max-w-7xl mx-auto mt-10 p-6 bg-white shadow-2xl  border border-gray-200">

   <div class="mt-6"> <!-- Agregamos margen superior -->
    <!-- Encabezado -->
    <div class="relative w-full p-4 border border-gray-300 rounded-lg text-center">
        <h2 class="text-xl font-bold flex">
            <i class="fas fa-tasks mr-2"></i> Actividades
        </h2>
    </div>
</div>

<!-- Contenedor principal con línea lateral -->
<div class="relative flex space-x-6">
    <!-- Línea vertical con icono en el centro -->
    <div class="relative w-10 flex justify-center">
    <!-- Línea más fina y centrada -->
    <div class="absolute w-px bg-gray-300 h-full left-1/2 transform -translate-x-1/2"></div>
    
    <!-- Icono perfectamente centrado en la línea -->
    <div class="absolute top-1/2 transform -translate-y-1/2 flex justify-center bg-white px-2">
        <i class="fas fa-user-group text-blue-600 text-3xl"></i>

    </div>
</div>

    <!-- Contenido del formulario -->
    <div class="border mt-6 border-gray-300 p-6 bg-white rounded-lg shadow-lg w-full">
        <form action="guardar_actividad.php" method="post">

        <!-- Descripción -->
       <div class="relative w-full mt-6 flex items-center">
            <i class="fas fa-align-left text-blue-600 text-lg mr-3"></i>

            <textarea 
                id="descripcion_actividad" 
                name="descripcion_actividad" 
                rows="1" 
                class="w-full p-3 rounded-lg border border-gray-400 focus:ring-2 focus:ring-blue-500 shadow-sm"
                placeholder="Descripción" required></textarea>
        </div>

        <!-- Fecha y Hora -->
        <div class="relative w-full mt-6 flex items-center grid-cols-2 gap-4 mt-6">
            <i class="fas fa-calendar text-blue-600 text-lg mr-3"></i>
            <?php
            // Limitar fecha y hora según la tarea
            $fecha_inicio = isset($tarea['fecha_inicio']) ? $tarea['fecha_inicio'] : '';
            $fecha_fin = isset($tarea['fecha_fin']) ? $tarea['fecha_fin'] : '';
            $hora_inicio = '00:00';
            $hora_fin = '23:59';
            ?>
            <div class="relative w-full">
            <label for="fecha_realizacion"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Realizada el:
            </label>
            <input 
                type="date" 
                id="fecha_realizacion" 
                name="fecha_realizacion" 
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
                min="<?= htmlspecialchars($fecha_inicio) ?>"
                max="<?= htmlspecialchars($fecha_fin) ?>"
                required
            >
            </div>
            <div class="relative w-full">
            <label for="hora_finalizacion"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Hora de Finalización:
            </label>
            <input 
                type="time" 
                id="hora_finalizacion" 
                name="hora_finalizacion" 
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
                min="<?= $hora_inicio ?>"
                max="<?= $hora_fin ?>"
                required
            >
            </div>
        </div>
        <script>
        // Validación extra en el frontend para asegurar que la fecha esté en el rango permitido
        document.getElementById('fecha_realizacion').addEventListener('change', function() {
            const min = this.min;
            const max = this.max;
            if (this.value < min) this.value = min;
            if (this.value > max) this.value = max;
        });
        </script>

        <!-- Tiempo invertido -->
           <div class="relative w-full mt-6 flex items-center  grid-cols-2 gap-4 mt-6">
            <i class="fas fa-hourglass-half text-blue-600 text-lg mr-3"></i>
            <div class="relative w-full">
                <label for="tiempo_invertido"
                    class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                    Tiempo Invertido:
                </label>
                <input 
                    type="number" 
                    id="tiempo_invertido" 
                    name="tiempo_invertido" 
                    class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Horas</span>
            </div>
            <div class="relative w-full">
                <label for="minutos_invertidos"
                    class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                    Minutos Invertidos:
                </label>
                <input 
                    type="number" 
                    id="minutos_invertidos" 
                    name="minutos_invertidos" 
                    class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Minutos</span>
            </div>
        </div>

      <hr class="my-6">

    
<!-- Selector de responsables -->
<div class="w-full bg-white shadow-md mb-6">
    <label class="block text-lg font-semibold flex items-center">
        <i class="fas fa-user-check text-blue-600 mr-2"></i> Seleccionar Responsables (Mecánicos)
    </label>

    <div class="relative w-full mt-3">
        <div class="border border-gray-300 rounded-md p-3 bg-white shadow-sm cursor-pointer min-h-[42px]"
             onclick="toggleDropdown()" id="selectedItems">
            Selecciona responsables...
        </div>

        <div id="dropdown" class="absolute w-full bg-white border border-gray-300 rounded-md shadow-md mt-2 hidden max-h-60 overflow-auto z-10">
            <div class="p-2">
                <input type="text" id="searchBox" class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500"
                    placeholder="Buscar responsables..." onkeyup="filterOptions()">
            </div>
            <div id="optionsContainer" class="p-2 space-y-1"></div>
        </div>
    </div>

    <!-- Campo oculto para guardar los IDs seleccionados -->
    <input type="hidden" name="responsables_seleccionados" id="responsablesSeleccionados">
</div>

<script>
    let personas = []; // será llenado desde PHP
    let seleccionados = [];

    function toggleDropdown() {
        document.getElementById("dropdown").classList.toggle("hidden");
    }

    function filterOptions() {
        const searchTerm = document.getElementById("searchBox").value.toLowerCase();
        const optionsContainer = document.getElementById("optionsContainer");
        optionsContainer.innerHTML = "";

        personas.forEach(p => {
            const nombreCompleto = p.nombre.toLowerCase();
            if (nombreCompleto.includes(searchTerm)) {
                const yaSeleccionado = seleccionados.find(sel => sel.id === p.id);
                const clase = yaSeleccionado ? 'bg-blue-100 text-blue-700' : 'hover:bg-gray-200';

                optionsContainer.innerHTML += `
                    <div class="p-2 cursor-pointer rounded-md flex items-center justify-between ${clase}"
                         onclick="toggleSeleccion('${p.id}', '${p.nombre}')">
                        <span><i class="fas fa-user text-blue-600 mr-2"></i>${p.nombre}</span>
                        ${yaSeleccionado ? '<i class="fas fa-check text-green-600"></i>' : ''}
                    </div>
                `;
            }
        });
    }

    function toggleSeleccion(id, nombre) {
        const index = seleccionados.findIndex(p => p.id === id);
        if (index > -1) {
            seleccionados.splice(index, 1); // quitar
        } else {
            seleccionados.push({ id, nombre });
        }
        renderSeleccionados();
        filterOptions();
    }

    function renderSeleccionados() {
        const selectedDiv = document.getElementById("selectedItems");
        const nombres = seleccionados.map(p => p.nombre);
        selectedDiv.textContent = nombres.length > 0 ? nombres.join(', ') : "Selecciona responsables...";

        // Guardar los IDs seleccionados en campo oculto
        document.getElementById("responsablesSeleccionados").value = seleccionados.map(p => p.id).join(',');
    }

    // Cargar datos desde PHP
    document.addEventListener("DOMContentLoaded", () => {
        fetch('obtener_mecanicos.php') // Ajusta esta ruta a tu archivo PHP real
            .then(res => res.json())
            .then(data => {
                personas = data;
                filterOptions();
            });
    });
</script> 

<!-- BOTÓN PARA ABRIR DROPDOWN -->
<div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
        <i class="fas fa-tools text-green-600"></i>Seleccione los repuestos necesarios
    </h2>
    <button type="button" onclick="toggleDropdownRepuestos()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Agregar repuestos
    </button>
</div>

<!-- DROPDOWN BUSCADOR -->
<div id="dropdownRepuestos" class="hidden mb-4 border rounded-lg p-4 bg-white shadow">
  <input id="buscarRepuesto" type="text" oninput="cargarRepuestos(1)" placeholder="Buscar repuesto..."
         class="border p-2 rounded w-full mb-2 focus:ring-2 focus:ring-blue-300" />
  <div id="contenedorRepuestos" class="max-h-60 overflow-y-auto"></div>
</div>

<!-- TABLA DE REPUESTOS SELECCIONADOS -->
<div id="tablaRepuestosAgregados" class="hidden mt-4">
  <table class="min-w-full text-sm text-left border rounded shadow">
    <thead class="bg-gray-100">
      <tr>
        <th class="p-2">Imagen</th>
        <th class="p-2">Detalles</th>
        <th class="p-2">Disponible</th>
        <th class="p-2">Stock Máx</th>
        <th class="p-2">Cantidad</th>
        <th class="p-2 text-center">Acciones</th>
      </tr>
    </thead>
    <tbody id="listaRepuestosAgregados"></tbody>
  </table>
</div>


<script>
let repuestosSeleccionados = [];

function toggleDropdownRepuestos() {
    const dropdown = document.getElementById("dropdownRepuestos");
    if (repuestosSeleccionados.length >= 2) {
        alert("Solo puedes agregar 2 repuestos.");
        return;
    }
    dropdown.classList.toggle("hidden");
    cargarRepuestos(1);
}

function cargarRepuestos(pagina = 1) {
    const buscar = document.getElementById("buscarRepuesto").value;
    fetch(`buscar_repuestos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorRepuestos").innerHTML = html;
        });
}

function agregarRepuestoDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMaximo, marca, modelo, tipo) {
    if (repuestosSeleccionados.length >= 2) {
        alert("Límite de 2 repuestos alcanzado.");
        return;
    }

    if (repuestosSeleccionados.some(r => r.id === id)) {
        alert("Este repuesto ya ha sido agregado.");
        return;
    }

    const nuevoRepuesto = {
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMaximo
    };

    repuestosSeleccionados.push(nuevoRepuesto);
    renderizarTablaRepuestos();
    actualizarInputsOcultosRepuestos();
    document.getElementById("dropdownRepuestos").classList.add("hidden");
}

function renderizarTablaRepuestos() {
    const cuerpo = document.getElementById("listaRepuestosAgregados");
    const contenedor = document.getElementById("tablaRepuestosAgregados");
    cuerpo.innerHTML = "";

    repuestosSeleccionados.forEach((r, i) => {
        const total = r.cantidad + r.pendiente;

        cuerpo.innerHTML += `
            <tr class="border-b">
                <td class="p-2">
                    <img src="${r.imagen}" alt="${r.nombre}" class="w-12 h-12 object-cover rounded-md">
                </td>
                <td class="p-2">
                    <div class="font-semibold">${r.nombre}</div>
                    <div class="text-sm text-gray-600">${r.marca} / ${r.modelo} / ${r.tipo}</div>
                </td>
                <td class="p-2">${r.disponible}</td>
                <td class="p-2">${r.stockMaximo}</td>
                <td class="p-2">
                    <input type="number" min="1" value="${total}" 
                        onblur="actualizarCantidadRepuesto(${i}, this.value)" 
                        class="w-20 border rounded p-1 text-center focus:ring-2 focus:ring-blue-300">
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">Planificado: ${r.cantidad}</span>
                        ${r.pendiente > 0 ? `<br><span class="text-yellow-600">Pendiente: ${r.pendiente}</span>` : ''}
                        ${total > r.stockMaximo
                            ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo estimado (${r.stockMaximo})</span>`
                            : ''
                        }
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verRepuesto(${r.id})" class="text-blue-600 hover:text-blue-800" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" onclick="quitarRepuesto(${i})" class="text-red-600 hover:text-red-800" title="Quitar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;
    });

    contenedor.classList.toggle("hidden", repuestosSeleccionados.length === 0);
    actualizarInputsOcultosRepuestos();
}

function quitarRepuesto(index) {
    repuestosSeleccionados.splice(index, 1);
    renderizarTablaRepuestos();
}

function actualizarCantidadRepuesto(index, nuevaCantidad) {
    const r = repuestosSeleccionados[index];
    nuevaCantidad = parseInt(nuevaCantidad);

    if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
        alert("Cantidad no válida.");
        renderizarTablaRepuestos();
        return;
    }

    if (nuevaCantidad <= r.disponible) {
        r.cantidad = nuevaCantidad;
        r.pendiente = 0;
        renderizarTablaRepuestos();
    } else {
        const excedente = nuevaCantidad - r.disponible;
        mostrarModalPendienteRepuesto(excedente, () => {
            r.cantidad = r.disponible;
            r.pendiente = excedente;
            renderizarTablaRepuestos();
        }, () => {
            renderizarTablaRepuestos(); // cancelar
        });
    }

    if (nuevaCantidad > r.stockMaximo) {
        mostrarAlertaRepuesto("Estás superando el stock máximo recomendado.", "warning");
    }
}

function verRepuesto(id) {
    window.open(`ver_repuesto.php?id=${id}`, '_blank');
}

function mostrarModalPendienteRepuesto(cantidadPendiente, aceptarCallback, cancelarCallback) {
    const modal = document.createElement('div');
    modal.id = "modalPendienteRepuesto";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";

    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" onclick="aceptarPendienteRepuesto()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sí</button>
                <button type="button" onclick="cancelarPendienteRepuesto()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">No</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    window.aceptarPendienteRepuesto = () => {
        aceptarCallback();
        cerrarModalPendienteRepuesto();
    };
    window.cancelarPendienteRepuesto = () => {
        cancelarCallback();
        cerrarModalPendienteRepuesto();
    };
}

function cerrarModalPendienteRepuesto() {
    const modal = document.getElementById("modalPendienteRepuesto");
    if (modal) modal.remove();
}

function mostrarAlertaRepuesto(mensaje, tipo = "info") {
    const color = tipo === "warning" ? "bg-yellow-100 text-yellow-800" : "bg-blue-100 text-blue-800";
    const alerta = document.createElement('div');
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${color}`;
    alerta.innerHTML = mensaje;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}

function actualizarInputsOcultosRepuestos() {
    const contenedor = document.getElementById("inputsRepuestosOcultos");
    contenedor.innerHTML = "";

    let index = 0;
    let hayRepuestos = false;

    repuestosSeleccionados.forEach((r) => {
        if (r.cantidad > 0) {
            contenedor.innerHTML += `
                <input type="hidden" name="repuestos[${index}][id]" value="${r.id}">
                <input type="hidden" name="repuestos[${index}][cantidad]" value="${r.cantidad}">
                <input type="hidden" name="repuestos[${index}][status_id]" value="25">
            `;
            index++;
            hayRepuestos = true;
        }
        if (r.pendiente > 0) {
            contenedor.innerHTML += `
                <input type="hidden" name="repuestos[${index}][id]" value="${r.id}">
                <input type="hidden" name="repuestos[${index}][cantidad]" value="${r.pendiente}">
                <input type="hidden" name="repuestos[${index}][status_id]" value="26">
            `;
            index++;
            hayRepuestos = true;
        }
    });

    return hayRepuestos;
}

function validarRepuestosSeleccionados() {
    const hayRepuestos = actualizarInputsOcultosRepuestos();

    if (!hayRepuestos) {
        alert("Debe seleccionar al menos un repuesto para continuar.");
        return false;
    }

    return true;
}

document.getElementById('formularioTarea').addEventListener('submit', function (e) {
    const inputHidden = document.createElement('input');
    inputHidden.type = 'hidden';
    inputHidden.name = 'repuestos_seleccionados';
    inputHidden.value = JSON.stringify(repuestosSeleccionados);
    this.appendChild(inputHidden);
});
</script>


<div id="inputsRepuestosOcultos"></div>
<hr class="my-6">
<!-- Contenedor del buscador y tabla de herramientas -->

    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
            <i class="fas fa-tools text-indigo-600"></i>Seleccione las herramientas necesarias 
        </h2>
        <button type="button" onclick="toggleDropdownHerramientas()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 shadow flex items-center">
            <i class="fas fa-plus mr-2"></i> Agregar herramientas
        </button>
    </div>

    <!-- Dropdown de selección de herramientas -->
    <div id="dropdownHerramientas" class="border mt-3 p-4 rounded-md shadow-md bg-white hidden relative z-10">
        <div class="mb-3">
            <input type="text" id="buscarHerramienta" placeholder="Buscar herramienta..."
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-indigo-500"
                onkeyup="cargarHerramientas(1)">
        </div>
        <div id="contenedorHerramientas" class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"></div>
        <div id="paginacionHerramientas" class="mt-3 flex justify-center gap-2"></div>
        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleDropdownHerramientas()"
                class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i> Cerrar
            </button>
        </div>
    </div>

    <!-- Dropdown de herramientas -->
<div id="dropdownHerramientas" class="hidden absolute bg-white shadow-md rounded z-40 w-full max-w-lg">
    <input type="text" id="buscarHerramienta" onkeyup="cargarHerramientas()" class="w-full p-2 border-b" placeholder="Buscar herramienta...">
    <div id="contenedorHerramientas" class="max-h-60 overflow-y-auto"></div>
</div>

<!-- Tabla de herramientas agregadas -->
<div id="tablaHerramientasAgregadas" class="mt-4 hidden">
    <table class="min-w-full bg-white shadow rounded">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Imagen</th>
                <th class="p-2">Herramienta</th>
                <th class="p-2">Disponible</th>
                <th class="p-2">Stock Mín.</th>
                <th class="p-2">Stock Máx.</th>
                <th class="p-2">Cantidad</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody id="listaHerramientasAgregadas"></tbody>
    </table>
</div>

<!-- Input ocultos para herramientas -->
<div id="inputsHerramientasPlanificadas"></div>
<div id="inputsHerramientasPendientes"></div>

<script>
let herramientasSeleccionadas = [];

function toggleDropdownHerramientas() {
    const dropdown = document.getElementById("dropdownHerramientas");
    if (herramientasSeleccionadas.length >= 2) {
        alert("Solo puedes agregar 2 herramientas.");
        return;
    }
    dropdown.classList.toggle("hidden");
    cargarHerramientas(1);
}

function cargarHerramientas(pagina = 1) {
    const buscar = document.getElementById("buscarHerramienta").value;
    fetch(`buscar_herramientas_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorHerramientas").innerHTML = html;
        });
}

function agregarHerramientaDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMinimo, stockMaximo, marca, modelo, tipo) {
    if (herramientasSeleccionadas.length >= 2) {
        alert("Límite de 2 herramientas alcanzado.");
        return;
    }

    if (herramientasSeleccionadas.some(h => h.id === id)) {
        alert("Esta herramienta ya ha sido agregada.");
        return;
    }

    const nuevaHerramienta = {
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMinimo,
        stockMaximo
    };

    herramientasSeleccionadas.push(nuevaHerramienta);
    renderizarTablaHerramientas();
    document.getElementById("dropdownHerramientas").classList.add("hidden");
}

function renderizarTablaHerramientas() {
    const cuerpo = document.getElementById("listaHerramientasAgregadas");
    const contenedor = document.getElementById("tablaHerramientasAgregadas");
    cuerpo.innerHTML = "";

    herramientasSeleccionadas.forEach((h, i) => {
        const total = h.cantidad + h.pendiente;

        cuerpo.innerHTML += `
            <tr class="border-b">
                <td class="p-2">
                    <img src="${h.imagen}" alt="${h.nombre}" class="w-12 h-12 object-cover rounded-md">
                </td>
                <td class="p-2">
                    <div class="font-semibold">${h.nombre}</div>
                    <div class="text-sm text-gray-600">${h.marca} / ${h.modelo} / ${h.tipo} / ${h.unidad}</div>
                </td>
                <td class="p-2">${h.disponible}</td>
                <td class="p-2">${h.stockMinimo}</td>
                <td class="p-2">${h.stockMaximo}</td>
                <td class="p-2">
                    <input type="number" min="1" value="${total}" 
                        onblur="actualizarCantidadHerramienta(${i}, this.value)" 
                        class="w-20 border rounded p-1 text-center shadow-sm focus:ring-2 focus:ring-blue-300">
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">Planificado: ${h.cantidad}</span>
                        ${h.pendiente > 0 ? `<br><span class="text-yellow-600">Pendiente: ${h.pendiente}</span>` : ''}
                        ${total > h.stockMaximo
                            ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo estimado (${h.stockMaximo})</span>`
                            : ''
                        }
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verHerramienta(${h.id})" class="text-blue-600 hover:text-blue-800" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" onclick="quitarHerramienta(${i})" class="text-red-600 hover:text-red-800" title="Quitar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;
    });

    contenedor.classList.toggle("hidden", herramientasSeleccionadas.length === 0);
    actualizarInputsOcultosHerramientas();
}

function quitarHerramienta(index) {
    herramientasSeleccionadas.splice(index, 1);
    renderizarTablaHerramientas();
}

function actualizarCantidadHerramienta(index, nuevaCantidad) {
    const h = herramientasSeleccionadas[index];
    nuevaCantidad = parseInt(nuevaCantidad);

    if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
        alert("Cantidad no válida.");
        renderizarTablaHerramientas();
        return;
    }

    if (nuevaCantidad <= h.disponible) {
        h.cantidad = nuevaCantidad;
        h.pendiente = 0;
        renderizarTablaHerramientas();
    } else {
        const excedente = nuevaCantidad - h.disponible;

        mostrarModalPendienteHerramienta(excedente, () => {
            h.cantidad = h.disponible;
            h.pendiente = excedente;
            renderizarTablaHerramientas();
        }, () => {
            renderizarTablaHerramientas(); // Revertir si cancela
        });
    }

    if (nuevaCantidad > h.stockMaximo) {
        mostrarAlertaHerramienta("Estás superando el stock máximo recomendado.", "warning");
    }
}

function actualizarInputsOcultosHerramientas() {
    const contenedorPlanificadas = document.getElementById("inputsHerramientasPlanificadas");
    const contenedorPendientes = document.getElementById("inputsHerramientasPendientes");
    contenedorPlanificadas.innerHTML = "";
    contenedorPendientes.innerHTML = "";

    let planIndex = 0;
    let pendIndex = 0;

    herramientasSeleccionadas.forEach(h => {
        if (h.cantidad > 0) {
            contenedorPlanificadas.innerHTML += `
                <input type="hidden" name="herramientas_planificadas[${planIndex}][id]" value="${h.id}">
                <input type="hidden" name="herramientas_planificadas[${planIndex}][cantidad]" value="${h.cantidad}">
                <input type="hidden" name="herramientas_planificadas[${planIndex}][status_id]" value="25">
            `;
            planIndex++;
        }

        if (h.pendiente > 0) {
            contenedorPendientes.innerHTML += `
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][id]" value="${h.id}">
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][cantidad]" value="${h.pendiente}">
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][status_id]" value="26">
            `;
            pendIndex++;
        }
    });
}

function verHerramienta(id) {
    window.open(`ver_herramienta.php?id=${id}`, '_blank');
}

function mostrarModalPendienteHerramienta(cantidadPendiente, aceptarCallback, cancelarCallback) {
    const modal = document.createElement('div');
    modal.id = "modalPendienteHerramienta";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";

    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" onclick="aceptarPendienteHerramienta()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sí</button>
                <button type="button" onclick="cancelarPendienteHerramienta()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">No</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    window.aceptarPendienteHerramienta = () => {
        aceptarCallback();
        cerrarModalPendienteHerramienta();
    };
    window.cancelarPendienteHerramienta = () => {
        cancelarCallback();
        cerrarModalPendienteHerramienta();
    };
}

function cerrarModalPendienteHerramienta() {
    const modal = document.getElementById("modalPendienteHerramienta");
    if (modal) modal.remove();
}

function mostrarAlertaHerramienta(mensaje, tipo = "info") {
    const color = tipo === "warning" ? "bg-yellow-100 text-yellow-800" : "bg-blue-100 text-blue-800";
    const alerta = document.createElement('div');
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${color}`;
    alerta.innerHTML = mensaje;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}
</script>


<hr class="my-6">
<!-- Contenedor del buscador y tabla de productos -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
            <i class="fas fa-box-open text-blue-600"></i>Seleccione los productos necesarios 
        </h2>
        <button type="button" onclick="toggleDropdownProductos()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow flex items-center">
            <i class="fas fa-plus mr-2"></i> Agregar productos
        </button>
    </div>

    <!-- Dropdown de selección de productos -->
    <div id="dropdownProductos" class="border mt-3 p-4 rounded-md shadow-md bg-white hidden relative z-10">
        <div class="mb-3">
            <input type="text" id="buscarProducto" placeholder="Buscar producto..."
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500"
                onkeyup="cargarProductos(1)">
        </div>
        <div id="contenedorProductos" class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"></div>
        <div id="paginacionProductos" class="mt-3 flex justify-center gap-2"></div>
        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleDropdownProductos()"
                class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i> Cerrar
            </button>
        </div>
    </div>

    <!-- Tabla de productos agregados -->
    <div id="tablaProductosAgregados" class="mt-5 hidden">
        <table class="min-w-full border border-gray-300 rounded-md shadow-md text-sm text-gray-800 bg-white">
            <thead class="bg-gray-100">
                <tr>
                     <th class="px-3 py-2">Imagen</th>
            <th class="px-3 py-2">Especificación</th>
            <th class="px-3 py-2">Cantidad</th>
            <th class="px-3 py-2">Stock Mínimo</th>
            <th class="px-3 py-2">Stock Máximo</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2">Quitar</th>
                </tr>
            </thead>
            <tbody id="listaProductosAgregados"></tbody>
        </table>
    </div>
</div>
<!-- Contenedores ocultos donde se crean los inputs para enviar al backend -->
<div id="inputsProductosPlanificados" class="hidden"></div>
<div id="inputsProductosPendientes" class="hidden"></div>
<!-- Campo oculto para enviar los productos seleccionados (JSON, opcional) -->
<input type="hidden" name="productos_seleccionados" id="productos_seleccionados">

<script>
let productosSeleccionados = [];

function toggleDropdownProductos() {
    const dropdown = document.getElementById("dropdownProductos");
    if (productosSeleccionados.length >= 2) {
        alert("Solo puedes agregar 2 productos.");
        return;
    }
    dropdown.classList.toggle("hidden");
    cargarProductos(1);
}

function cargarProductos(pagina = 1) {
    const buscar = document.getElementById("buscarProducto").value;
    fetch(`buscar_productos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorProductos").innerHTML = html;
        });
}

function agregarProductoDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMinimo, stockMaximo, marca, modelo, tipo) {
    if (productosSeleccionados.length >= 2) {
        alert("Límite de 2 productos alcanzado.");
        return;
    }

    if (productosSeleccionados.some(p => p.id === id)) {
        alert("Este producto ya ha sido agregado.");
        return;
    }

    const nuevoProducto = {
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMinimo,
        stockMaximo
    };

    productosSeleccionados.push(nuevoProducto);
    renderizarTablaProductos();
    document.getElementById("dropdownProductos").classList.add("hidden");
}

function renderizarTablaProductos() {
    const cuerpo = document.getElementById("listaProductosAgregados");
    const contenedor = document.getElementById("tablaProductosAgregados");
    cuerpo.innerHTML = "";

    productosSeleccionados.forEach((p, i) => {
        const total = p.cantidad + p.pendiente;
        cuerpo.innerHTML += `
            <tr class="border-b">
                <td class="p-2"><img src="${p.imagen}" alt="${p.nombre}" class="w-12 h-12 object-cover rounded-md"></td>
                <td class="p-2">
                    <div class="font-semibold">${p.nombre}</div>
                    <div class="text-sm text-gray-600">${p.marca} / ${p.modelo} / ${p.tipo} / ${p.unidad}</div>
                </td>
                <td class="p-2">${p.disponible}</td>
                <td class="p-2">${p.stockMinimo}</td>
                <td class="p-2">${p.stockMaximo}</td>
                <td class="p-2">
                    <input type="number" min="1" value="${total}" 
                        onblur="actualizarCantidadProducto(${i}, this.value)" 
                        class="w-20 border rounded p-1 text-center shadow-sm focus:ring-2 focus:ring-blue-300">
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">Planificado: ${p.cantidad}</span>
                        ${p.pendiente > 0 ? `<br><span class="text-yellow-600">Pendiente: ${p.pendiente}</span>` : ''}
                        ${total > p.stockMaximo
                            ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo (${p.stockMaximo})</span>`
                            : ''
                        }
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verProducto(${p.id})" class="text-blue-600 hover:text-blue-800" title="Ver"><i class="fas fa-eye"></i></button>
                    <button type="button" onclick="quitarProducto(${i})" class="text-red-600 hover:text-red-800" title="Quitar"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>`;
    });

    contenedor.classList.toggle("hidden", productosSeleccionados.length === 0);
    actualizarInputsOcultosProductos();
}

function quitarProducto(index) {
    productosSeleccionados.splice(index, 1);
    renderizarTablaProductos();
}

function actualizarCantidadProducto(index, nuevaCantidad) {
    const p = productosSeleccionados[index];
    nuevaCantidad = parseInt(nuevaCantidad);

    if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
        alert("Cantidad no válida.");
        renderizarTablaProductos();
        return;
    }

    if (nuevaCantidad <= p.disponible) {
        p.cantidad = nuevaCantidad;
        p.pendiente = 0;
        renderizarTablaProductos();
    } else {
        const excedente = nuevaCantidad - p.disponible;

        mostrarModalPendiente(excedente, () => {
            p.cantidad = p.disponible;
            p.pendiente = excedente;
            renderizarTablaProductos();
        }, () => {
            renderizarTablaProductos();
        });
    }

    if (nuevaCantidad > p.stockMaximo) {
        mostrarAlerta("Estás superando el stock máximo recomendado.", "warning");
    }
}

function verProducto(id) {
    window.open(`ver_producto.php?id=${id}`, '_blank');
}

function mostrarModalPendiente(cantidadPendiente, aceptarCallback, cancelarCallback) {
    const modal = document.createElement('div');
    modal.id = "modalPendiente";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" onclick="aceptarPendiente()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sí</button>
                <button type="button" onclick="cancelarPendiente()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">No</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    window.aceptarPendiente = () => { aceptarCallback(); cerrarModalPendiente(); };
    window.cancelarPendiente = () => { cancelarCallback(); cerrarModalPendiente(); };
}

function cerrarModalPendiente() {
    const modal = document.getElementById("modalPendiente");
    if (modal) modal.remove();
}

function mostrarAlerta(mensaje, tipo = "info") {
    const color = tipo === "warning" ? "bg-yellow-100 text-yellow-800" : "bg-blue-100 text-blue-800";
    const alerta = document.createElement('div');
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${color}`;
    alerta.innerHTML = mensaje;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}

// Esta función genera inputs ocultos con estructura array en POST
function actualizarInputsOcultosProductos() {
    const contenedorPlanificadas = document.getElementById("inputsProductosPlanificados");
    const contenedorPendientes = document.getElementById("inputsProductosPendientes");
    contenedorPlanificadas.innerHTML = "";
    contenedorPendientes.innerHTML = "";

    let planIndex = 0;
    let pendIndex = 0;

    productosSeleccionados.forEach(p => {
        if (p.cantidad > 0) {
            contenedorPlanificadas.innerHTML += `
                <input type="hidden" name="productos_planificados[${planIndex}][id]" value="${p.id}">
                <input type="hidden" name="productos_planificados[${planIndex}][cantidad]" value="${p.cantidad}">
                <input type="hidden" name="productos_planificados[${planIndex}][status_id]" value="25">
            `;
            planIndex++;
        }

        if (p.pendiente > 0) {
            contenedorPendientes.innerHTML += `
                <input type="hidden" name="productos_pendientes[${pendIndex}][id]" value="${p.id}">
                <input type="hidden" name="productos_pendientes[${pendIndex}][cantidad]" value="${p.pendiente}">
                <input type="hidden" name="productos_pendientes[${pendIndex}][status_id]" value="26">
            `;
            pendIndex++;
        }
    });
}

// En caso de usar <form onsubmit="return prepararEnvioProductos()">
function prepararEnvioProductos() {
    document.getElementById("productos_seleccionados").value = JSON.stringify(productosSeleccionados);
    actualizarInputsOcultosProductos();
    return true;
}
</script>
        <!-- Botón final -->
        <div class="text-center mt-6">
            <?php
// Assuming $id_tarea is the task ID you want to pass
$id_tarea = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

    <!-- Hidden input to store the task ID -->
    <input type="hidden" name="id_tarea" value="<?php echo htmlspecialchars($id_tarea); ?>">

    <!-- Other form fields for activity details -->

    <button 
        type="submit" 
        class="bg-blue-500 text-white py-3 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition">
        <i class="fas fa-plus-circle mr-2"></i> Añadir Nueva Actividad
    </button>
        </div>
        </form>
    </div>
    
</div>

<!-- Tabla de Actividades Agregadas -->
<div class="mt-8">
    <h2 class="text-xl font-bold text-blue-700 mb-4 flex items-center">
        <i class="fas fa-list text-blue-500 text-2xl mr-2"></i> Actividades Agregadas
    </h2>
    <div class="overflow-x-auto">
    <?php
    // Obtener el id_tarea de la URL
    $id_tarea = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Incluir conexión PDO
    include 'db_connection.php';

    // Obtener actividades relacionadas a la tarea
    $stmt = $conn->prepare("SELECT * FROM actividades WHERE tarea_id = ?");
    $stmt->execute([$id_tarea]);
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada actividad, obtener herramientas, productos y repuestos relacionados
    foreach ($actividades as $k => $actividad) {
        $id_actividad = $actividad['id_actividad'];

        // Herramientas relacionadas con la actividad
        $stmt = $conn->prepare("
            SELECT h.*, ah.cantidad, s.nombre_status 
            FROM herramienta_actividad ah
            JOIN herramientas h ON ah.herramienta_id = h.id_herramienta
            LEFT JOIN status s ON ah.status_id = s.id_status
            WHERE ah.id_actividad = ?
        ");
        $stmt->execute([$id_actividad]);
        $actividades[$k]['herramientas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Productos relacionados con la actividad
        $stmt = $conn->prepare("
            SELECT p.*, ap.cantidad, s.nombre_status 
            FROM producto_actividad ap
            JOIN producto p ON ap.producto_id = p.id_producto
            LEFT JOIN status s ON ap.status_id = s.id_status
            WHERE ap.actividad_id = ?
        ");
        $stmt->execute([$id_actividad]);
        $actividades[$k]['productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Repuestos relacionados con la actividad
        $stmt = $conn->prepare("
            SELECT r.*, ar.cantidad, s.nombre_status 
            FROM repuesto_actividad ar
            JOIN repuesto r ON ar.repuesto_id = r.id_repuesto
            LEFT JOIN status s ON ar.status_id = s.id_status
            WHERE ar.actividad_id = ?
        ");
        $stmt->execute([$id_actividad]);
        $actividades[$k]['repuestos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($actividades as $actividad): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg flex flex-col mb-6 hover:shadow-2xl transition-all">
            <!-- Header: ID de la actividad -->
            <div class="bg-blue-600 rounded-t-xl px-6 py-4 flex items-center justify-between">
                <span class="text-white font-bold text-lg flex items-center gap-2">
                    <i class="fas fa-hashtag"></i> <?= htmlspecialchars($actividad['id_actividad']) ?>
                </span>
                <div class="flex gap-2">
                    <button class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded transition" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
            <!-- Descripción -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="mb-2">
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Descripción</span>
                    <span class="text-gray-800"><?= nl2br(htmlspecialchars($actividad['descripcion_actividad'])) ?></span>
                </div>
            </div>
            <!-- Fechas y tiempo -->
            <div class="px-6 py-4 border-b border-gray-100 grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Fecha Realizada</span>
                    <span class="text-blue-700 font-medium"><?= htmlspecialchars($actividad['fecha_realizar']) ?></span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Hora Finalización</span>
                    <span class="text-blue-700 font-medium"><?= htmlspecialchars($actividad['hora_finalizacion']) ?></span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Tiempo Invertido</span>
                    <span class="text-green-700 font-medium"><?= htmlspecialchars($actividad['tiempo_invertido']) ?> h</span>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Minutos Invertidos</span>
                    <span class="text-green-700 font-medium"><?= htmlspecialchars($actividad['minutos_invertidos']) ?> min</span>
                </div>
            </div>
            <!-- Utensilios a usar -->
            <div class="px-6 py-4 flex-1">
                <div class="mb-2">
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Herramientas</span>
                    <?php if (!empty($actividad['herramientas'])): ?>
                        <ul class="list-disc ml-5 text-gray-700 text-sm">
                            <?php foreach ($actividad['herramientas'] as $h): ?>
                                <li>
                                    <span class="font-semibold"><?= htmlspecialchars($h['nombre_herramienta']) ?></span>
                                    <span class="text-gray-500">x<?= htmlspecialchars($h['cantidad']) ?></span>
                                    <span class="inline-block ml-2 px-2 py-0.5 rounded-full text-xs
                                        <?php
                                            $status = strtolower($h['nombre_status']);
                                            if (strpos($status, 'pendiente') !== false) echo 'bg-yellow-100 text-yellow-700';
                                            elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) echo 'bg-green-100 text-green-700';
                                            elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) echo 'bg-red-100 text-red-700';
                                            else echo 'bg-gray-200 text-gray-700';
                                        ?>">
                                        <?= htmlspecialchars($h['nombre_status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="text-gray-400">Sin herramientas</span>
                    <?php endif; ?>
                </div>
                <div class="mb-2">
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Productos</span>
                    <?php if (!empty($actividad['productos'])): ?>
                        <ul class="list-disc ml-5 text-gray-700 text-sm">
                            <?php foreach ($actividad['productos'] as $p): ?>
                                <li>
                                    <span class="font-semibold"><?= htmlspecialchars($p['nombre_producto']) ?></span>
                                    <span class="text-gray-500">x<?= htmlspecialchars($p['cantidad']) ?></span>
                                    <span class="inline-block ml-2 px-2 py-0.5 rounded-full text-xs
                                        <?php
                                            $status = strtolower($p['nombre_status']);
                                            if (strpos($status, 'pendiente') !== false) echo 'bg-yellow-100 text-yellow-700';
                                            elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) echo 'bg-green-100 text-green-700';
                                            elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) echo 'bg-red-100 text-red-700';
                                            else echo 'bg-gray-200 text-gray-700';
                                        ?>">
                                        <?= htmlspecialchars($p['nombre_status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="text-gray-400">Sin productos</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="block text-xs text-gray-500 font-semibold uppercase mb-1">Repuestos</span>
                    <?php if (!empty($actividad['repuestos'])): ?>
                        <ul class="list-disc ml-5 text-gray-700 text-sm">
                            <?php foreach ($actividad['repuestos'] as $r): ?>
                                <li>
                                    <span class="font-semibold"><?= htmlspecialchars($r['nombre_repuesto']) ?></span>
                                    <span class="text-gray-500">x<?= htmlspecialchars($r['cantidad']) ?></span>
                                    <span class="inline-block ml-2 px-2 py-0.5 rounded-full text-xs
                                        <?php
                                            $status = strtolower($r['nombre_status']);
                                            if (strpos($status, 'pendiente') !== false) echo 'bg-yellow-100 text-yellow-700';
                                            elseif (strpos($status, 'entregado') !== false || strpos($status, 'completo') !== false) echo 'bg-green-100 text-green-700';
                                            elseif (strpos($status, 'rechazado') !== false || strpos($status, 'falta') !== false) echo 'bg-red-100 text-red-700';
                                            else echo 'bg-gray-200 text-gray-700';
                                        ?>">
                                        <?= htmlspecialchars($r['nombre_status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <span class="text-gray-400">Sin repuestos</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
