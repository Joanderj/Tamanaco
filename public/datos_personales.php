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

$sql = "SELECT p.*, u.id_usuario,u.nombre_imagen,u.url
  FROM usuarios u 
  JOIN personas p ON u.id_persona = p.id_persona 
  WHERE u.usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();
$stmt->close();

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
    // Si el menú está inactivo o el perfil no tiene permisos, redirigir a dashboard.php
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


<div class="container mx-auto px-4 py-6">
<div class="container mx-auto max-w-4xl p-8 bg-white rounded-lg shadow-md">
  <!-- Título -->
  <div class="flex flex-col items-center mb-6">
    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
        <i class="fas fa-user text-3xl"></i>
    </div>
    <h2 class="text-3xl font-extrabold text-gray-800">Datos Personales</h2>
    <p class="text-gray-600 mt-2 text-center">Gestiona y actualiza tu información personal.</p>
</div>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Leer el mensaje de éxito desde la sesión
$mensaje_exito = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : "";

// Limpiar el mensaje de éxito después de mostrarlo
unset($_SESSION['mensaje']);
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
  <!-- Formulario -->
  <form action="editar_datos_personales.php" method="POST" enctype="multipart/form-data">
  <div class="text-center mb-6">
  <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center">
    <!-- Previsualización: Mostrar solo si hay imagen registrada -->
    <img id="imagen-preview" src="<?php echo htmlspecialchars($perfil['url']); ?>" alt="<?php echo htmlspecialchars($perfil['nombre_imagen']); ?>" 
         class="absolute inset-0 w-full h-full object-cover rounded-lg <?php echo empty($perfil['url']) ? 'hidden' : ''; ?>" />
    <!-- Placeholder: Ocultar si hay imagen registrada -->
    <div id="imagen-placeholder" class="text-center <?php echo !empty($perfil['url']) ? 'hidden' : ''; ?>">
        <i class="fas fa-building text-3xl text-blue-500"></i>
        <p class="text-blue-500 font-medium">Haga clic para subir una foto</p>
        <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
    </div>
</div>
<center>
<!-- Botón con icono de cámara -->
<label for="imagen" class="relative w-16 h-16 flex items-center justify-center bg-blue-500 hover:bg-blue-600 rounded-full cursor-pointer transition-colors shadow-md">
  <!-- Icono de cámara (Font Awesome) -->
  <i class="fas fa-camera text-white text-xl"></i>
  <!-- Input file oculto pero funcional -->
  <input 
    type="file" 
    id="imagen" 
    name="nombre_imagen" 
    accept="image/*" 
    class="absolute inset-0 opacity-0 cursor-pointer" 
    onchange="mostrarCropper()"
  />
</label>
</center> 

    <!-- Cédula y Nacionalidad -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="relative">
        <label for="cedula" class="block font-medium text-gray-600">Cédula:</label>
        <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($perfil['cedula']); ?>" placeholder="Ejemplo: 12345678" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
      </div>
      <div class="relative col-span-2">
        <label for="nacionalidad" class="block font-medium text-gray-600">Nacionalidad:</label>
        <select id="nacionalidad" name="nacionalidad" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled>
          <option value="v" <?php echo $perfil['nacionalidad'] === 'v' ? 'selected' : ''; ?>>Venezolana</option>
          <option value="e" <?php echo $perfil['nacionalidad'] === 'e' ? 'selected' : ''; ?>>Extranjera</option>
        </select>
      </div>
    </div>

    <!-- Nombre y Apellidos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="relative">
        <label for="nombre1" class="block font-medium text-gray-600">Primer Nombre:</label>
        <input type="text" id="nombre1" name="nombre1" value="<?php echo htmlspecialchars($perfil['primer_nombre']); ?>" placeholder="Primer Nombre" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500" onclick="habilitarInput('nombre1')">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
      <div class="relative">
        <label for="nombre2" class="block font-medium text-gray-600">Segundo Nombre:</label>
        <input type="text" id="nombre2" name="nombre2" value="<?php echo htmlspecialchars($perfil['segundo_nombre']); ?>" placeholder="Segundo Nombre" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500" onclick="habilitarInput('nombre2')">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
      <div class="relative">
        <label for="apellido1" class="block font-medium text-gray-600">Primer Apellido:</label>
        <input type="text" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($perfil['primer_apellido']); ?>" placeholder="Primer Apellido" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500" onclick="habilitarInput('apellido1')">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
      <div class="relative">
        <label for="apellido2" class="block font-medium text-gray-600">Segundo Apellido:</label>
        <input type="text" id="apellido2" name="apellido2" value="<?php echo htmlspecialchars($perfil['segundo_apellido']); ?>" placeholder="Segundo Apellido" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
        <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500" onclick="habilitarInput('apellido2')">
          <i class="fas fa-pencil-alt"></i>
        </button>
      </div>
    </div>

    <!-- Fecha de Nacimiento y Edad -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
  <div>
    <label for="nacimiento" class="block font-semibold">Fecha de Nacimiento:</label>
    <input type="date" id="nacimiento" name="nacimiento" value="<?php echo htmlspecialchars($perfil['fecha_nacimiento']); ?>" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
  </div>
  <div>
    <label for="edad" class="block font-semibold">Edad:</label>
    <input type="number" id="edad" name="edad" value="<?php echo htmlspecialchars($perfil['edad']); ?>" placeholder="Edad" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled />
    <small id="error-edad" class="text-red-500"></small>
  </div>
