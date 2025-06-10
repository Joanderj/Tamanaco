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
$menu_actual = 7;

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
<?php
include 'db_connection.php';

$id_tarea = $_GET['id_tarea'] ?? null;
$productos_pendientes = [];
$repuestos_pendientes = [];
$herramientas_pendientes = [];

if ($id_tarea) {
    // Productos
    $sql1 = "SELECT p.id_producto, p.nombre_producto, p.id_marca, p.id_modelo, p.id_tipo, p.url, pt.cantidad 
             FROM producto_tarea pt 
             JOIN producto p ON pt.producto_id = p.id_producto 
             WHERE pt.tarea_id = ? AND pt.status_id = 26";
    $sql2 = "SELECT p.id_producto, p.nombre_producto, p.id_marca, p.id_modelo, p.id_tipo, p.url, pa.cantidad 
             FROM producto_actividad pa 
             JOIN producto p ON pa.producto_id = p.id_producto 
             JOIN actividades a ON pa.actividad_id = a.id_actividad 
             WHERE a.tarea_id = ? AND pa.status_id = 26";
    $stmt = $conn->prepare($sql1); $stmt->execute([$id_tarea]); $pt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conn->prepare($sql2); $stmt->execute([$id_tarea]); $pa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Unificar y sumar cantidades por id_producto
    $productos_tmp = [];
    foreach (array_merge($pt, $pa) as $item) {
        $id = $item['id_producto'];
        if (!isset($productos_tmp[$id])) {
            $productos_tmp[$id] = $item;
            $productos_tmp[$id]['cantidad'] = (int)$item['cantidad'];
        } else {
            $productos_tmp[$id]['cantidad'] += (int)$item['cantidad'];
        }
    }
    // Obtener info de marca, modelo, tipo
    foreach ($productos_tmp as $item) {
        $marca = $modelo = $tipo = '';
        // Marca
        $stmt = $conn->prepare("SELECT nombre_marca FROM marca WHERE id_marca = ?");
        $stmt->execute([$item['id_marca']]);
        $marca = $stmt->fetchColumn() ?: '';
        // Modelo
        $stmt = $conn->prepare("SELECT nombre_modelo FROM modelo WHERE id_modelo = ?");
        $stmt->execute([$item['id_modelo']]);
        $modelo = $stmt->fetchColumn() ?: '';
        // Tipo
        $stmt = $conn->prepare("SELECT nombre_tipo FROM tipo WHERE id_tipo = ?");
        $stmt->execute([$item['id_tipo']]);
        $tipo = $stmt->fetchColumn() ?: '';
        $productos_pendientes[] = [
            'nombre_producto' => $item['nombre_producto'],
            'marca' => $marca,
            'modelo' => $modelo,
            'tipo' => $tipo,
            'url' => $item['url'],
            'cantidad' => $item['cantidad']
        ];
    }

    // Repuestos
    $sql1 = "SELECT r.id_repuesto, r.nombre_repuesto, r.id_marca, r.id_modelo, r.id_tipo, r.url, rt.cantidad 
             FROM repuesto_tarea rt 
             JOIN repuesto r ON rt.repuesto_id = r.id_repuesto 
             WHERE rt.tarea_id = ? AND rt.status_id = 26";
    $sql2 = "SELECT r.id_repuesto, r.nombre_repuesto, r.id_marca, r.id_modelo, r.id_tipo, r.url, ra.cantidad 
             FROM repuesto_actividad ra 
             JOIN repuesto r ON ra.repuesto_id = r.id_repuesto 
             JOIN actividades a ON ra.actividad_id = a.id_actividad 
             WHERE a.tarea_id = ? AND ra.status_id = 26";
    $stmt = $conn->prepare($sql1); $stmt->execute([$id_tarea]); $rt = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conn->prepare($sql2); $stmt->execute([$id_tarea]); $ra = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $repuestos_tmp = [];
    foreach (array_merge($rt, $ra) as $item) {
        $id = $item['id_repuesto'];
        if (!isset($repuestos_tmp[$id])) {
            $repuestos_tmp[$id] = $item;
            $repuestos_tmp[$id]['cantidad'] = (int)$item['cantidad'];
        } else {
            $repuestos_tmp[$id]['cantidad'] += (int)$item['cantidad'];
        }
    }
    foreach ($repuestos_tmp as $item) {
        $marca = $modelo = $tipo = '';
        $stmt = $conn->prepare("SELECT nombre_marca FROM marca WHERE id_marca = ?");
        $stmt->execute([$item['id_marca']]);
        $marca = $stmt->fetchColumn() ?: '';
        $stmt = $conn->prepare("SELECT nombre_modelo FROM modelo WHERE id_modelo = ?");
        $stmt->execute([$item['id_modelo']]);
        $modelo = $stmt->fetchColumn() ?: '';
        $stmt = $conn->prepare("SELECT nombre_tipo FROM tipo WHERE id_tipo = ?");
        $stmt->execute([$item['id_tipo']]);
        $tipo = $stmt->fetchColumn() ?: '';
        $repuestos_pendientes[] = [
            'nombre_repuesto' => $item['nombre_repuesto'],
            'marca' => $marca,
            'modelo' => $modelo,
            'tipo' => $tipo,
            'url' => $item['url'],
            'cantidad' => $item['cantidad']
        ];
    }

    // Herramientas
    $sql1 = "SELECT h.id_herramienta, h.nombre_herramienta, h.id_marca, h.id_modelo, h.id_tipo, h.url, ht.cantidad 
             FROM herramienta_tarea ht 
             JOIN herramientas h ON ht.herramienta_id = h.id_herramienta 
             WHERE ht.tarea_id = ? AND ht.status_id = 26";
    $sql2 = "SELECT h.id_herramienta, h.nombre_herramienta, h.id_marca, h.id_modelo, h.id_tipo, h.url, ha.cantidad 
             FROM herramienta_actividad ha 
             JOIN herramientas h ON ha.herramienta_id = h.id_herramienta 
             JOIN actividades a ON ha.id_actividad = a.id_actividad 
             WHERE a.tarea_id = ? AND ha.status_id = 26";
    $stmt = $conn->prepare($sql1); $stmt->execute([$id_tarea]); $ht = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $conn->prepare($sql2); $stmt->execute([$id_tarea]); $ha = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $herramientas_tmp = [];
    foreach (array_merge($ht, $ha) as $item) {
        $id = $item['id_herramienta'];
        if (!isset($herramientas_tmp[$id])) {
            $herramientas_tmp[$id] = $item;
            $herramientas_tmp[$id]['cantidad'] = (int)$item['cantidad'];
        } else {
            $herramientas_tmp[$id]['cantidad'] += (int)$item['cantidad'];
        }
    }
    foreach ($herramientas_tmp as $item) {
        $marca = $modelo = $tipo = '';
        $stmt = $conn->prepare("SELECT nombre_marca FROM marca WHERE id_marca = ?");
        $stmt->execute([$item['id_marca']]);
        $marca = $stmt->fetchColumn() ?: '';
        $stmt = $conn->prepare("SELECT nombre_modelo FROM modelo WHERE id_modelo = ?");
        $stmt->execute([$item['id_modelo']]);
        $modelo = $stmt->fetchColumn() ?: '';
        $stmt = $conn->prepare("SELECT nombre_tipo FROM tipo WHERE id_tipo = ?");
        $stmt->execute([$item['id_tipo']]);
        $tipo = $stmt->fetchColumn() ?: '';
        $herramientas_pendientes[] = [
            'nombre_herramienta' => $item['nombre_herramienta'],
            'marca' => $marca,
            'modelo' => $modelo,
            'tipo' => $tipo,
            'url' => $item['url'],
            'cantidad' => $item['cantidad']
        ];
    }
}

