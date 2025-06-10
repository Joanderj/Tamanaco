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


</div>
     <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
 <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
 <div class="flex flex-col items-center mb-6">
    <div class="bg-green-600 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
        <i class="fas fa-tools text-3xl"></i> <!-- Ícono de mantenimiento -->
    </div>
    <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Tarea de Mantenimiento</h2>
    <p class="text-gray-600 mt-2 text-center">Planifica y gestiona las tareas de mantenimiento de forma eficiente.</p>
</div>

    <!-- Contenedor global para mensajes de error -->
    <div id="mensaje-global" class="hidden bg-red-100 text-red-700 p-4 rounded-lg mb-4">
        <strong id="tipo-mensaje-global"></strong> <span id="texto-mensaje-global"></span>
    </div>

<form id="formTarea" action="guardar_tarea.php" method="POST">

      <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white">
    <div class="mb-6">
    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white">
    <!-- Texto flotante sobre el borde -->
    <label for="tipo_mantenimiento" class="absolute -top-3 left-4 bg-white px-2 text-blue-600 text-sm font-semibold">
        Tipo de Mantenimiento
    </label>
 
    <!-- Select con diseño mejorado -->
    <select id="tipo_mantenimiento" name="tipo_mantenimiento"
        class="w-full border border-blue-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        <option value="" disabled selected>Seleccione el tipo de mantenimiento</option>
        <?php
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT id_tipo, nombre_tipo FROM tipo_mantenimiento";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            while ($tipo = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<option value="' . htmlspecialchars($tipo["id_tipo"]) . '">' . htmlspecialchars($tipo["nombre_tipo"]) . '</option>';
            }
        } catch (PDOException $e) {
            echo '<option value="">Error al cargar tipos de mantenimiento</option>';
        }
        ?>
    </select>
</div>
    </div>
    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white">
    <div class="mb-6">
    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white">
    <!-- Texto flotante sobre el borde -->
    <label for="id_importancia" class="absolute -top-3 left-4 bg-white px-2 text-blue-600 text-sm font-semibold">
        nivel de importancia
    </label>

    <!-- Select con diseño mejorado -->
    <select id="id_importancia" name="id_importancia"
        class="w-full border border-blue-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        <option value="" disabled selected>Seleccione el nivel de importancia</option>
        <?php
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "SELECT id_importancia, nivel FROM prioridad";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            while ($prioridad = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($prioridad["id_importancia"]) . '">' . htmlspecialchars($prioridad["nivel"]) . '</option>';
            }
        } catch (PDOException $e) {
            echo '<option value="">Error al cargar niveles de importancia</option>';
        }
        ?>
    </select>
</div>
    </div>
    <div class="flex justify-center space-x-6">

    <!-- Botón de mantenimiento interno -->
    <label class="flex items-center justify-between w-80 px-6 py-4 bg-white rounded-md shadow-md border border-gray-400 cursor-pointer">
        <div class="flex items-center space-x-4">
            <i class="fas fa-building text-green-500 text-xl"></i> <!-- Ícono representativo -->
            <span class="text-gray-700 font-medium">Interno</span>
        </div>
        <input type="radio" name="categoria_mantenimiento" value="interno" class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-2 focus:ring-green-500">
    </label>

    <!-- Botón de mantenimiento externo -->
    <label class="flex items-center justify-between w-80 px-6 py-4 bg-white rounded-md shadow-md border border-gray-400 cursor-pointer">
        <div class="flex items-center space-x-4">
            <i class="fas fa-truck text-red-500 text-xl"></i> <!-- Ícono representativo -->
            <span class="text-gray-700 font-medium">Externo</span>
        </div>
        <input type="radio" name="categoria_mantenimiento" value="externo" class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-2 focus:ring-red-500" id="radioExterno">
    </label>
</div>
 <!-- SELECT DE PROVEEDOR -->
<div id="selectProveedorContainer" class="mt-4 hidden">
    <label for="proveedor" class="block text-md font-medium text-gray-700">Seleccione un proveedor:</label>
    <select id="proveedor" name="proveedor" class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        <option value="" disabled selected>Seleccione un proveedor</option>
        <?php
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=bd_tamanaco;charset=utf8", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "SELECT id_proveedor, nombre_proveedor FROM proveedor WHERE id_status = 1 ORDER BY nombre_proveedor ASC";
            $stmt = $pdo->query($sql);
            while ($proveedor = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($proveedor["id_proveedor"]) . '">' . htmlspecialchars($proveedor["nombre_proveedor"]) . '</option>';
            }
        } catch (PDOException $e) {
            echo '<option value="">Error al cargar proveedores</option>';
        }
        ?>
    </select>
</div>

   
</div>
</div>
    <div class="relative w-full  border border-gray-300 rounded-lg shadow-md p-4 bg-white">
    <div class="mb-6">
    <label for="maquina" class="block text-lg font-semibold flex items-center mb-2">
        <i class="fas fa-search text-blue-600 mr-2"></i> Buscar en
    </label>

    <!-- Toggle Switch -->
    <div class="flex items-center space-x-4">
        <span class="text-gray-700 font-medium">Sede</span>
        <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" id="toggleSede" class="sr-only">
        <div class="w-12 h-6 bg-gray-300 rounded-full transition duration-300 ease-in-out shadow-md" id="toggleBackground">
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow toggle-circle" id="toggleCircle"></div>
        </div>
    </label>
    </div>
    <!-- Select de Sedes (Oculto por defecto) -->
    <div id="selectSedeContainer" class="mt-4 hidden">
    <label for="sede" class="block text-md font-medium text-gray-700">Seleccione una sede:</label>
    <select id="sede" name="sede" class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        <option value="" disabled selected>Seleccione una sede</option>
        <?php
        // Configuración de la base de datos
        $host = "localhost";
        $dbname = "bd_tamanaco";
        $user = "root";
        $password = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Consulta para obtener las sedes activas en orden alfabético
            $sql = "SELECT id_sede, nombre_sede FROM sede WHERE id_status = 1 ORDER BY nombre_sede ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            // Generar opciones dinámicas ordenadas
            while ($sede = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($sede["id_sede"]) . '">' . htmlspecialchars($sede["nombre_sede"]) . '</option>';
            }
        } catch (PDOException $e) {
            echo '<option value="">Error al cargar sedes</option>';
        }
        ?>
    </select>
