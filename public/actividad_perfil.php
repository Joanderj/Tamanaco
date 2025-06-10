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
$usuario = $_SESSION['username'];

// Consulta para obtener el perfil del usuario
$sql = "SELECT * FROM usuarios u JOIN personas p ON u.id_persona = p.id_persona WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();


// Menú actual (empresa.php -> id_menu = 9)
$menu_actual = 8;

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

if ($permiso_menu['permiso'] == 0) {
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
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 8
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

// Cerrar conexión
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
        <img src="perfil.jpg" alt="Foto de Perfil" class="w-16 h-16 rounded-full border-4 border-gray-300 shadow-lg">
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
          <i class="fa fa-home mr-2"></i> Inicio:
          <!-- Botón de cierre como icono en la esquina superior derecha -->
          <button class="text-white text-xl ml-auto cursor-pointer hover:text-red-300" onclick="toggleSidebar()">
            <i class="fa fa-times"></i>
          </button>
        </h2>
      
        <!-- Submenús con iconos y diseños hover -->
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-user mr-2"></i> Empleado
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-box mr-2"></i> Inventario
        </a>
        <hr class="border-gray-300 my-2">

        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-tools mr-2"></i> Mantenimiento
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-chart-bar mr-2"></i> Reportes
        </a>
      </nav>
</div>
<hr>

<div class="p-6 bg-gray-50 rounded ">
<div class="flex flex-wrap justify-around space-y-4 md:space-y-0 md:space-x-6">
    <?php foreach ($submenus_tipo_1 as $submenu): ?>
      <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
        <div class="flex flex-col items-center">
          <!-- Botón dinámico -->
          <button class="
            <?php 
              echo $submenu['nombre_submenu'] === 'Datos Personales' ? 'bg-red-500 hover:bg-red-600' : 
                   ($submenu['nombre_submenu'] === 'Dirección de Habitación' ? 'bg-blue-500 hover:bg-blue-600' : 
                   ($submenu['nombre_submenu'] === 'Seguridad' ? 'bg-yellow-500 hover:bg-yellow-600' : 
                   ($submenu['nombre_submenu'] === 'Correo' ? 'bg-purple-500 hover:bg-purple-600' : 
                   ($submenu['nombre_submenu'] === 'Teléfono' ? 'bg-green-500 hover:bg-green-600' : 
                   ($submenu['nombre_submenu'] === 'Actividad' ? 'bg-indigo-500 hover:bg-indigo-600' : 
                   'bg-gray-500 hover:bg-gray-600')))));
            ?> 
            text-white w-16 h-16 rounded-full flex items-center justify-center">
            <i class="
              <?php 
                echo $submenu['nombre_submenu'] === 'Datos Personales' ? 'fas fa-user' : 
                     ($submenu['nombre_submenu'] === 'Dirección de Habitación' ? 'fas fa-home' : 
                     ($submenu['nombre_submenu'] === 'Seguridad' ? 'fas fa-lock' : 
                     ($submenu['nombre_submenu'] === 'Correo' ? 'fas fa-envelope' : 
                     ($submenu['nombre_submenu'] === 'Teléfono' ? 'fas fa-phone' : 
                     ($submenu['nombre_submenu'] === 'Actividad' ? 'fas fa-chart-line' : 
                     'fas fa-tasks')))));
              ?>
              text-xl"></i>
          </button>
          <!-- Etiqueta del botón -->
          <span class="text-gray-700 text-sm mt-2"><?php echo htmlspecialchars($submenu['nombre_submenu']); ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interfaz de Usuario</title>
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/flatpickr.min.css">
    <link rel="stylesheet" href="../public/css/all.min.css">
    <link rel="stylesheet" href="../public/css/main.min.css">
    <script src="../public/js/chart.js"></script>
    <style>
        /* Tu CSS aquí */
    </style>
</head>
<body>
    
<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Iniciar sesión si aún no se ha hecho
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegúrate de tener cargado el ID del usuario activo en la sesión o en $perfil
$id_usuario = isset($perfil['id_usuario']) ? $perfil['id_usuario'] : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null);

// Verifica si hay un usuario válido
if (!$id_usuario) {
    die("Usuario no autenticado.");
}

// Inicializar variables de fecha y paginación
$fechaDesde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : '';
$fechaHasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : '';
$limit = 5; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Construir consulta SQL dinámicamente
$sql = "SELECT * FROM registro_actividades WHERE id_usuario = ?";
$params = [$id_usuario];
$types = "i";