$proveedores = [];
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$res = $conexion->query("SELECT id_proveedor, nombre_proveedor FROM proveedor");
while ($f = $res->fetch_assoc()) $proveedores[] = $f;
$conexion->close();
?>

    <script>
        function calcularTotales() {
            let totalGeneral = 0;
            document.querySelectorAll('[data-item]').forEach(row => {
                const cantidad = parseFloat(row.querySelector('[name^=cantidad]').value) || 0;
                const precio = parseFloat(row.querySelector('[name^=precio]').value) || 0;
                const total = cantidad * precio;
                row.querySelector('.total').innerText = total.toFixed(2);
                totalGeneral += total;
            });
            document.getElementById('total_general').innerText = totalGeneral.toFixed(2);
        }
    </script>
<form action="procesar_compra.php" method="POST" class="max-w-7xl mx-auto mt-12 p-8 bg-white shadow-xl rounded-2xl border border-gray-200 space-y-10 text-gray-800">
    <input type="hidden" name="id_tarea" value="<?= $id_tarea ?>">

    <div class="flex justify-between items-center">
        <h2 class="text-3xl font-extrabold text-blue-700">🧾 Planificación de Compra</h2>
        <div class="text-2xl font-bold text-green-700">Total: $<span id="total_general">0.00</span></div>
    </div>

    <?php
    function renderTableWithDetails($items, $type, $color, $icon, $label, $proveedores) {
        if (count($items)) {
            echo "<div class='rounded-xl border border-$color-300 overflow-x-auto'>";
            echo "<table class='min-w-full text-sm text-left'>";
            echo "<thead class='bg-$color-100 text-$color-800 font-semibold uppercase'>";
            echo "<tr>";
            echo "<th class='px-4 py-3'>🧷 Imagen</th>";
            echo "<th class='px-4 py-3'>Nombre</th>";
            echo "<th class='px-4 py-3'>Marca</th>";
            echo "<th class='px-4 py-3'>Modelo</th>";
            echo "<th class='px-4 py-3'>Tipo</th>";
            echo "<th class='px-4 py-3 text-center'>Cantidad</th>";
            echo "<th class='px-4 py-3'>Proveedor</th>";
            echo "<th class='px-4 py-3'>Precio Unitario</th>";
            echo "<th class='px-4 py-3 text-right'>Total</th>";
            echo "</tr></thead><tbody class='divide-y divide-gray-200'>";
            foreach ($items as $item) {
                $nombre = htmlspecialchars($item["nombre_$type"]);
                $marca = htmlspecialchars($item["marca"] ?? '—');
                $modelo = htmlspecialchars($item["modelo"] ?? '—');
                $tipo = htmlspecialchars($item["tipo"] ?? '—');
                $url = htmlspecialchars($item["url"] ?? 'https://via.placeholder.com/60');
                $cantidad = intval($item['cantidad']);
                echo "<tr data-item class='bg-white hover:bg-gray-50 transition'>";
                echo "<td class='px-4 py-2'><img src='$url' alt='Imagen' class='w-14 h-14 object-contain rounded border'></td>";
                echo "<td class='px-4 py-2 font-medium text-$color-800'><input type='text' name='nombre_{$type}[]' value='$nombre' readonly class='w-full bg-transparent'></td>";
                echo "<td class='px-4 py-2'>$marca</td>";
                echo "<td class='px-4 py-2'>$modelo</td>";
                echo "<td class='px-4 py-2'>$tipo</td>";
                echo "<td class='px-4 py-2 text-center'><input type='number' name='cantidad_{$type}[]' value='$cantidad' readonly class='w-16 text-center bg-gray-100 border border-gray-300 rounded'></td>";
                echo "<td class='px-4 py-2'>";
                echo "<select name='proveedor_{$type}[]' class='w-full border border-gray-300 rounded p-1'>";
                echo "<option value=''>Seleccionar</option>";
                foreach ($proveedores as $prov) {
                    echo "<option value='{$prov['id_proveedor']}'>{$prov['nombre_proveedor']}</option>";
                }
                echo "</select></td>";
                echo "<td class='px-4 py-2'><input type='number' step='0.01' min='0' name='precio_{$type}[]' placeholder='0.00' class='w-full text-right border border-gray-300 rounded p-1' oninput='calcularTotales()'></td>";
                echo "<td class='px-4 py-2 text-right font-semibold text-green-700'>$<span class='total'>0.00</span></td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";
        } else {
            echo "<p class='italic text-gray-500 mb-4'>No hay $label pendientes.</p>";
        }
    }

    renderTableWithDetails($herramientas_pendientes, 'herramienta', 'blue', 'fa-tools', 'Herramientas', $proveedores);
    renderTableWithDetails($repuestos_pendientes, 'repuesto', 'purple', 'fa-cogs', 'Repuestos', $proveedores);
    renderTableWithDetails($productos_pendientes, 'producto', 'rose', 'fa-box-open', 'Productos', $proveedores);
    ?>

    <div class="flex justify-end gap-4 pt-6 border-t mt-8">
        <a href="index.php" class="text-gray-600 hover:text-gray-800 border border-gray-300 px-4 py-2 rounded-md transition">← Regresar</a>
        <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-2 rounded-md transition shadow">
            Confirmar Compra
        </button>
    </div>
    <?php
    // Mostrar el usuario activo (logueado)
    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        echo '<div class="mt-4 text-right text-sm text-gray-500">';
        echo 'Usuario activo: <span class="font-semibold text-blue-700">' . htmlspecialchars($_SESSION['username']) . '</span>';
        echo '</div>';
    }
    ?>
    <?php
    // Intentar recuperar el id_usuario si no está en la sesión
    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        // Conexión a la base de datos para obtener el id_usuario usando el username
        $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
        if (!$conexion->connect_error && isset($_SESSION['username'])) {
            $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
            $stmt->bind_param("s", $_SESSION['username']);
            $stmt->execute();
            $stmt->bind_result($id_usuario);
            if ($stmt->fetch()) {
                $_SESSION['id_usuario'] = $id_usuario;
            }
            $stmt->close();
            $conexion->close();
        }
    }
    ?>
    <?php if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])): ?>
        <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($_SESSION['id_usuario']) ?>">
    <?php else: ?>
        <input type="hidden" name="id_usuario" value="">
        <script>
            alert('Error: No se encontró el ID de usuario en la sesión. Por favor, vuelva a iniciar sesión.');
        </script>
    <?php endif; ?>

</form>

<script>
function calcularTotales() {
    const rows = document.querySelectorAll('[data-item]');
    let totalGeneral = 0;
    rows.forEach(row => {
        const cantidad = parseFloat(row.querySelector('[name^="cantidad_"]').value) || 0;
        const precio = parseFloat(row.querySelector('[name^="precio_"]').value) || 0;
        const total = cantidad * precio;
        row.querySelector('.total').textContent = total.toFixed(2);
        totalGeneral += total;
    });
    document.getElementById('total_general').textContent = totalGeneral.toFixed(2);
}
</script>