</div>
</div>

<!-- Script para mostrar el select cuando el toggle esté activo -->
<script>
    document.getElementById("toggleSede").addEventListener("change", function () {
        const selectContainer = document.getElementById("selectSedeContainer");
        if (this.checked) {
            selectContainer.classList.remove("hidden");
        } else {
            selectContainer.classList.add("hidden");
        }
    });
</script>
<div class="relative w-full  border border-blue-300 rounded-lg shadow-md p-4 bg-white">
    <!-- Texto flotante sobre el borde -->
    <label for="dropdownBtn" class="absolute -top-3 left-4 bg-white px-2 text-blue-600 text-sm font-semibold">
        Máquina
    </label>

    <!-- Botón de selección de máquina -->
    <button type="button" id="dropdownBtn"
        class="w-full flex items-center justify-between bg-white hover:bg-gray-50 transition">
        <div class="flex items-center space-x-3">
            <img id="selectedImage" src="img/selecionar maquina.jpg" alt="Imagen de máquina"
                class="w-12 h-12 object-cover rounded-md border border-gray-300">
            <span id="selectedName" class="text-gray-700 font-medium">Seleccione una máquina</span>
        </div>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </button>
</div>

    <!-- Menú desplegable oculto por defecto -->
    <div id="dropdownMenu"
        class="absolute hidden w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-2 max-h-60 overflow-y-auto z-50">
        <!-- Opciones dinámicas se insertarán aquí -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const dropdownMenu = document.getElementById("dropdownMenu");
    const dropdownBtn = document.getElementById("dropdownBtn");
    const selectedImage = document.getElementById("selectedImage");
    const selectedName = document.getElementById("selectedName");
    const sedeSelect = document.getElementById("sede");
    const toggleSede = document.getElementById("toggleSede");

    const inputIdMaquinaUnica = document.getElementById("id_maquina_unica");
    const inputIdSede = document.getElementById("id_sede");
    const servicioSelect = document.getElementById("servicio");

    function cargarMaquinas() {
        const sedeId = toggleSede.checked ? sedeSelect.value : "";
        const url = "obtener_maquinas.php" + (sedeId ? `?id_sede=${sedeId}` : "");

        fetch(url)
            .then(response => response.json())
            .then(data => {
                dropdownMenu.innerHTML = data.map(maquina => `
                    <div class="flex items-center p-3 hover:bg-gray-100 cursor-pointer transition"
                        onclick="seleccionarMaquina('${maquina.url}', '${maquina.nombre_maquina}', ${maquina.id_maquina_unica}, ${maquina.id_sede})">
                        <img src="${maquina.url}" alt="${maquina.nombre_maquina}" class="w-12 h-12 object-cover rounded-md border border-gray-300">
                        <div class="ml-3 text-left">
                            <p class="text-gray-800 font-semibold">${maquina.nombre_maquina} ${maquina.CodigoUnico}</p>
                            <p class="text-sm text-gray-500">
                                ${maquina.nombre_marca} - ${maquina.nombre_modelo} - ${maquina.nombre_tipo}
                            </p>
                        </div>
                    </div>
                `).join("");
            })
            .catch(error => {
                console.error("Error al cargar máquinas:", error);
                dropdownMenu.innerHTML = '<div class="p-3 text-red-600">Error al cargar máquinas</div>';
            });
    }

    // Cargar servicios por máquina
    function cargarServiciosPorMaquina(idMaquinaUnica) {
        if (!servicioSelect) return;
        servicioSelect.innerHTML = '<option value="" disabled selected>Cargando servicios...</option>';

        fetch("cargar_servicios_por_maquina.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id_maquina_unica=${encodeURIComponent(idMaquinaUnica)}`
        })
        .then(res => res.json())
        .then(data => {
            servicioSelect.innerHTML = '';

            if (Array.isArray(data)) {
                if (data.length === 0) {
                    servicioSelect.innerHTML = '<option value="">No hay servicios disponibles</option>';
                    return;
                }

                servicioSelect.innerHTML = '<option value="" disabled selected>Seleccione un servicio</option>';
                data.forEach(s => {
                    const option = document.createElement("option");
                    option.value = s.id_servicio;
                    option.textContent = s.nombre_servicio;
                    option.setAttribute("data-descripcion", s.descripcion);
                    servicioSelect.appendChild(option);
                });
            } else if (data.error) {
                servicioSelect.innerHTML = `<option value="">${data.error}</option>`;
            }
        })
        .catch(err => {
            console.error("Error al cargar servicios:", err);
            servicioSelect.innerHTML = '<option value="">Error al cargar servicios</option>';
        });
    }

    // Función global para selección
    window.seleccionarMaquina = function (imagen, nombre, idMaquinaUnica, idSede) {
        selectedImage.src = imagen;
        selectedName.textContent = nombre;
        dropdownMenu.classList.add("hidden");

        // Guardar los ID en campos ocultos
        inputIdMaquinaUnica.value = idMaquinaUnica;
        inputIdSede.value = idSede;

        const inputNombreMaquina = document.getElementById("nombre_maquina");
        if (inputNombreMaquina) {
            inputNombreMaquina.value = nombre;
        }

        // Cargar los servicios de la máquina seleccionada
        cargarServiciosPorMaquina(idMaquinaUnica);
    };

    // Listeners
    dropdownBtn.addEventListener("click", function () {
        dropdownMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", function (event) {
        if (!dropdownMenu.contains(event.target) && !dropdownBtn.contains(event.target)) {
            dropdownMenu.classList.add("hidden");
        }
    });

    sedeSelect.addEventListener("change", cargarMaquinas);
    toggleSede.addEventListener("change", cargarMaquinas);

    // Al cargar la página, si ya hay un ID de máquina, cargar sus servicios
    if (inputIdMaquinaUnica && inputIdMaquinaUnica.value) {
        cargarServiciosPorMaquina(inputIdMaquinaUnica.value);
    }

    // Cargar las máquinas inicialmente
    cargarMaquinas();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectServicio = document.getElementById('servicio');

    selectServicio.addEventListener('change', function () {
        const servicioID = this.value;
        if (!servicioID) return;

        fetch('obtener_datos_servicio.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_servicio=' + encodeURIComponent(servicioID)
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            // ✅ Autocompletar campos editables
            document.getElementById('titulo_mantenimiento').value = data.nombre_servicio || '';
            if (typeof quill !== 'undefined' && quill.root) {
                quill.root.innerHTML = data.descripcion || '';
            }
            document.getElementById('horas_mantenimiento').value = data.tiempo_programado || '';
            document.getElementById('horas_parada').value = data.tiempo_paro_maquina || '';

            // ✅ Agregar repuestos seleccionados
            if (Array.isArray(data.repuestos)) {
                repuestosSeleccionados = []; // limpiar primero
                data.repuestos.forEach(r => {
                    const repuesto = {
                        id: r.id,
                        nombre: r.nombre_repuesto,
                        unidad: r.unidad,
                        clasificacion: r.clasificacion,
                        imagen: r.imagen || 'placeholder.jpg',
                        marca: r.marca,
                        modelo: r.modelo,
                        tipo: r.tipo,
                        cantidad: r.cantidad <= r.disponible ? r.cantidad : r.disponible,
                        pendiente: r.cantidad > r.disponible ? (r.cantidad - r.disponible) : 0,
                        disponible: r.disponible,
                        stockMinimo: r.stock_minimo || 0,
                        stockMaximo: r.stock_maximo || 10
                    };
                    repuestosSeleccionados.push(repuesto);
                });
                renderizarTablaRepuestos();
            }

            // ✅ Agregar productos seleccionados
            if (Array.isArray(data.productos)) {
                productosSeleccionados = []; // limpiar primero
                data.productos.forEach(p => {
                    const producto = {
                        id: p.id,
                        nombre: p.nombre_producto,
                        unidad: p.unidad,
                        clasificacion: p.clasificacion,
                        imagen: p.imagen || 'placeholder.jpg',
                        marca: p.marca,
                        modelo: p.modelo,
                        tipo: p.tipo,
                        cantidad: p.cantidad <= p.disponible ? p.cantidad : p.disponible,
                        pendiente: p.cantidad > p.disponible ? (p.cantidad - p.disponible) : 0,
                        disponible: p.disponible,
                        stockMinimo: p.stock_minimo || 0,
                        stockMaximo: p.stock_maximo || 10
                    };
                    productosSeleccionados.push(producto);
                });
                renderizarTablaProductos();
            }

            // ✅ Agregar herramientas seleccionadas
            if (Array.isArray(data.herramientas)) {
                herramientasSeleccionadas = []; // limpiar primero
                data.herramientas.forEach(h => {
                    const herramienta = {
                        id: h.id,
                        nombre: h.nombre_herramienta,
                        unidad: h.unidad,
                        clasificacion: h.clasificacion,
                        imagen: h.imagen || 'placeholder.jpg',
                        marca: h.marca,
                        modelo: h.modelo,
                        tipo: h.tipo,
                        cantidad: h.cantidad <= h.disponible ? h.cantidad : h.disponible,
                        pendiente: h.cantidad > h.disponible ? (h.cantidad - h.disponible) : 0,
                        disponible: h.disponible,
                        stockMinimo: h.stock_minimo || 0,
                        stockMaximo: h.stock_maximo || 10
                    };
                    herramientasSeleccionadas.push(herramienta);
                });
                renderizarTablaHerramientas();
            }
        })
        .catch(err => {
            console.error('Error al cargar servicio:', err);
            alert('Error al obtener los datos del servicio.');
        });
    });
});