if ($fechaDesde && $fechaHasta) {
    $sql .= " AND fecha BETWEEN ? AND ?";
    $params[] = $fechaDesde;
    $params[] = $fechaHasta;
    $types .= "ss";
}

// Agregar orden y paginación
$sql .= " ORDER BY fecha DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Preparar y ejecutar consulta
$stmt = $conexion->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Almacenar registros en un array
$registro_actividades = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $registro_actividades[] = $row;
    }
} else {
    echo "No se encontraron registros.";
}

// Consulta para el total de registros (paginación)
$total_sql = "SELECT COUNT(*) as total FROM registro_actividades WHERE id_usuario = ?";
$types_total = "i";
$params_total = [$id_usuario];

if ($fechaDesde && $fechaHasta) {
    $total_sql .= " AND fecha BETWEEN ? AND ?";
    $types_total .= "ss";
    $params_total[] = $fechaDesde;
    $params_total[] = $fechaHasta;
}

$stmt_total = $conexion->prepare($total_sql);
$stmt_total->bind_param($types_total, ...$params_total);
$stmt_total->execute();
$total_result = $stmt_total->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Cerrar statements y conexión
$stmt->close();
$stmt_total->close();
$conexion->close();
?>

<!-- FILTRO DE FECHAS -->
<form method="POST" action=""
      class="flex flex-col md:flex-row items-center justify-between gap-6 bg-white/60 backdrop-blur-md shadow-2xl p-6 rounded-2xl w-full max-w-5xl mx-auto mt-12 border border-gray-200 transition-all">
    
    <!-- Desde -->
    <div class="flex flex-col w-full md:w-1/3">
        <label for="fecha_desde" class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 2a1 1 0 0 1 1 1v1h10V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v2H3V6a2 2 0 0 1 2-2h1V3a1 1 0 0 1 1-1Zm15 7H3v11a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9ZM8 13a1 1 0 1 1 0-2h2a1 1 0 1 1 0 2H8Z"/>
            </svg>
            Desde:
        </label>
        <input type="date" id="fecha_desde" name="fecha_desde" required
               class="border border-gray-300 rounded-xl px-4 py-2 text-sm text-gray-800 shadow-inner focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all">
    </div>

    <!-- Hasta -->
    <div class="flex flex-col w-full md:w-1/3">
        <label for="fecha_hasta" class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 5a2 2 0 0 1 2-2h1V2a1 1 0 1 1 2 0v1h6V2a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H3V5Zm0 5v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-9H3Zm5 3a1 1 0 0 1 1-1h2a1 1 0 1 1 0 2H9a1 1 0 0 1-1-1Z"/>
            </svg>
            Hasta:
        </label>
        <input type="date" id="fecha_hasta" name="fecha_hasta" required
               class="border border-gray-300 rounded-xl px-4 py-2 text-sm text-gray-800 shadow-inner focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all">
    </div>

    <!-- Botón Buscar -->
    <div class="pt-4 md:pt-6 w-full md:w-auto">
        <button type="submit"
                class="bg-gradient-to-r from-blue-600 to-indigo-500 text-white px-6 py-2 rounded-xl font-semibold text-sm shadow-md hover:from-blue-700 hover:to-indigo-600 hover:scale-105 transition-all duration-300">
             Buscar
        </button>
    </div>
</form>

<!-- BOTÓN BLOQUEAR -->
<div class="flex justify-end max-w-5xl mx-auto mt-8">
    <button id="blockButton"
            class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl font-semibold shadow-lg hover:scale-105 transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24">
            <path d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728"/>
        </svg>
        Bloquear Usuario
    </button>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div id="confirmDialog" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 transition-all duration-300">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full scale-95 opacity-0 transform transition-all duration-300 animate-fadeIn">
        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">
            ¿Bloquear al usuario y cerrar sesión?
        </h3>
        <p class="text-sm text-gray-600 text-center mb-6">Esta acción no se puede deshacer.</p>
        <div class="flex justify-center gap-4">
            <button id="confirmYes"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl font-semibold shadow-md transition-all">
                 Sí
            </button>
            <button id="confirmNo"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-xl font-semibold shadow-md transition-all">
                 No
            </button>
        </div>
    </div>
</div>