</div>

    <!-- Género -->
    <div class="relative mb-6">
      <label for="genero" class="block font-medium text-gray-600">Género:</label>
      <select id="genero" name="genero" class="w-full p-3 border border-gray-300 rounded-lg bg-gray-100 focus:outline-none" disabled>
        <option value="Masculino" <?php echo $perfil['genero'] === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
        <option value="Femenino" <?php echo $perfil['genero'] === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
      </select>
      <button type="button" class="absolute top-2 right-2 text-gray-500 hover:text-blue-500" onclick="habilitarInput('genero')">
        <i class="fas fa-pencil-alt"></i>
      </button>
    </div>
    <input type="hidden" name="id_persona" value="<?php echo isset($perfil['id_persona']) ? htmlspecialchars($perfil['id_persona']) : ''; ?>" />
    <input type="hidden" name="id_usuario" value="<?php echo isset($perfil['id_usuario']) ? htmlspecialchars($perfil['id_usuario']) : ''; ?>" />
        <input type="hidden" name="cedula" value="<?php echo isset($perfil['cedula']) ? htmlspecialchars($perfil['cedula']) : ''; ?>" />

    <!-- Botón Guardar -->
    <div class="flex justify-center mt-6">
      <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 transition duration-300">
        Guardar Cambios
      </button>
    </div>
  </form>

  <script>
    function habilitarInput(id) {
      const input = document.getElementById(id);
      input.disabled = false;
      input.classList.remove('bg-gray-100');
      input.classList.add('bg-white');
      input.focus();
    }
  </script>
</div>

</div>

     

  
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
  document.getElementById("nacimiento").addEventListener("change", function () {
    const fechaNacimiento = new Date(this.value);
    const hoy = new Date();
    const edadInput = document.getElementById("edad");
    const errorFecha = document.getElementById("error-fecha");

    // Calcular la edad
    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
    const mes = hoy.getMonth() - fechaNacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
      edad--;
    }

    // Validar la edad mínima de 18 años
    if (edad >= 18) {
      edadInput.value = edad;
      edadInput.disabled = false;
      edadInput.classList.remove("bg-gray-200", "cursor-not-allowed");
      edadInput.classList.add("bg-white", "cursor-text");
      errorFecha.textContent = ""; // Limpiar mensaje bajo el campo
    } else {
      edadInput.value = ""; // Limpiar la edad
      edadInput.disabled = true; // Bloquear el campo
      edadInput.classList.add("bg-gray-200", "cursor-not-allowed");
      edadInput.classList.remove("bg-white", "cursor-text");
      errorFecha.textContent = "Debe tener al menos 18 años para continuar."; // Mostrar mensaje bajo el campo
    }
  });
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