</script>



<input type="hidden" name="id_sede" id="id_sede">
<!-- Campo oculto con el ID de máquina única -->
<input type="hidden" name="id_maquina_unica" id="id_maquina_unica" value="">
<input type="hidden" name="id_sede" id="id_sede">
<input type="hidden" name="nombre_maquina" id="nombre_maquina">



<!-- SELECT DE SERVICIO -->
<div id="selectServicioContainerr" class="mt-4">
    <label for="servicio" class="block text-md font-medium text-gray-700">Seleccione un servicio:</label>
    <select id="servicio" name="servicio" class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        <option value="" disabled selected>Seleccione un servicio</option>
    </select>
</div>



<div id="costoMantenimientoContainer" class="hidden">
    <label for="costo_mantenimiento" class="block text-lg font-semibold text-gray-700">
        <i class="fas fa-dollar-sign text-blue-600 mr-2"></i> Costo del Mantenimiento
    </label>
    <div class="relative w-full">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 text-lg">
            <i class="fas fa-dollar-sign"></i>
        </span>
        <input type="number" id="costo_mantenimiento" name="costo_mantenimiento"
            class="w-full pl-8 border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 shadow-sm"
            placeholder="Escribe el costo aquí..." min="0" step="0.01">
    </div>
</div>


  <div id="contenedorTituloMantenimiento" class="relative w-full mt-4">
    <label for="titulo_mantenimiento" class="block text-lg font-semibold text-gray-700">
        <i class="fas fa-wrench text-blue-600 mr-2"></i> Título del Mantenimiento
    </label>
    <input type="text" id="titulo_mantenimiento" name="titulo_mantenimiento"
        class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 shadow-sm"
        placeholder="Escribe el título del mantenimiento aquí...">
</div>
<div id="contenedorDescripcionMantenimiento" class="mb-6 mt-4">
    <label for="descripcion_funcionamiento" class="block text-lg font-semibold flex items-center">
        <i class="fas fa-tools text-blue-600 mr-2"></i> Descripción del Mantenimiento:
    </label>
<!-- Hidden input for description -->
<input type="hidden" id="descripcion_tarea" name="descripcion_tarea">

<!-- Quill Editor Container -->
<div id="editor" class="w-full p-2 bg-white">
</div>

<div class="flex space-x-1 my-6">
  <hr class="w-1/3 border-t-4 border-red-500">
  <hr class="w-1/3 border-t-4 border-sky-500">
  <hr class="w-1/3 border-t-4 border-blue-500">
</div>