<!-- ANIMACIÓN (solo si usas Tailwind Plugin de Animation) -->
<style>
@keyframes fadeIn {
  0% {
    opacity: 0;
    transform: scale(0.95);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.3s ease-out forwards;
}
</style>

<!-- REGISTRO DE ACTIVIDADES -->
<h2 class="text-3xl font-extrabold text-gray-800 mt-12 text-center">
    <span class="inline-flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 1.75a.75.75 0 0 1 .75.75v.568a9.003 9.003 0 0 1 7.182 7.182H20a.75.75 0 0 1 0 1.5h-.568a9.003 9.003 0 0 1-7.182 7.182v.568a.75.75 0 0 1-1.5 0v-.568a9.003 9.003 0 0 1-7.182-7.182H4a.75.75 0 0 1 0-1.5h.568A9.003 9.003 0 0 1 11.25 3.068V2.5a.75.75 0 0 1 .75-.75ZM12 5a7 7 0 1 0 0 14a7 7 0 0 0 0-14Z"/>
        </svg>
        Registro de Actividades
    </span>
</h2>

<div class="space-y-6 mt-8 max-w-5xl mx-auto px-4">
    <?php foreach ($registro_actividades as $actividad): ?>
        <div class="bg-white/60 backdrop-blur-md border border-gray-200 rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2 6a2 2 0 0 1 2-2h3.2a2 2 0 0 1 1.6.8l1.6 2.4H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6Z"/>
                        </svg>
                        <?= htmlspecialchars($actividad['actividad']) ?>
                    </h3>
                    <p class="text-sm text-gray-600 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10Zm-.75-9.25V7a.75.75 0 0 1 1.5 0v5.25H17a.75.75 0 0 1 0 1.5h-5.75a.75.75 0 0 1-.75-.75Z"/>
                        </svg>
                        <span><?= htmlspecialchars($actividad['fecha']) ?></span>
                    </p>
                    <p class="text-sm text-gray-600 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4Zm8 12a2 2 0 1 1 0-4a2 2 0 0 1 0 4Zm0-6a2 2 0 1 1 0-4a2 2 0 0 1 0 4Z"/>
                        </svg>
                        <span><?= htmlspecialchars($actividad['dispositivo']) ?></span>
                    </p>
                </div>

                <div class="px-3 py-1 rounded-full text-sm font-semibold shadow-inner 
                    <?php
                        $accion = strtolower($actividad['accion']);
                        $bg = 'bg-blue-100 text-blue-800';
                        if (str_contains($accion, 'elimin')) $bg = 'bg-red-100 text-red-700';
                        elseif (str_contains($accion, 'modif')) $bg = 'bg-yellow-100 text-yellow-800';
                        elseif (str_contains($accion, 'crea')) $bg = 'bg-green-100 text-green-800';
                        echo $bg;
                    ?>">
                    <?= htmlspecialchars($actividad['accion']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<!-- Paginación mejorada -->
<div class="flex justify-center mt-4">
    <!-- Enlace a la primera página -->
    <?php if ($page > 1): ?>
        <a href="?page=1" class="px-4 py-2 border bg-white text-blue-500 border-blue-500 rounded-lg">Primera</a>
    <?php endif; ?>

    <!-- Enlace a la página anterior -->
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 border bg-white text-blue-500 border-blue-500 rounded-lg">Anterior</a>
    <?php endif; ?>

    <!-- Rango de páginas -->
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);
    for ($i = $start_page; $i <= $end_page; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 border <?php echo ($i === $page) ? 'bg-blue-500 text-white' : 'bg-white text-blue-500'; ?> border-blue-500 rounded-lg">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <!-- Enlace a la página siguiente -->
    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 border bg-white text-blue-500 border-blue-500 rounded-lg">Siguiente</a>
    <?php endif; ?>

    <!-- Enlace a la última página -->
    <?php if ($page < $total_pages): ?>
        <a href="?page=<?php echo $total_pages; ?>" class="px-4 py-2 border bg-white text-blue-500 border-blue-500 rounded-lg">Última</a>
    <?php endif; ?>
</div>

        
</body>
</html>


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
    document.getElementById('blockButton').onclick = function() {
        document.getElementById('confirmDialog').classList.remove('hidden');
    };

    document.getElementById('confirmNo').onclick = function() {
        document.getElementById('confirmDialog').classList.add('hidden');
    };

    document.getElementById('confirmYes').onclick = function() {
        // Redirigir a la página que maneja el bloqueo y cierre de sesión
        window.location.href = 'bloquear_usuario.php'; // Cambia esto a tu archivo real
    };
</script>


</body>
</html>