<!-- Cargar Quill.js -->
<script src="js/quill.min.js"></script>
<script>
    // Initialize Quill Editor
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link']
            ]
        }
    });

    // Capture the content before submitting the form
    document.querySelector('#formTarea').addEventListener('submit', function (event) {
        const contenidoQuill = quill.root.innerHTML.trim();
        const textoPlano = quill.getText().trim(); // Capture plain text

        if (!textoPlano || textoPlano === '<p><br></p>') {
            alert('La descripción es obligatoria.');
            event.preventDefault(); // Block submission if empty
            return false;
        }

        // Assign content to the hidden input
        document.getElementById('descripcion_tarea').value = contenidoQuill;

        // Additional validation to ensure the hidden field has a value before submitting
        if (!document.getElementById('descripcion_tarea').value) {
            alert('Error: La descripción aún no se ha asignado correctamente.');
            event.preventDefault();
            return false;
        }

        return true;
    });
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const radioInterno = document.querySelector('input[name="categoria_mantenimiento"][value="interno"]');
    const radioExterno = document.querySelector('input[name="categoria_mantenimiento"][value="externo"]');

    const contenedorProveedor = document.getElementById("selectProveedorContainer");
    const contenedorServicio = document.getElementById("selectServicioContainer");
    const contenedorTitulo = document.getElementById("contenedorTituloMantenimiento");
    const contenedorDescripcion = document.getElementById("contenedorDescripcionMantenimiento");
    const contenedorCosto = document.getElementById("costoMantenimientoContainer"); // Asegúrate de agregar un contenedor a este input

    function actualizarVisibilidad() {
        if (radioExterno.checked) {
            contenedorProveedor?.classList.remove("hidden");
            contenedorServicio?.classList.remove("hidden");
            contenedorTitulo?.classList.remove("hidden");
            contenedorDescripcion?.classList.remove("hidden");
            contenedorCosto?.classList.remove("hidden");
        } else if (radioInterno.checked) {
            contenedorProveedor?.classList.add("hidden");
            contenedorServicio?.classList.add("hidden");
            contenedorTitulo?.classList.remove("hidden");
            contenedorDescripcion?.classList.remove("hidden");
            contenedorCosto?.classList.add("hidden");
        }
    }

    if (radioInterno && radioExterno) {
        radioInterno.addEventListener("change", actualizarVisibilidad);
        radioExterno.addEventListener("change", actualizarVisibilidad);
        actualizarVisibilidad(); // Inicializar al cargar
    }
});
</script>




    <div class="relative w-full border border-grey-300 rounded-lg shadow-md p-4 bg-white">
    <label class="block text-lg font-semibold flex items-center">
        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Planificación
    </label>
<!-- Toggle Switch - Todo el día -->
<div class="flex items-center space-x-4 mt-3">
    <span class="text-gray-700 font-medium">Todo el día</span>
    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" id="toggleFullDay" class="sr-only">
        <div class="w-12 h-6 bg-gray-300 rounded-full transition duration-300 ease-in-out shadow-md" id="fullDayWrapper">
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow toggle-circle" id="fullDayKnob"></div>
        </div>
    </label>
</div>


    <!-- Campos de Fecha y Hora en formato 2x2 -->
    <div id="fechaHoraContainer" class="mt-4 grid grid-cols-2 gap-4">
        <div>
            <label for="fecha_inicio" class="block text-md font-medium text-gray-700">Fecha de inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio"
                class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="hora_inicio" class="block text-md font-medium text-gray-700">Hora de inicio:</label>
            <input type="time" id="hora_inicio" name="hora_inicio"
                class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="fecha_fin" class="block text-md font-medium text-gray-700">Fecha de finalización:</label>
            <input type="date" id="fecha_fin" name="fecha_fin"
                class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="hora_fin" class="block text-md font-medium text-gray-700">Hora de finalización:</label>
            <input type="time" id="hora_fin" name="hora_fin"
                class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const fechaInicio = document.getElementById("fecha_inicio");
    const horaInicio = document.getElementById("hora_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const horaFin = document.getElementById("hora_fin");
    const toggleFullDay = document.getElementById("toggleFullDay");
    const inputHoras = document.getElementById("horas_mantenimiento");
    const guardarBtn = document.getElementById("guardar");

    function actualizarVisibilidadHoras() {
        const mostrarHoras = !toggleFullDay.checked;
        horaInicio.parentElement.style.display = mostrarHoras ? "block" : "none";
        horaFin.parentElement.style.display = mostrarHoras ? "block" : "none";
    }

    function calcularFechaFin() {
        const fInicio = fechaInicio.value;
        const hInicio = horaInicio.value;
        const horasMantenimiento = parseFloat(inputHoras.value);

        if (!fInicio || isNaN(horasMantenimiento) || horasMantenimiento <= 0) {
            fechaFin.value = "";
            horaFin.value = "";
            guardarBtn.disabled = true;
            return;
        }

        const esTodoElDia = toggleFullDay.checked;

        let fechaHoraInicio;
        if (esTodoElDia) {
            fechaHoraInicio = new Date(`${fInicio}T00:00`);
        } else {
            if (!hInicio) {
                fechaFin.value = "";
                horaFin.value = "";
                guardarBtn.disabled = true;
                return;
            }
            fechaHoraInicio = new Date(`${fInicio}T${hInicio}`);
        }

        const msAgregar = horasMantenimiento * 60 * 60 * 1000;
        const fechaHoraFin = new Date(fechaHoraInicio.getTime() + msAgregar);

        // Asignar la nueva fecha y hora de fin
        fechaFin.value = fechaHoraFin.toISOString().slice(0, 10);
        if (!esTodoElDia) {
            horaFin.value = fechaHoraFin.toTimeString().slice(0, 5);
        } else {
            horaFin.value = ""; // se oculta de todas formas
        }

        guardarBtn.disabled = false;
    }

    // Cuando cambian los inputs clave, recalculamos la fecha/hora de fin
    [fechaInicio, horaInicio, inputHoras].forEach(input => {
        input.addEventListener("change", calcularFechaFin);
    });

    toggleFullDay.addEventListener("change", () => {
        actualizarVisibilidadHoras();
        calcularFechaFin();
    });

    actualizarVisibilidadHoras(); // Inicial
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const fechaInicio = document.getElementById("fecha_inicio");
    const horaInicio = document.getElementById("hora_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const horaFin = document.getElementById("hora_fin");
    const toggleFullDay = document.getElementById("toggleFullDay");
    const horasMantenimiento = document.getElementById("horas_mantenimiento");
    const minutosMantenimiento = document.getElementById("minutos_mantenimiento");

    function actualizarFechaFinDesdeDuracion() {
        if (toggleFullDay.checked) return; // no aplica si es todo el día

        const fInicio = fechaInicio.value;
        const hInicio = horaInicio.value;

        if (!fInicio || !hInicio) return;

        const horas = parseInt(horasMantenimiento.value) || 0;
        const minutos = parseInt(minutosMantenimiento.value) || 0;

        const inicio = new Date(`${fInicio}T${hInicio}`);

        // Sumar duración
        inicio.setHours(inicio.getHours() + horas);
        inicio.setMinutes(inicio.getMinutes() + minutos);

        // Formato para actualizar inputs
        const fechaFinFormateada = inicio.toISOString().split("T")[0];
        const horaFinFormateada = inicio.toTimeString().slice(0, 5);

        fechaFin.value = fechaFinFormateada;
        horaFin.value = horaFinFormateada;
    }

    // Escuchar cambios en horas/minutos de mantenimiento
    [horasMantenimiento, minutosMantenimiento].forEach(input => {
        input.addEventListener("input", actualizarFechaFinDesdeDuracion);
    });

    // También actualizar si se cambia la hora o fecha de inicio
    [fechaInicio, horaInicio].forEach(input => {
        input.addEventListener("change", actualizarFechaFinDesdeDuracion);
    });

    // Opcional: resetear fecha/hora fin si se activa "Todo el día"
    toggleFullDay.addEventListener("change", function () {
        if (toggleFullDay.checked) {
            horaFin.value = "";
        } else {
            actualizarFechaFinDesdeDuracion();
        }
    });
});
</script>




    <!-- Campo de Fecha sin Hora (oculto por defecto) -->
    <div id="fechaSoloContainer" class="mt-4 hidden">
        <label for="fecha_solo" class="block text-md font-medium text-gray-700">Fecha incluida:</label>
        <input type="date" id="fecha_solo" name="fecha_solo"
            class="w-full border border-gray-300 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500">
    </div>
    </div>
    <hr>
    <div class="grid grid-cols-2 gap-6 mt-3">
        <div class="relative w-full">
            <label for="horas_mantenimiento"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Tiempo de Mantenimiento Programado:
            </label>
            <input type="number" id="horas_mantenimiento" name="horas_mantenimiento"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
                min="0" max="9999">
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Horas</span>
        </div>
        <div class="relative w-full">
            <label for="minutos_mantenimiento"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Minutos:
            </label>
            <input type="number" id="minutos_mantenimiento" name="minutos_mantenimiento"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-blue-500"
                min="0" max="60">
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Minutos</span>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-6 mt-3">
        <div class="relative w-full">
            <label for="horas_parada"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Tiempo de Parada Programada
            </label>
            <input type="number" id="horas_parada" name="horas_parada"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-red-500"
                min="0" max="9999" >
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Horas</span>
        </div>
        <div class="relative w-full">
            <label for="minutos_parada"
                class="absolute -top-3 left-4 bg-white px-2 text-gray-700 text-sm font-semibold">
                Minutos:
            </label>
            <input type="number" id="minutos_parada" name="minutos_parada"
                class="w-full border border-gray-400 rounded-md p-3 bg-white shadow-sm focus:ring-2 focus:ring-red-500"
                min="0" max="60" >
                <span class="absolute right-3 top-3 text-gray-600 text-sm">Minutos</span>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");

    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    const fechaMinima = `${yyyy}-${mm}-${dd}`;

    // Aplicar restricciones de fecha mínima
    fechaInicio.min = fechaMinima;
    fechaFin.min = fechaMinima;

    // También actualiza la fecha mínima de fin según la fecha de inicio seleccionada
    fechaInicio.addEventListener("change", function () {
        if (fechaInicio.value) {
            fechaFin.min = fechaInicio.value;
            if (fechaFin.value < fechaInicio.value) {
                fechaFin.value = fechaInicio.value;
            }
        }
    });
});
</script>


<!-- Script para Validación -->
<script>
    function validarLimites(input, max) {
        input.addEventListener("input", function () {
            if (parseInt(this.value) > max) {
                this.value = max;
            }
            if (parseInt(this.value) < 0 || this.value === "") {
                this.value = 0;
            }
        });
    }

    // Aplicar validación a los campos
    validarLimites(document.getElementById("horas_mantenimiento"), 9999);
    validarLimites(document.getElementById("minutos_mantenimiento"), 60);
    validarLimites(document.getElementById("horas_parada"), 9999);
    validarLimites(document.getElementById("minutos_parada"), 60);
</script>


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
<input type="hidden" name="tarea_insumos" id="tarea_insumos">

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
let repuestosServicio = [];

function totalRepuestosAgregados() {
  return repuestosSeleccionados.length + repuestosServicio.length;
}

// --- CARGA INTERNO ---
function toggleDropdownRepuestos() {
  if (totalRepuestosAgregados() >= 2) {
    alert("Solo puedes agregar hasta 2 repuestos.");
    return;
  }
  document.getElementById("dropdownRepuestos").classList.toggle("hidden");
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
  if (totalRepuestosAgregados() >= 2) {
    alert("Límite de 2 repuestos alcanzado.");
    return;
  }
  if (repuestosSeleccionados.some(r => r.id === id)) {
    alert("Este repuesto ya fue agregado desde inventario.");
    return;
  }

  const r = {
    id, nombre, unidad, clasificacion, imagen,
    marca, modelo, tipo,
    cantidad: disponible > 0 ? 1 : 0,
    pendiente: disponible > 0 ? 0 : 1,
    disponible, stockMaximo,
    origen: 'interno'
  };
  repuestosSeleccionados.push(r);
  renderizarTablaRepuestos();
  document.getElementById("dropdownRepuestos").classList.add("hidden");
}

// --- CARGA EXTERNO ---
function toggleDropdownRepuestosServicios() {
  if (totalRepuestosAgregados() >= 2) {
    alert("Solo puedes agregar hasta 2 repuestos.");
    return;
  }
  document.getElementById("dropdownRepuestosServicios").classList.toggle("hidden");
  cargarRepuestosServicios(1);
}

function cargarRepuestosServicios(pagina = 1) {
  const buscar = document.getElementById("buscarServicioRepuesto").value;
  fetch(`buscar_servicios_repuestos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("contenedorRepuestosServicios").innerHTML = html;
    });
}

function agregarRepuestoDesdeServicio(id, nombre) {
  if (totalRepuestosAgregados() >= 2) {
    alert("Límite de 2 repuestos alcanzado.");
    return;
  }
  if (repuestosServicio.some(r => r.id === id)) {
    alert("Este repuesto ya fue agregado desde servicio.");
    return;
  }
  repuestosServicio.push({
    id, nombre, cantidad: 1, origen: 'externo'
  });
  renderizarTablaRepuestos();
  document.getElementById("dropdownRepuestosServicios").classList.add("hidden");
}

// --- CARGA AUTOMÁTICA DE SERVICIO YA GUARDADO ---
function cargarRepuestosDesdeServicioExistente(data) {
  data.forEach(item => {
    if (item.origen === 'interno') {
      repuestosSeleccionados.push({
        id: item.id,
        nombre: item.nombre,
        unidad: item.unidad || '',
        clasificacion: item.clasificacion || '',
        imagen: item.imagen || '',
        marca: item.marca || '',
        modelo: item.modelo || '',
        tipo: item.tipo || '',
        cantidad: item.status_id === 25 ? item.cantidad : 0,
        pendiente: item.status_id === 26 ? item.cantidad : 0,
        disponible: item.disponible || 0,
        stockMaximo: item.stockMaximo || 0,
        origen: 'interno'
      });
    } else if (item.origen === 'externo') {
      repuestosServicio.push({
        id: item.id,
        nombre: item.nombre,
        cantidad: item.cantidad,
        origen: 'externo'
      });
    }
  });

  renderizarTablaRepuestos();
}

// --- TABLA ---
function renderizarTablaRepuestos() {
  const cuerpo = document.getElementById("listaRepuestosAgregados");
  const tabla = document.getElementById("tablaRepuestosAgregados");
  cuerpo.innerHTML = "";

  repuestosSeleccionados.forEach((r, i) => {
    const total = r.cantidad + r.pendiente;
    cuerpo.innerHTML += `
      <tr class="border-b">
        <td class="p-2"><img src="${r.imagen}" class="w-12 h-12 rounded" /></td>
        <td class="p-2">
          <strong>${r.nombre}</strong><br>
          <small>${r.marca} / ${r.modelo} / ${r.tipo}</small><br>
          <small class="text-blue-600">Origen: Inventario</small>
        </td>
        <td class="p-2 text-center">${r.disponible}</td>
        <td class="p-2 text-center">${r.stockMaximo}</td>
        <td class="p-2 text-center">
          <input type="number" value="${total}" min="1"
            onblur="actualizarCantidadRepuesto(${i}, this.value)"
            class="w-20 text-center border rounded p-1" />
          <div class="text-xs text-green-700">Planificado: ${r.cantidad}</div>
          ${r.pendiente > 0 ? `<div class="text-xs text-yellow-600">Pendiente: ${r.pendiente}</div>` : ''}
          ${total > r.stockMaximo ? `<div class="text-xs text-red-600">Supera stock máximo</div>` : ''}
        </td>
        <td class="p-2 text-center">
          <button onclick="verRepuesto(${r.id})" class="text-blue-600"><i class="fas fa-eye"></i></button>
          <button onclick="quitarRepuestoInterno(${i})" class="text-red-600"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>`;
  });

  repuestosServicio.forEach((r, i) => {
    cuerpo.innerHTML += `
      <tr class="border-b bg-gray-100">
        <td class="p-2"><i class="fas fa-tools text-xl"></i></td>
        <td class="p-2">
          <strong>${r.nombre}</strong><br>
          <small class="text-purple-600">Origen: Servicio externo</small>
        </td>
        <td class="p-2 text-center" colspan="2">N/A</td>
        <td class="p-2 text-center">
          <input type="number" value="${r.cantidad}" min="1"
            onblur="actualizarCantidadServicio(${i}, this.value)"
            class="w-20 text-center border rounded p-1" />
        </td>
        <td class="p-2 text-center">
          <button onclick="quitarRepuestoServicio(${i})" class="text-red-600"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>`;
  });

  tabla.classList.toggle("hidden", totalRepuestosAgregados() === 0);
  actualizarInputsOcultosRepuestos();
}

// --- ACTUALIZACIONES ---
function actualizarCantidadRepuesto(i, valor) {
  const nueva = parseInt(valor);
  const r = repuestosSeleccionados[i];
  if (isNaN(nueva) || nueva < 1) return;

  if (nueva <= r.disponible) {
    r.cantidad = nueva;
    r.pendiente = 0;
  } else {
    const pendiente = nueva - r.disponible;
    mostrarModalPendienteRepuesto(pendiente, () => {
      r.cantidad = r.disponible;
      r.pendiente = pendiente;
      renderizarTablaRepuestos();
    }, () => {
      renderizarTablaRepuestos();
    });
  }

  renderizarTablaRepuestos();
}

function actualizarCantidadServicio(i, valor) {
  const nueva = parseInt(valor);
  if (!isNaN(nueva) && nueva > 0) {
    repuestosServicio[i].cantidad = nueva;
  } else {
    alert("Cantidad no válida");
  }
  renderizarTablaRepuestos();
}

// --- QUITAR ---
function quitarRepuestoInterno(i) {
  repuestosSeleccionados.splice(i, 1);
  renderizarTablaRepuestos();
}

function quitarRepuestoServicio(i) {
  repuestosServicio.splice(i, 1);
  renderizarTablaRepuestos();
}

// --- MODAL ---
function mostrarModalPendienteRepuesto(pendiente, aceptar, cancelar) {
  const modal = document.createElement("div");
  modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
  modal.id = "modalPendienteRepuesto";
  modal.innerHTML = `
    <div class="bg-white p-6 rounded shadow-lg text-center w-80">
      <p>¿Agregar <strong>${pendiente}</strong> como <span class="text-yellow-600">pendiente</span>?</p>
      <div class="mt-4 flex justify-around">
        <button onclick="aceptarPendienteRepuesto()" class="bg-green-600 text-white px-4 py-1 rounded">Sí</button>
        <button onclick="cancelarPendienteRepuesto()" class="bg-gray-600 text-white px-4 py-1 rounded">No</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
  window.aceptarPendienteRepuesto = () => {
    aceptar();
    cerrarModalPendienteRepuesto();
  };
  window.cancelarPendienteRepuesto = () => {
    cancelar();
    cerrarModalPendienteRepuesto();
  };
}

function cerrarModalPendienteRepuesto() {
  const modal = document.getElementById("modalPendienteRepuesto");
  if (modal) modal.remove();
}

// --- INPUTS OCULTOS ---
function actualizarInputsOcultosRepuestos() {
  const contenedor = document.getElementById("inputsRepuestosOcultos");
  contenedor.innerHTML = "";
  let i = 0, hay = false;

  repuestosSeleccionados.forEach(r => {
    if (r.cantidad > 0) {
      contenedor.innerHTML += `
        <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
        <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cantidad}">
        <input type="hidden" name="repuestos[${i}][status_id]" value="25">
        <input type="hidden" name="repuestos[${i}][origen]" value="interno">`;
      i++;
      hay = true;
    }
    if (r.pendiente > 0) {
      contenedor.innerHTML += `
        <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
        <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.pendiente}">
        <input type="hidden" name="repuestos[${i}][status_id]" value="26">
        <input type="hidden" name="repuestos[${i}][origen]" value="interno">`;
      i++;
      hay = true;
    }
  });

  repuestosServicio.forEach(r => {
    contenedor.innerHTML += `
      <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
      <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cantidad}">
      <input type="hidden" name="repuestos[${i}][status_id]" value="26">
      <input type="hidden" name="repuestos[${i}][origen]" value="externo">`;
    i++;
    hay = true;
  });

  return hay;
}

// --- VALIDACIÓN FINAL ---
function validarRepuestosSeleccionados() {
  const hay = actualizarInputsOcultosRepuestos();
  if (!hay) {
    alert("Debes seleccionar al menos un repuesto.");
    return false;
  }
  return true;
}

function verRepuesto(id) {
  window.open(`ver_repuesto.php?id=${id}`, '_blank');
}
</script>

<div id="inputsRepuestosOcultos"></div>
<hr class="my-6">
<!-- Contenedor del buscador y tabla de herramientas -->
<div class="mb-6">
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
    const buscar = document.getElementById("buscarHerramienta").value || "";
    fetch(`buscar_herramientas_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorHerramientas").innerHTML = html;
        })
        .catch(error => {
            console.error("Error al cargar herramientas:", error);
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
                            : ''}
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
            </tr>
        `;
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
    } else {
        const excedente = nuevaCantidad - h.disponible;

        mostrarModalPendienteHerramienta(excedente, () => {
            h.cantidad = h.disponible;
            h.pendiente = excedente;
            renderizarTablaHerramientas();
        }, () => {
            renderizarTablaHerramientas(); // Cancelado
        });
        return;
    }

    if (nuevaCantidad > h.stockMaximo) {
        mostrarAlertaHerramienta("Estás superando el stock máximo recomendado.", "warning");
    }

    renderizarTablaHerramientas();
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
                <button type="button" id="btnAceptarPendienteHerramienta" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sí</button>
                <button type="button" id="btnCancelarPendienteHerramienta" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">No</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById("btnAceptarPendienteHerramienta").onclick = () => {
        aceptarCallback();
        cerrarModalPendienteHerramienta();
    };

    document.getElementById("btnCancelarPendienteHerramienta").onclick = () => {
        cancelarCallback();
        cerrarModalPendienteHerramienta();
    };
}

function cerrarModalPendienteHerramienta() {
    const modal = document.getElementById("modalPendienteHerramienta");
    if (modal) modal.remove();
}

function mostrarAlertaHerramienta(mensaje, tipo = "info") {
    const color = tipo === "warning"
        ? "bg-yellow-100 text-yellow-800"
        : "bg-blue-100 text-blue-800";

    const alerta = document.createElement("div");
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${color}`;
    alerta.innerText = mensaje;

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
    if (productosSeleccionados.length >= 2) {
        mostrarAlerta("Solo puedes agregar 2 productos.", "warning");
        return;
    }
    document.getElementById("dropdownProductos").classList.toggle("hidden");
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
        mostrarAlerta("Límite de 2 productos alcanzado.", "warning");
        return;
    }

    if (productosSeleccionados.some(p => p.id === id)) {
        mostrarAlerta("Este producto ya fue agregado.", "warning");
        return;
    }

    productosSeleccionados.push({
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMinimo,
        stockMaximo
    });

    renderizarTablaProductos();
    document.getElementById("dropdownProductos").classList.add("hidden");
}

function renderizarTablaProductos() {
    const tbody = document.getElementById("listaProductosAgregados");
    const contenedor = document.getElementById("tablaProductosAgregados");
    tbody.innerHTML = "";

    productosSeleccionados.forEach((p, i) => {
        const total = p.cantidad + p.pendiente;
        tbody.innerHTML += `
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
                        ${total > p.stockMaximo ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo (${p.stockMaximo})</span>` : ''}
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verProducto(${p.id})" class="text-blue-600 hover:text-blue-800" title="Ver"><i class="fas fa-eye"></i></button>
                    <button type="button" onclick="quitarProducto(${i})" class="text-red-600 hover:text-red-800" title="Quitar"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });

    contenedor.classList.toggle("hidden", productosSeleccionados.length === 0);
    actualizarInputsOcultosProductos();
}

function quitarProducto(index) {
    productosSeleccionados.splice(index, 1);
    renderizarTablaProductos();
}

function actualizarCantidadProducto(index, nuevaCantidad) {
    let cantidad = Math.floor(parseFloat(nuevaCantidad));
    if (isNaN(cantidad) || cantidad < 1) {
        mostrarAlerta("Cantidad no válida.", "warning");
        renderizarTablaProductos();
        return;
    }

    const p = productosSeleccionados[index];

    if (cantidad <= p.disponible) {
        p.cantidad = cantidad;
        p.pendiente = 0;
        renderizarTablaProductos();
    } else {
        const excedente = cantidad - p.disponible;
        mostrarModalPendiente(excedente, () => {
            p.cantidad = p.disponible;
            p.pendiente = excedente;
            renderizarTablaProductos();
        }, renderizarTablaProductos);
    }

    if (cantidad > p.stockMaximo) {
        mostrarAlerta("Estás superando el stock máximo recomendado.", "warning");
    }
}

function verProducto(id) {
    window.open(`ver_producto.php?id=${id}`, '_blank');
}

function mostrarModalPendiente(cantidadPendiente, aceptarCallback, cancelarCallback) {
    cerrarModalPendiente(); // Por si hay uno anterior
    const modal = document.createElement('div');
    modal.id = "modalPendiente";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" id="btnAceptarPendiente">Sí</button>
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" id="btnCancelarPendiente">No</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    document.getElementById("btnAceptarPendiente").onclick = () => {
        aceptarCallback();
        cerrarModalPendiente();
    };

    document.getElementById("btnCancelarPendiente").onclick = () => {
        cancelarCallback();
        cerrarModalPendiente();
    };
}

function cerrarModalPendiente() {
    const modal = document.getElementById("modalPendiente");
    if (modal) modal.remove();
}

function mostrarAlerta(mensaje, tipo = "info") {
    const colores = {
        warning: "bg-yellow-100 text-yellow-800",
        info: "bg-blue-100 text-blue-800"
    };
    const alerta = document.createElement('div');
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${colores[tipo] || colores.info}`;
    alerta.innerHTML = mensaje;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}

function actualizarInputsOcultosProductos() {
    const contenedorPlanificadas = document.getElementById("inputsProductosPlanificados");
    const contenedorPendientes = document.getElementById("inputsProductosPendientes");
    contenedorPlanificadas.innerHTML = "";
    contenedorPendientes.innerHTML = "";

    let planIndex = 0, pendIndex = 0;

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

function prepararEnvioProductos() {
    document.getElementById("productos_seleccionados").value = JSON.stringify(productosSeleccionados);
    actualizarInputsOcultosProductos();
    return true;
}
</script>



<!-- Toggle para notificaciones por email -->
<div class="flex items-center space-x-4 mt-3 mb-6">
    <i class="fas fa-envelope text-blue-600"></i>
    <label class="toggle-wrapper">
        <input type="checkbox" id="notificarEmail" class="toggle-input">
        <div class="toggle-track" id="toggleTrack">
            <div class="toggle-thumb" id="toggleThumb"></div>
        </div>
    </label>
    <span class="text-lg font-semibold text-gray-700">
        Notificar a los asignados de la tarea por email
    </span>
</div>

<!-- Estilos personalizados -->
<style>
.toggle-wrapper {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 24px;
}

.toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-track {
    background-color: #d1d5db; /* gray-300 */
    border-radius: 9999px;
    width: 100%;
    height: 100%;
    position: absolute; /* Changed from relative to absolute */
    top: 0; /* Added to align properly */
    left: 0; /* Added to align properly */
    transition: background-color 0.3s ease;
    cursor: pointer;
}

.toggle-thumb {
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Activo */
.toggle-track.active {
    background-color: #2563eb; /* blue-600 */
}

.toggle-track.active .toggle-thumb {
    transform: translateX(24px);
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleInput = document.getElementById('notificarEmail');
    const toggleTrack = document.getElementById('toggleTrack');

    toggleInput.addEventListener('change', () => {
        toggleTrack.classList.toggle('active', toggleInput.checked);
    });

    // Agregar funcionalidad para enviar el valor del toggle
    const form = document.querySelector('form');
    form.addEventListener('submit', (event) => {
        const notificarEmail = document.getElementById("notificarEmail").checked ? 1 : 0;
        const formData = new FormData(form);
        formData.append('notificar_email', notificarEmail);

        // Aquí puedes enviar el formData con fetch o procesarlo según sea necesario
        console.log('Notificar Email:', notificarEmail);
    });
});
</script>

    <!-- Nota sobre campos obligatorios -->
    <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>
    <input type="hidden" name="tarea_insumos" id="tarea_insumos">
    <!-- Botones -->
    <div class="flex justify-between mt-4 space-x-4">
        <!-- Botón Guardar -->
        <button type="submit" id="guardar"
                class="bg-green-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-green-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-300 disabled:bg-gray-400 disabled:text-gray-200 disabled:cursor-not-allowed"
                disabled>
            <i class="fas fa-save mr-2"></i> <!-- Ícono de Guardar -->
            Guardar Tipo
        </button>

        <!-- Botón Regresar -->
        <button type="button" onclick="location.href='tareas.php';"
                class="bg-blue-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-blue-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i class="fas fa-arrow-left mr-2"></i> <!-- Ícono de Regresar -->
            Regresar
        </button>
    </div>

    <script>
function prepararEnvioInsumos() {
    const tareaInsumos = {
        productos: window.productosSeleccionados || [],
        repuestos: window.repuestosSeleccionados || [],
        herramientas: window.herramientasSeleccionadas || []
    };

    document.getElementById('tarea_insumos').value = JSON.stringify(tareaInsumos);
    return true;
}
</script>

</form>
</div>







<script>
        const toggle = document.getElementById('toggleSede');
        const toggleCircle = document.getElementById('toggleCircle');
        const toggleBackground = document.getElementById('toggleBackground');

        toggle.addEventListener('change', () => {
            if (toggle.checked) {
                toggleCircle.style.transform = 'translateX(100%)';
                toggleBackground.classList.add('bg-green-500');
            } else {
                toggleCircle.style.transform = 'translateX(0)';
                toggleBackground.classList.remove('bg-green-500');
            }
        });

    </script>

    <!-- Script para alternar entre "Todo el día" y planificación con hora -->
<script>
 
document.addEventListener("DOMContentLoaded", function () {


    // Variables para el Toggle de "Todo el día"
    const toggleFullDay = document.getElementById("toggleFullDay");
    const fullDayWrapper = document.getElementById("fullDayWrapper");
    const fullDayKnob = document.getElementById("fullDayKnob");

 

    // Evento para alternar "Todo el día"
    toggleFullDay.addEventListener("change", () => {
        if (toggleFullDay.checked) {
            fullDayKnob.style.transform = "translateX(100%)";
            fullDayWrapper.classList.add("bg-green-500");
        } else {
            fullDayKnob.style.transform = "translateX(0)";
            fullDayWrapper.classList.remove("bg-green-500");
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


