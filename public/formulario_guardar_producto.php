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
<!-- Contenido principal -->
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-center text-blue-600 mb-4">
            <i class="fas fa-box"></i> Formulario de Producto
        </h1>
 <?php
// Verificar si la sesión aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Leer el mensaje de error desde la sesión
$error_message = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : "";

// Limpiar el mensaje de error después de mostrarlo
unset($_SESSION['mensaje_error']);
?>

<!-- Mostrar mensaje de error si existe -->
<?php if (!empty($error_message)): ?>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 p-4 rounded-full shadow-lg animate-pulse">
                    <i class="fas fa-exclamation-triangle text-red-500 text-4xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-bold text-red-600 mb-2">¡Error!</h2>
                <p class="text-gray-700"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" 
                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>
        <!-- Formulario -->
        <form action="guardar_producto.php" method="POST" enctype="multipart/form-data" class="space-y-4">
    <!-- Imagen del Producto -->
<div class="text-center mb-6">
  <!-- Etiqueta -->
<label for="imagen" class="block text-lg font-semibold mb-2">Imagen del Producto</label>

<!-- Contenedor -->
<div onclick="document.getElementById('imagen').click()" 
     class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center cursor-pointer group overflow-hidden">

    <!-- Input oculto -->
    <input type="file" id="imagen" name="nombre_imagen" accept="image/*"
           class="hidden" onchange="mostrarPrevisualizacion(this)" />

    <!-- Previsualización -->
    <img id="imagen-preview" src="" alt="Previsualización"
         class="w-full h-full object-cover hidden rounded-lg" />

    <!-- Placeholder -->
    <div id="imagen-placeholder" class="text-center text-blue-500 group-hover:opacity-70">
        <i class="fas fa-box text-4xl mb-2"></i>
        <p class="font-medium">Haga clic para subir una imagen</p>
        <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
    </div>
</div>

<script>
function mostrarPrevisualizacion(input) {
    const file = input.files[0];
    const preview = document.getElementById('imagen-preview');
    const placeholder = document.getElementById('imagen-placeholder');

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        preview.src = '';
        placeholder.classList.remove('hidden');
    }
}
</script>
</div>

    <!-- Nombre del Producto -->
    <div>
        <label for="nombre_producto" class="block font-semibold flex items-center">
            <i class="fas fa-tag text-blue-500 mr-2"></i> <!-- Ícono de marca -->
            Nombre del Producto: <span class="text-red-600">*</span>
        </label>
        <input type="text" id="nombre_producto" name="nombre_producto" placeholder="Ingrese el nombre del producto" class="w-full border border-gray-300 rounded-lg p-2" required>
        <!-- Mensajes específicos para el input -->
        <small id="mensaje-error-input-producto" class="text-red-500 hidden">Este producto ya existe</small>
        <small id="mensaje-exito-input-producto" class="text-green-500 hidden">¡Este producto está disponible!</small>
        <small class="text-gray-500">Ejemplo: Taladro Eléctrico</small>
    </div>
   <!-- Selección de Marca -->
<!-- Selección de Marca -->
<div>
  <label for="marca" class="block font-semibold flex items-center">
    <i class="fas fa-industry text-blue-500 mr-2"></i> Marca: <span class="text-red-600">*</span>
  </label>
  <div class="flex items-center gap-4">
    <select id="marca" name="marca" onchange="cargarModelos()" class="w-full border border-gray-300 rounded-lg p-2" required>
      <option value="" disabled selected>Seleccione una marca</option>
      <!-- Aquí se cargan dinámicamente las marcas -->
    </select>
    <button type="button" onclick="abrirVentanaEmergenteMarca()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
      <i class="fas fa-plus"></i>
    </button>
  </div>
</div>


<!-- Selección de Modelo -->
<div>
  <label for="modelo" class="block font-semibold flex items-center">
    <i class="fas fa-cube text-blue-500 mr-2"></i> Modelo: <span class="text-red-600">*</span>
  </label>
  <div class="flex items-center gap-4">
    <select id="modelo" name="modelo" class="w-full border border-gray-300 rounded-lg p-2" required>
      <option value="" disabled selected>Seleccione un modelo</option>
    </select>
    <button type="button" onclick="abrirVentanaEmergenteModelo()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
      <i class="fas fa-plus"></i>
    </button>
  </div>
</div>

    <!-- Selección de Tipo -->
    <div>
        <label for="tipo" class="block font-semibold flex items-center">
            <i class="fas fa-cube text-blue-500 mr-2"></i> <!-- Ícono de tipo -->
            Tipo: <span class="text-red-600">*</span>
        </label>
        <div class="flex items-center gap-4">
          <select id="tipo" name="tipo" class="w-full border border-gray-300 rounded-lg p-2" onchange="actualizarClasificaciones()" required>
    <option value="" disabled selected>Seleccione un tipo</option>
    <!-- Opciones se insertarán con JavaScript -->
</select>

            <button type="button" onclick="abrirVentanaEmergenteTipo()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
<div>
<!-- Selector de Clasificación -->
<label for="clasificacion" class="block font-semibold flex items-center mb-2">
    <i class="fas fa-layer-group text-blue-500 mr-2"></i> <!-- Ícono de clasificación -->
    Cantidad y Clasificación: <span class="text-red-600">*</span>
</label>
<div class="flex items-center w-full border border-gray-300 rounded-lg overflow-hidden shadow-md bg-white p-3">
    <!-- Selector de clasificación con estilo elegante -->
    <div class="relative">
        <select id="clasificacion" name="clasificacion"
            class="bg-gray-100 text-gray-700 text-sm border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 hover:bg-gray-200 transition-all"
            onchange="actualizarAbreviatura()" required>
            <option value="" data-abreviatura="">Seleccionar</option>
        </select>
    </div>
   

    <!-- Input para cantidad con diseño refinado -->
    <div class="relative flex-1 ml-4">
        <input type="text" id="unidad_medida" name="unidad_medida"
            placeholder="Ingrese cantidad"
            class="w-full p-3 pl-4 text-gray-800 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 shadow-sm bg-white"
            required>
        <span id="abreviatura"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 font-medium pointer-events-none">kg</span>
    </div>
    <hr class="border-gray-300 mx-4 h-6">
    <button type="button" onclick="abrirVentanaEmergenteClasificacion()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                <i class="fas fa-plus"></i>
            </button>
</div>

</div>

    <!-- Vincular Proveedor -->
    <div>
        <label for="vincularProveedor" class="block font-semibold mb-2">¿Desea vincular este producto a un proveedor?</label>
         <div class="flex items-center gap-4">
        <select id="vincularProveedor" name="vincularProveedor" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="mostrarOpcionesProveedor()" >
            <option value="">Seleccionar</option>
            <option value="uno">Un proveedor</option>
            <option value="muchos">Muchos proveedores</option>
        </select>

    <button type="button" onclick="abrirVentanaEmergenteProveedor()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
      <i class="fas fa-plus"></i>
    </button>
</div>
    </div>
    <!-- Opciones de Proveedor -->
    <div id="opcionesProveedor" class="mt-4 hidden">
        <!-- Selección para un proveedor -->
        <div id="selectProveedor" class="hidden border border-blue-500 rounded-lg p-4 bg-blue-50 shadow-md transition-all">
        
    <label for="proveedorUnico" class="block text-sm font-medium text-gray-700 mb-1">Seleccione el proveedor:</label>
    <select id="proveedorUnico" name="proveedorUnico"
        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
        <option value="">Seleccionar proveedor</option>
    </select>


        </div>

        <!-- Selección para múltiples proveedores -->
        <div id="tablaProveedores" class="hidden border border-green-500 rounded-lg p-4 bg-green-50 shadow-md transition-all">
            <label class="block font-semibold mb-2">Seleccione los proveedores:</label>
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2">Seleccionar</th>
                        <th class="border border-gray-300 px-4 py-2">Nombre del proveedor</th>
                    </tr>
                </thead>
              <tbody id="tbodyProveedores">
    <!-- Aquí se cargarán dinámicamente -->
</tbody>

            </table>
        </div>
    </div>


    <!-- Nota sobre campos obligatorios -->
    <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>

    <!-- Botones -->
    <div class="flex justify-between mt-4">
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Guardar</button>
        <button type="button" class="bg-gray-300 text-black py-2 px-4 rounded-lg hover:bg-gray-400" onclick="window.location.href='producto.php'">Regresar</button>
    </div>
</form>

    </div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<!-- Modal: Registrar Marca -->
<div id="modalMarca" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl p-6 relative overflow-y-auto max-h-[90vh]">
    
    <!-- Botón cerrar (X) -->
    <button onclick="cerrarVentanaEmergenteMarca()" class="absolute top-3 right-4 text-gray-500 hover:text-red-500 text-2xl">
      &times;
    </button>

    <!-- Título del modal -->
    <h2 class="text-xl font-bold mb-4 text-blue-600 flex items-center">
      <i class="fas fa-tag mr-2"></i> Registrar nueva Marca
    </h2>

    <!-- Tu formulario de marca (sin cambios) -->
    <form action="guardar_marca_producto.php" method="POST" class="space-y-4" id="form-marca">
      <!-- Nombre de la Marca -->
      <div>
        <label for="nombre_marca" class="block font-semibold flex items-center">
          <i class="fas fa-tag text-blue-500 mr-2"></i> Nombre de la Marca: <span class="text-red-600">*</span>
        </label> 
        <input type="text" id="nombre_marca" name="nombre_marca"
          placeholder="Ingrese el nombre de la marca"
          class="w-full border border-gray-300 rounded-lg p-2" required
          oninput="convertirMayusculas(this); validarNombreMarca()">
        <small id="mensaje-error-input-marca" class="text-red-500 hidden">Esta marca ya existe</small>
        <small id="mensaje-exito-input-marca" class="text-green-500 hidden">¡Esta marca está disponible!</small>
        <small class="text-gray-500">Ejemplo: TAMANACO</small>
      </div>

      <!-- Vincular Modelos -->
      <div>
        <label for="vincular_opcion" class="block font-semibold flex items-center">
          <i class="fas fa-link text-blue-500 mr-2"></i> Vincular Modelos:<span class="text-red-600">*</span>
        </label>
        <div class="flex space-x-4">
          <select id="vincular_opcion" name="vincular_opcion"
            class="w-full border border-gray-300 rounded-lg p-2" required
            onchange="toggleModeloSelection()">
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="uno">Vincular un modelo</option>
            <option value="varios">Vincular varios modelos</option>
          </select>
          
        </div>
      </div>

      <!-- Vincular un Modelo -->
      <div id="vincular_uno" class="hidden">
        <label for="modelo_uno" class="block font-semibold flex items-center">
          <i class="fas fa-cube text-blue-500 mr-2"></i> Seleccionar un modelo <span class="text-red-600">*</span>
        </label>
        <select name="modelo_uno" id="modelo_uno" class="w-full border border-gray-300 rounded-lg p-2">
          <?php
          $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
          $resultado = $conexion->query("SELECT id_modelo, nombre_modelo FROM modelo");
          while ($fila = $resultado->fetch_assoc()) {
              echo "<option value='{$fila['id_modelo']}'>{$fila['nombre_modelo']}</option>";
          }
          $conexion->close();
          ?>
        </select>
      </div>

      <!-- Vincular varios modelos -->
      <div id="vincular_varios" class="hidden">
        <label class="block font-semibold">
          <i class="fas fa-cube text-blue-500 mr-2"></i> Selecciona los modelos <span class="text-red-600">*</span>
        </label>
        <!-- Buscador para modelos -->
<div class="mb-4">
    <input type="text" id="buscar_modelo" placeholder="Buscar modelo..."
        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
</div>

<!-- Tabla de modelos -->
<table class="table-auto w-full border border-gray-300 rounded-lg shadow-md">
    <thead class="bg-blue-100 text-blue-600">
        <tr>
            <th class="px-4 py-2 text-left font-bold">Seleccionar</th>
            <th class="px-4 py-2 text-left font-bold">Nombre del Modelo</th>
        </tr>
    </thead> 
    <tbody id="tbody_modelos">
        <?php
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        $resultado = $conexion->query("SELECT id_modelo, nombre_modelo FROM modelo");
        while ($fila = $resultado->fetch_assoc()) {
            echo "
            <tr class='hover:bg-blue-50' onclick='toggleSelection(this)'>
                <td class='px-4 py-2'>
                    <input type='checkbox' name='modelos[]' value='{$fila['id_modelo']}' onchange='highlightRow(this)'>
                </td>
                <td class='px-4 py-2 text-gray-700'>{$fila['nombre_modelo']}</td>
            </tr>";
        }
        $conexion->close();
        ?>
    </tbody>
</table>

<!-- Controles de paginación -->
<div class="flex justify-between items-center mt-4">
    <button type="button" onclick="cambiarPaginaModeloLista(-1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Anterior</button>
    <span id="pagina_actual_modelo_lista" class="text-gray-700 font-semibold"></span>
    <button type="button" onclick="cambiarPaginaModeloLista(1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Siguiente</button>
</div>

      </div>


      <!-- Nota -->
      <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>

      <!-- Botones -->
      <div class="flex justify-between mt-4 space-x-4">
        <button type="button" id="guardar" onclick="guardarMarca()"
  class="bg-green-500 text-white py-2 px-6 rounded-lg ...">
  <i class="fas fa-save mr-2"></i> Guardar
</button>

        <button type="button" onclick="cerrarVentanaEmergenteMarca()"
          class="bg-gray-400 text-white py-2 px-6 rounded-lg flex items-center hover:bg-gray-500 transition-all duration-300">
          <i class="fas fa-times mr-2"></i> Cancelar
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Structure -->
<div id="ventanaEmergenteModelo" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center max-h-[90vh] overflow-y-auto">
    <div class="bg-white max-w-4xl mx-auto rounded-lg shadow-lg p-6">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-cube text-3xl"></i> <!-- Ícono de modelos -->
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Modelo</h2>
            <p class="text-gray-600 mt-2 text-center">Registra un modelo y vincúlalo a una marca de forma rápida y organizada.</p>
        </div>
           <!-- Contenedor global para mensajes de error -->
    <div id="mensaje-global-modelo" class="hidden bg-red-100 text-red-700 p-4 rounded-lg mb-4">
        <strong id="tipo-mensaje-global-modelo"></strong> <span id="texto-mensaje-global-modelo"></span>
    </div>

        <form id="formRegistrarModelo" method="POST" action="guardar_modelo_producto.php">
            <div class="mb-4">
                <label for="nombre_modelo" class="block text-gray-700 font-bold mb-2">Nombre del Modelo <span class="text-red-600">*</span></label>
                <input type="text" id="nombre_modelo" name="nombre_modelo" placeholder="Ingrese el nombre del modelo"
                       class="border border-gray-300 rounded-lg p-2 w-full" required oninput="convertirMayusculas(this); validarNombreModelo()">
                       <small id="mensaje-error-input-modelo" class="text-red-500 hidden">Este modelo ya existe</small>
                       <small id="mensaje-exito-input-modelo" class="text-green-500 hidden">¡Este modelo está disponible!</small>
                <small class="text-gray-500">Ejemplo: SEDAN, SUV, CAMIONETA</small>
            </div>


<!-- Vincular Marcas a Modelos -->
<div>
    <label for="modelo_vincular_opcion" class="block font-semibold flex items-center">
        <i class="fas fa-link text-blue-500 mr-2"></i>
        Vincular Marcas al Modelo:<span class="text-red-600">*</span>
    </label>
    <div class="flex space-x-4">
        <select id="modelo_vincular_opcion" name="modelo_vincular_opcion"
                class="w-full border border-gray-300 rounded-lg p-2" required
                onchange="toggleMarcaModeloSelection()">
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="una">Vincular una marca</option>
            <option value="varias">Vincular varias marcas</option>
        </select>
    </div>
</div>

<!-- Vincular una Marca -->
<div id="modelo_vincular_una" class="hidden">
    <label for="modelo_marca_una" class="block font-semibold flex items-center">
        <i class="fas fa-tags text-blue-500 mr-2"></i>
        Seleccionar una marca <span class="text-red-600">*</span>
    </label>
    <select name="modelo_marca_una" id="modelo_marca_una" class="w-full border border-gray-300 rounded-lg p-2">
        <?php
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        $resultado = $conexion->query("SELECT id_marca, nombre_marca FROM marca");
        while ($fila = $resultado->fetch_assoc()) {
            echo "<option value='{$fila['id_marca']}'>{$fila['nombre_marca']}</option>";
        }
        $conexion->close();
        ?>
    </select>
</div>

<!-- Vincular Varias Marcas -->
<div id="modelo_vincular_varias" class="hidden">
    <label class="block font-semibold">
        <i class="fas fa-tags text-blue-500 mr-2"></i> Selecciona las marcas <span class="text-red-600">*</span>
    </label>
  <!-- Buscador -->
<div class="mb-4">
    <input type="text" id="buscar_modelo_marca" placeholder="Buscar marca..."
        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
</div>

<!-- Tabla con paginación -->
<table class="table-auto w-full border border-gray-300 rounded-lg shadow-md">
    <thead class="bg-blue-100 text-blue-600">
        <tr>
            <th class="px-4 py-2 text-left font-bold">Seleccionar</th>
            <th class="px-4 py-2 text-left font-bold">Nombre de la Marca</th>
        </tr>
    </thead>
    <tbody id="tbody_modelo_marca">
        <?php
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        $resultado = $conexion->query("SELECT id_marca, nombre_marca FROM marca");
        while ($fila = $resultado->fetch_assoc()) {
            echo "
            <tr class='hover:bg-blue-50' onclick='toggleModeloSelectiones(this)'>
                <td class='px-4 py-2'>
                    <input type='checkbox' name='modelo_marcas[]' value='{$fila['id_marca']}' onchange='highlightModeloRow(this)'>
                </td>
                <td class='px-4 py-2 text-gray-700'>{$fila['nombre_marca']}</td>
            </tr>";
        }
        $conexion->close();
        ?>
    </tbody>
</table>

<!-- Controles de paginación -->
<div class="flex justify-between items-center mt-4">
    <button type="button" onclick="cambiarPaginaModelo(-1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Anterior</button>
    <span id="pagina_actual_modelo" class="text-gray-700 font-semibold"></span>
    <button type="button" onclick="cambiarPaginaModelo(1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Siguiente</button>
</div>


<small id="mensaje_limite_modelo" class="text-red-600 hidden mt-1">Solo puedes seleccionar hasta 5 marcas.</small>

</div>

            <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>
            <div class="flex justify-between mt-4 space-x-4">
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fas fa-save mr-2"></i> <!-- Ícono de Guardar -->
                    Guardar
                </button>
                <button type="button" onclick="cerrarVentanaEmergenteModelo()"
                        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-all duration-300">
                    Cancelar
                </button>

            </div>
        </form>
    </div>
</div>

<script>
    const filasModeloLista = Array.from(document.querySelectorAll('#tbody_modelos tr'));
    let paginaActualModeloLista = 1;
    const filasPorPaginaModeloLista = 5;

    function mostrarPaginaModeloLista() {
        const inicio = (paginaActualModeloLista - 1) * filasPorPaginaModeloLista;
        const fin = inicio + filasPorPaginaModeloLista;
        const textoFiltro = document.getElementById('buscar_modelo').value.toLowerCase();

        let visibles = 0;
        let totalFiltradas = 0;

        filasModeloLista.forEach((fila) => {
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(textoFiltro)) {
                totalFiltradas++;
                if (visibles >= inicio && visibles < fin) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        const totalPaginas = Math.ceil(totalFiltradas / filasPorPaginaModeloLista);
        document.getElementById('pagina_actual_modelo_lista').textContent = `Página ${paginaActualModeloLista} de ${totalPaginas}`;
    }

    function cambiarPaginaModeloLista(direccion) {
        const textoFiltro = document.getElementById('buscar_modelo').value.toLowerCase();
        const totalFiltradas = filasModeloLista.filter(f => f.textContent.toLowerCase().includes(textoFiltro)).length;
        const totalPaginas = Math.ceil(totalFiltradas / filasPorPaginaModeloLista);

        paginaActualModeloLista += direccion;
        if (paginaActualModeloLista < 1) paginaActualModeloLista = 1;
        if (paginaActualModeloLista > totalPaginas) paginaActualModeloLista = totalPaginas;
        mostrarPaginaModeloLista();
    }

    document.getElementById('buscar_modelo').addEventListener('input', () => {
        paginaActualModeloLista = 1;
        mostrarPaginaModeloLista();
    });

    window.addEventListener('DOMContentLoaded', () => {
        mostrarPaginaModeloLista();
    });
</script>

<script>
/**
 * Resalta la fila seleccionada.
 * Si el checkbox está marcado, añade una clase "selected-row".
 * Si el checkbox se desmarca, elimina el resaltado.
 */
function highlightRow(checkbox) {
    const row = checkbox.closest('tr'); // Obtiene la fila correspondiente al checkbox
    if (checkbox.checked) {
        row.classList.add('selected-row'); // Aplica el estilo de la fila seleccionada
    } else {
        row.classList.remove('selected-row'); // Remueve el estilo si se desmarca
    }
}
</script>
<!-- Script para alternar entre opciones -->
<script>
    function toggleModeloSelection() {
        const selectOption = document.getElementById('vincular_opcion').value;
        document.getElementById('vincular_uno').classList.add('hidden');
        document.getElementById('vincular_varios').classList.add('hidden');

        if (selectOption === 'uno') {
            document.getElementById('vincular_uno').classList.remove('hidden');
        } else if (selectOption === 'varios') {
            document.getElementById('vincular_varios').classList.remove('hidden');
        }
    }
</script>
<!-- Script para alternar entre opciones -->
<script>
    function toggleMarcaModeloSelection() {
        const selectOption = document.getElementById('modelo_vincular_opcion').value;
        document.getElementById('modelo_vincular_una').classList.add('hidden');
        document.getElementById('modelo_vincular_varias').classList.add('hidden');

        if (selectOption === 'una') {
            document.getElementById('modelo_vincular_una').classList.remove('hidden');
        } else if (selectOption === 'varias') {
            document.getElementById('modelo_vincular_varias').classList.remove('hidden');
        }
    }

    function highlightModeloRow(checkbox) {
        const row = checkbox.closest('tr');
        row.classList.toggle('bg-blue-100', checkbox.checked);
    }

    function toggleModeloSelectiones(row) {
        const checkbox = row.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        highlightModeloRow(checkbox);
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  cargarMarcas();
});

function cargarMarcas() {
  fetch('obtener_marcas.php')
    .then(response => response.json())
    .then(data => {
      const marcaSelect = document.getElementById('marca');
      marcaSelect.innerHTML = '<option value="" disabled selected>Seleccione una marca</option>';

      data.forEach(marca => {
        const option = document.createElement('option');
        option.value = marca.id_marca;
        option.textContent = marca.nombre_marca;
        marcaSelect.appendChild(option);
      });
    })
    .catch(error => {
      console.error('Error cargando marcas:', error);
    });
}
</script>

<script>
function cargarModelos() {
  const selectMarca = document.getElementById("marca");
  const selectModelo = document.getElementById("modelo");
  if (!selectMarca || !selectModelo) return;

  const idMarca = selectMarca.value;
  if (!idMarca) {
    selectModelo.innerHTML = '<option value="">Seleccione una marca primero</option>';
    return;
  }

  fetch(`cargar_modelos.php?id_marca=${idMarca}`)
    .then(res => res.json())
    .then(modelos => {
      selectModelo.innerHTML = ''; // Limpiar
      modelos.forEach(modelo => {
        const option = document.createElement('option');
        option.value = modelo.id;
        option.textContent = modelo.nombre;
        selectModelo.appendChild(option);
      });
    })
    .catch(() => {
      selectModelo.innerHTML = '<option value="">Seleccione un modelo</option>';
    });
}

</script>

<script>
function convertirMayusculas(input) {
    input.value = input.value.toUpperCase();
}
</script>

<script>
    const filasModelo = Array.from(document.querySelectorAll('#tbody_modelo_marca tr'));
    let paginaModeloActual = 1;
    const filasPorPaginaModelo = 5;

    function mostrarPaginaModelo() {
        const inicio = (paginaModeloActual - 1) * filasPorPaginaModelo;
        const fin = inicio + filasPorPaginaModelo;
        const textoFiltro = document.getElementById('buscar_modelo_marca').value.toLowerCase();

        let visibles = 0;
        let totalFiltradas = 0;

        filasModelo.forEach((fila, i) => {
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(textoFiltro)) {
                totalFiltradas++;
                if (visibles >= inicio && visibles < fin) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        const totalPaginas = Math.ceil(totalFiltradas / filasPorPaginaModelo);
        document.getElementById('pagina_actual_modelo').textContent = `Página ${paginaModeloActual} de ${totalPaginas}`;
    }

    function cambiarPaginaModelo(direccion) {
        const textoFiltro = document.getElementById('buscar_modelo_marca').value.toLowerCase();
        const totalFiltradas = filasModelo.filter(f => f.textContent.toLowerCase().includes(textoFiltro)).length;
        const totalPaginas = Math.ceil(totalFiltradas / filasPorPaginaModelo);

        paginaModeloActual += direccion;
        if (paginaModeloActual < 1) paginaModeloActual = 1;
        if (paginaModeloActual > totalPaginas) paginaModeloActual = totalPaginas;
        mostrarPaginaModelo();
    }

    document.getElementById('buscar_modelo_marca').addEventListener('input', () => {
        paginaModeloActual = 1;
        mostrarPaginaModelo();
    });

    // Mostrar página inicial al cargar
    window.addEventListener('DOMContentLoaded', () => {
        mostrarPaginaModelo();
    });
</script>

<script>
function abrirVentanaEmergenteMarca() {
  const modal = document.getElementById('modalMarca');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function cerrarVentanaEmergenteMarca() {
  const modal = document.getElementById('modalMarca');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}


// Función para abrir la ventana emergente
function abrirVentanaEmergenteModelo() {
    const ventana = document.getElementById('ventanaEmergenteModelo');
    if (ventana) {
        ventana.classList.remove('hidden'); // Mostrar la ventana
    } else {
        console.error('Elemento con ID "ventanaEmergenteModelo" no encontrado.');
    }
}

// Función para cerrar la ventana emergente
function cerrarVentanaEmergenteModelo() {
    const ventana = document.getElementById('ventanaEmergenteModelo');
    if (ventana) {
        ventana.classList.add('hidden'); // Ocultar la ventana
        const form = document.getElementById('formRegistrarModelo');
        if (form) {
            form.reset(); // Limpiar el formulario
        }
    } else {
        console.error('Elemento con ID "ventanaEmergenteModelo" no encontrado.');
    }
}
</script>

<script>
  // Función para guardar Marca junto con Modelos vinculados
  function guardarMarca() {
    const nombreMarca = document.getElementById('nombre_marca').value.trim();
    const vincularOpcion = document.getElementById('vincular_opcion').value;
    let modelosSeleccionados = [];

    if (vincularOpcion === "uno") {
      const modeloUno = document.getElementById('modelo_uno').value;
      if (modeloUno) modelosSeleccionados.push(modeloUno);
    } else if (vincularOpcion === "varios") {
      document.querySelectorAll("input[name='modelos[]']:checked").forEach(checkbox => {
        modelosSeleccionados.push(checkbox.value);
      });
    }

    if (!nombreMarca || !vincularOpcion || modelosSeleccionados.length === 0) {
      alert("Por favor complete todos los campos obligatorios.");
      return;
    }

    // Preparar datos para enviar vía AJAX
    const formData = new FormData();
    formData.append("nombre_marca", nombreMarca);
    formData.append("vincular_opcion", vincularOpcion);
    modelosSeleccionados.forEach(id => formData.append("modelos[]", id));

    fetch('guardar_marca_producto.php', {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert("Marca registrada exitosamente.");
        document.getElementById("form-marca").reset();
        if (typeof cerrarVentanaEmergente === "function") cerrarVentanaEmergente();
        actualizarSelectMarca(data.id_nueva_marca, nombreMarca);
      } else {
        alert("Error: " + data.mensaje);
      }
    })
    .catch(error => {
      console.error("Error en la petición:", error);
      alert("Error en el servidor.");
    });
  }

  // Función para agregar la nueva Marca al select y seleccionarla
  function actualizarSelectMarca(id, nombre) {
    const select = document.getElementById("marca");
    if (!select) return;
    const nuevaOpcion = document.createElement("option");
    nuevaOpcion.value = id;
    nuevaOpcion.textContent = nombre;
    nuevaOpcion.selected = true; // Seleccionarla automáticamente
    select.appendChild(nuevaOpcion);
    if (typeof cargarModelos === "function") cargarModelos();
  }
</script>

<script>
  // Manejo del formulario para guardar "Modelo Marca"
  document.getElementById('formRegistrarModelo').addEventListener('submit', function (event) {
    event.preventDefault(); // Evita el recargo de la página

    const formData = new FormData(this); // Crear un objeto FormData con los datos del formulario

    fetch('guardar_modelo_producto.php', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(data => {
      console.log(data); // Para depuración: muestra la respuesta en la consola

      if (data.success) {
        // Actualizar la tabla dinámicamente
        const tbody = document.querySelector('#modelo_vincular_varias tbody'); // Seleccionar cuerpo de la tabla
        if (tbody) {
          const row = document.createElement('tr');
          row.classList.add('hover:bg-blue-50');
          row.innerHTML = `
            <td class='px-4 py-2'>
              <input type='checkbox' name='modelos[]' value='${data.id_modelo}' onchange='highlightRow(this)'>
            </td>
            <td class='px-4 py-2 text-gray-700'>${data.nombre_modelo}</td>
          `;
          tbody.appendChild(row);
        }

        // Actualizar el select dinámicamente
        const selectModelo = document.getElementById('modelo_marca_una');
        if (selectModelo) {
          const nuevaOpcion = document.createElement('option');
          nuevaOpcion.value = data.id_modelo;
          nuevaOpcion.textContent = data.nombre_modelo;
          selectModelo.appendChild(nuevaOpcion);
        }

        mostrarMensaje('success', '¡Modelo se ha guardado exitosamente!');

        if (typeof cerrarVentanaEmergenteModelo === "function") cerrarVentanaEmergenteModelo();

        this.reset(); // Limpiar formulario
      } else {
        mostrarMensaje('error', data.message || 'Error desconocido al guardar el modelo.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      mostrarMensaje('error', 'Ocurrió un error al intentar guardar el Modelo.');
    });
  });

  // Función para mostrar mensajes con estilo centrado y animación
  function mostrarMensaje(tipo, mensaje) {
    const mensajeContenedor = document.createElement('div');
    mensajeContenedor.classList.add('fixed', 'inset-0', 'flex', 'items-center', 'justify-center', 'z-50');

    const mensajeContenido = document.createElement('div');
    mensajeContenido.classList.add('bg-white', 'rounded-lg', 'shadow-lg', 'p-6', 'max-w-sm', 'w-full', 'relative', 'flex', 'flex-col', 'items-center');

    const mensajeIcono = document.createElement('div');
    mensajeIcono.style.width = '80px';
    mensajeIcono.style.height = '80px';
    mensajeIcono.classList.add(
      tipo === 'error' ? 'bg-red-100' : 'bg-green-100',
      'rounded-full',
      'shadow-lg',
      'animate-pulse',
      'mb-4',
      'flex',
      'justify-center',
      'items-center'
    );

    const icono = document.createElement('i');
    icono.classList.add('text-4xl', 'fas');
    if (tipo === 'error') {
      icono.classList.add('fa-exclamation-triangle', 'text-red-500');
    } else {
      icono.classList.add('fa-check-circle', 'text-green-500');
    }
    mensajeIcono.appendChild(icono);

    const mensajeTitulo = document.createElement('h2');
    mensajeTitulo.textContent = tipo === 'error' ? '¡Error!' : '¡Éxito!';
    mensajeTitulo.classList.add('text-xl', 'font-bold', tipo === 'error' ? 'text-red-600' : 'text-green-600', 'mb-2');

    const mensajeTexto = document.createElement('p');
    mensajeTexto.textContent = mensaje;
    mensajeTexto.classList.add('text-gray-700', 'text-center');

    const botonCerrar = document.createElement('button');
    botonCerrar.innerHTML = '<i class="fas fa-times"></i>';
    botonCerrar.classList.add(
      'absolute',
      'top-2',
      'right-2',
      tipo === 'error' ? 'bg-red-500' : 'bg-green-500',
      tipo === 'error' ? 'hover:bg-red-600' : 'hover:bg-green-600',
      'text-white',
      'rounded-full',
      'p-2',
      'focus:outline-none'
    );
    botonCerrar.onclick = () => {
      document.body.removeChild(mensajeContenedor);
    };

    mensajeContenido.appendChild(mensajeIcono);
    mensajeContenido.appendChild(mensajeTitulo);
    mensajeContenido.appendChild(mensajeTexto);
    mensajeContenido.appendChild(botonCerrar);
    mensajeContenedor.appendChild(mensajeContenido);

    document.body.appendChild(mensajeContenedor);
  }

  // Función para resaltar la fila cuando un checkbox de modelo se selecciona
  function highlightRow(checkbox) {
    if (checkbox.checked) {
      checkbox.closest('tr').classList.add('bg-blue-100');
    } else {
      checkbox.closest('tr').classList.remove('bg-blue-100');
    }
  }
</script>


<script>
   function validarNombreModelo() {
    const nombreModelo = document.getElementById('nombre_modelo').value.trim(); // Limpiar espacios
    const mensajeErrorInputModelo = document.getElementById('mensaje-error-input-modelo'); // Mensaje de error
    const mensajeExitoInputModelo = document.getElementById('mensaje-exito-input-modelo'); // Mensaje de éxito
    const mensajeGlobalModelo = document.getElementById('mensaje-global-modelo'); // Contenedor del mensaje global
    const tipoMensajeGlobalModelo = document.getElementById('tipo-mensaje-global-modelo'); // Tipo de mensaje global
    const textoMensajeGlobalModelo = document.getElementById('texto-mensaje-global-modelo'); // Texto del mensaje global
    const botonGuardar = document.getElementById('guardar'); // Botón de guardar

    if (nombreModelo !== '') { // Validación dinámica
        fetch(`validar_modelo.php?nombre=${encodeURIComponent(nombreModelo)}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    // Mostrar mensaje de error
                    mensajeErrorInputModelo.classList.remove('hidden');
                    mensajeExitoInputModelo.classList.add('hidden');

                    // Configurar mensaje global de error
                    tipoMensajeGlobalModelo.textContent = "Error:";
                    textoMensajeGlobalModelo.textContent = "Este modelo ya existe. Por favor, ingrese otro.";
                    mensajeGlobalModelo.classList.remove('hidden');
                    mensajeGlobalModelo.classList.add('bg-red-100', 'text-red-700');

                    // Deshabilitar botón guardar
                    botonGuardar.disabled = true;
                    botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
                    botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
                } else {
                    // Mostrar mensaje de éxito
                    mensajeErrorInputModelo.classList.add('hidden');
                    mensajeExitoInputModelo.classList.remove('hidden');

                    // Ocultar mensaje global
                    mensajeGlobalModelo.classList.add('hidden');

                    // Habilitar botón guardar
                    botonGuardar.disabled = false;
                    botonGuardar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    botonGuardar.classList.add('bg-green-500', 'hover:bg-green-600');
                }
            })
            .catch(error => {
                console.error('Error en la validación:', error);
            });
    } else {
        // Si el campo está vacío, ocultar mensajes y deshabilitar botón
        mensajeErrorInputModelo.classList.add('hidden');
        mensajeExitoInputModelo.classList.add('hidden');
        mensajeGlobalModelo.classList.add('hidden');

        botonGuardar.disabled = true;
        botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
        botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
    }
}
</script>

<!-- Script para validación en línea -->
<script>
    function validarNombreMarca() {
        const nombreMarca = document.getElementById('nombre_marca').value;
        const mensajeErrorInput = document.getElementById('mensaje-error-input-marca');
        const mensajeExitoInput = document.getElementById('mensaje-exito-input-marca');
        const mensajeGlobal = document.getElementById('mensaje-global');
        const tipoMensajeGlobal = document.getElementById('tipo-mensaje-global');
        const textoMensajeGlobal = document.getElementById('texto-mensaje-global');
        const botonGuardar = document.getElementById('guardar');

        if (nombreMarca.trim() !== '') {
            fetch(`validar_marca.php?nombre=${encodeURIComponent(nombreMarca)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.existe) {
                        // Mostrar mensaje de error en el input
                        mensajeErrorInput.classList.remove('hidden');
                        mensajeExitoInput.classList.add('hidden');

                        // Mostrar mensaje global de error
                        tipoMensajeGlobal.textContent = "Error:";
                        textoMensajeGlobal.textContent = "Esta marca ya existe. Por favor, ingrese otra.";
                        mensajeGlobal.classList.remove('hidden');
                        mensajeGlobal.classList.add('bg-red-100', 'text-red-700');

                        botonGuardar.disabled = true; // Deshabilitar el botón
                        botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
                        botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
                    } else {
                        // Mostrar mensaje de éxito en el input
                        mensajeErrorInput.classList.add('hidden');
                        mensajeExitoInput.classList.remove('hidden');

                        // Ocultar mensaje global
                        mensajeGlobal.classList.add('hidden');

                        botonGuardar.disabled = false; // Habilitar el botón
                        botonGuardar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        botonGuardar.classList.add('bg-green-500', 'hover:bg-green-600');
                    }
                })
                .catch(error => {
                    console.error('Error en validación AJAX:', error);
                });
        } else {
            mensajeErrorInput.classList.add('hidden');
            mensajeExitoInput.classList.add('hidden');
            mensajeGlobal.classList.add('hidden');

            botonGuardar.disabled = true;
            botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
            botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
        }
    }
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
    // Función para actualizar la abreviatura dentro del input
    function actualizarAbreviatura() {
        const clasificacionSelect = document.getElementById('clasificacion');
        const abreviaturaSpan = document.getElementById('abreviatura');

        // Obtener la abreviatura de la opción seleccionada
        const abreviaturaSeleccionada = clasificacionSelect.options[clasificacionSelect.selectedIndex].getAttribute('data-abreviatura');
        abreviaturaSpan.textContent = abreviaturaSeleccionada || ''; // Mostrar abreviatura
    }
</script>
<script>
    function mostrarOpcionesProveedor() {
        const seleccion = document.getElementById('vincularProveedor').value;
        const opcionesProveedor = document.getElementById('opcionesProveedor');
        const selectProveedor = document.getElementById('selectProveedor');
        const tablaProveedores = document.getElementById('tablaProveedores');

        // Resetear todas las opciones
        opcionesProveedor.classList.add('hidden');
        selectProveedor.classList.add('hidden');
        tablaProveedores.classList.add('hidden');

        // Mostrar la opción correspondiente con indicadores activos
        if (seleccion === 'uno') {
            opcionesProveedor.classList.remove('hidden');
            selectProveedor.classList.remove('hidden');
            selectProveedor.style.borderColor = "#3b82f6"; // Indicador azul
        } else if (seleccion === 'muchos') {
            opcionesProveedor.classList.remove('hidden');
            tablaProveedores.classList.remove('hidden');
            tablaProveedores.style.borderColor = "#10b981"; // Indicador verde
        }
    }
</script>
<script>
    document.getElementById('marca').addEventListener('change', function() {
        const marcaId = this.value;
        const modeloSelect = document.getElementById('modelo');

        // Limpiar el select y agregar la opción por defecto
        modeloSelect.innerHTML = '';
        const opcionDefault = document.createElement('option');
        opcionDefault.value = '';
        opcionDefault.textContent = 'Seleccione un modelo';
        opcionDefault.disabled = true;
        opcionDefault.selected = true;
        modeloSelect.appendChild(opcionDefault);

        if (marcaId) {
            fetch(`obtener_modelos_producto.php?marca=${encodeURIComponent(marcaId)}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(modelo => {
                        const option = document.createElement('option');
                        option.value = modelo.id_modelo;
                        option.textContent = modelo.nombre_modelo;
                        modeloSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error al cargar los modelos:', error);
                });
        }
    });
</script>




<script>
    function actualizarClasificaciones() {
        const tipoId = document.getElementById('tipo').value; // Obtener el ID del tipo seleccionado
        const clasificacionSelect = document.getElementById('clasificacion'); // Select de clasificaciones

        // Limpiar el contenido del select de clasificaciones
        clasificacionSelect.innerHTML = '<option value="">Seleccionar</option>';

        if (tipoId) {
            // Hacer la llamada AJAX para obtener las clasificaciones relacionadas
            fetch(`obtener_clasificaciones_producto.php?tipo=${tipoId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(clasificacion => {
                        clasificacionSelect.innerHTML += `<option value="${clasificacion.id_clasificacion}" data-abreviatura="${clasificacion.abreviacion_clasificacion}">${clasificacion.nombre_clasificacion}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error al cargar las clasificaciones:', error);
                });
        }
    }

    function actualizarAbreviatura() {
        const clasificacionSelect = document.getElementById('clasificacion');
        const abreviaturaSpan = document.getElementById('abreviatura');

        // Obtener la abreviatura de la opción seleccionada
        const abreviaturaSeleccionada = clasificacionSelect.options[clasificacionSelect.selectedIndex]?.getAttribute('data-abreviatura');
        abreviaturaSpan.textContent = abreviaturaSeleccionada || ''; // Mostrar abreviatura en el input
    }


    let cropper;
const modal = document.getElementById('cropper-modal');
const imagenInput = document.getElementById('imagen');
const imagenCropper = document.getElementById('imagen-cropper');
const preview = document.getElementById('imagen-preview');
const placeholder = document.getElementById('imagen-placeholder');

function mostrarCropper() {
    const archivo = imagenInput.files[0];
    if (!archivo) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        imagenCropper.src = e.target.result;
        modal.classList.remove('hidden');
        cropper = new Cropper(imagenCropper, {
            aspectRatio: 1, // Recorte cuadrado
            viewMode: 1,
        });
    };
    reader.readAsDataURL(archivo);
}

function cerrarCropper() {
    modal.classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

function guardarRecorte() {
    if (cropper) {
        const canvas = cropper.getCroppedCanvas({
            width: 500, // Ajusta el tamaño del recorte
            height: 500,
        });
        preview.src = canvas.toDataURL('image/png'); // Mostrar la imagen recortada en la previsualización
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        cerrarCropper();
    }
}
</script>

<!-- Modal para Tipo -->
<div id="modalTipo" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-xl">
        <h2 class="text-xl font-bold text-blue-600 mb-4"><i class="fas fa-cube mr-2"></i>Registrar Nuevo Tipo</h2>
        <form id="form-tipo-varios_tipo" action="guardar_tipo_producto.php" method="POST" class="space-y-4">
            <div>
                <label for="nombre_tipo_varios_tipo" class="font-semibold block mb-1">Nombre del Tipo:</label>
                <input type="text" id="nombre_tipo_varios_tipo" name="nombre_tipo"
                    class="w-full border border-gray-300 rounded-lg p-2" required
                    oninput="convertirMayusculas(this); validarNombreTipo()">
                <small id="mensaje-error-input-tipo" class="text-red-500 hidden">Este tipo ya existe</small>
                <small id="mensaje-exito-input-tipo" class="text-green-500 hidden">¡Disponible!</small>
            </div>
            <!-- Tabla de Clasificaciones -->
            <!-- Vincular Clasificaciones -->
    <div >
        <label for="id_clasificacion" class="block font-semibold flex items-center">
            <i class="fas fa-link text-blue-500 mr-2"></i> <!-- Ícono de vincular -->
            Seleccionar Clasificaciones:<span class="text-red-600">*</span>
        </label>
        <div id="vincular_varios" class="overflow-auto border border-gray-300 rounded" style="max-height: 300px;">
            <table class="table-auto w-full">
                <thead class="bg-blue-100 text-blue-600">
                    <tr>
                        <th class="px-4 py-2 border">Seleccionar</th>
                        <th class="px-4 py-2 border">Nombre de la Clasificación</th>
                        <th class="px-4 py-2 border">Abreviación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Obtener clasificaciones
                    $resultado = $conexion->query("SELECT id_clasificacion, nombre_clasificacion, abreviacion_clasificacion FROM clasificacion");
                    while ($fila = $resultado->fetch_assoc()) {
                        echo "<tr onclick='toggleRowSelection(this)'>
                                <td class='px-4 py-2 border text-center'>
                                    <input type='checkbox' name='id_clasificacion[]' value='" . $fila['id_clasificacion'] . "' onchange='highlightRow(this)'>
                                </td>
                                <td class='px-4 py-2 border'>" . htmlspecialchars($fila['nombre_clasificacion'], ENT_QUOTES, 'UTF-8') . "</td>
                                <td class='px-4 py-2 border'>" . htmlspecialchars($fila['abreviacion_clasificacion'], ENT_QUOTES, 'UTF-8') . "</td>
                              </tr>";
                    }
                    $conexion->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Guardar</button>
                <button type="button" onclick="cerrarModalTipo()" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<script> function abrirVentanaEmergenteTipo() {
    document.getElementById('modalTipo').classList.remove('hidden');
}
function cerrarModalTipo() {
    document.getElementById('modalTipo').classList.add('hidden');
}
</script>


<!-- Modal para Clasificación -->
<div id="modalClasificacion" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-xl">
        <h2 class="text-xl font-bold text-blue-600 mb-4"><i class="fas fa-layer-group mr-2"></i>Registrar Nueva Clasificación</h2>
        <form id="form-clasificacion-una_clasificacion" action="guardar_clasificacion_producto.php" method="POST" class="space-y-4">
            <div>
                <label for="nombre_clasificacion_una_clasificacion" class="font-semibold block mb-1">Nombre:</label>
                <input type="text" id="nombre_clasificacion_una_clasificacion" name="nombre_clasificacion"
                    class="w-full border border-gray-300 rounded-lg p-2" required
                    oninput="convertirMayusculas(this); validarNombreClasificacion()">
                <small id="mensaje-error-input-clasificacion" class="text-red-500 hidden">Ya existe</small>
                <small id="mensaje-exito-input-clasificacion" class="text-green-500 hidden">¡Disponible!</small>
            </div>
            <div>
                <label for="abreviatura_clasificacion_una_clasificacion" class="font-semibold block mb-1">Abreviatura:</label>
                <input type="text" id="abreviatura_clasificacion_una_clasificacion" name="abreviatura_clasificacion"
                    class="w-full border border-gray-300 rounded-lg p-2" required
                    oninput="convertirMayusculas(this); validarAbreviatura()">
                <small id="mensaje-error-abreviatura" class="text-red-500 hidden">Debe tener 1-3 letras</small>
                <small id="mensaje-exito-abreviatura" class="text-green-500 hidden">¡Válido!</small>
            </div>
            <!-- Vincular Tipos -->
<div>
    <label for="vincular_opcion_tipo" class="block font-semibold flex items-center">
        <i class="fas fa-link text-blue-500 mr-2"></i>
        ¿Desea relacionarla a un tipo?:<span class="text-red-600">*</span>
    </label>
    <div class="flex space-x-4">
        <select id="vincular_opcion_tipo" name="vincular_opcion_tipo"
                class="w-full border border-gray-300 rounded-lg p-2" required
                onchange="toggleSeleccionRelacionada('tipo')">
            <option value="" disabled selected>Seleccione una opción</option>
            <option value="uno">Relacionar a un tipo</option>
            <option value="varios">Relacionar a varios tipos</option>
        </select>
      
    </div>
</div>

<!-- Relacionar a un Tipo -->
<div id="relacionar_uno_tipo" class="hidden mt-2">
    <label for="tipo_uno" class="block font-semibold flex items-center">
        <i class="fas fa-cube text-blue-500 mr-2"></i>
        Seleccionar un tipo <span class="text-red-600">*</span>
    </label>
    <select name="tipo_uno" id="tipo_uno" class="w-full border border-gray-300 rounded-lg p-2">
        <?php
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        $resultado = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo");
        while ($fila = $resultado->fetch_assoc()) {
            echo "<option value='{$fila['id_tipo']}'>{$fila['nombre_tipo']}</option>";
        }
        $conexion->close();
        ?>
    </select>
</div>

<!-- Vincular a Varios Tipos -->
<div id="vincular_varios_tipo" class="hidden mt-2">
    <label class="block font-semibold flex items-center">
        <i class="fas fa-cube text-blue-500 mr-2"></i>Selecciona los tipos <span class="text-red-600">*</span>
    </label>
    <!-- Buscador -->
<div class="mb-2">
    <input type="text" id="buscador_tipos" onkeyup="filtrarTipos()" placeholder="Buscar tipo..."
        class="w-full p-2 border border-gray-300 rounded-lg" />
</div>

<!-- Tabla con paginación -->
<table class="table-auto w-full border border-gray-300 rounded-lg shadow-md">
    <thead class="bg-blue-100 text-blue-600">
        <tr>
            <th class="px-4 py-2 text-left font-bold">Seleccionar</th>
            <th class="px-4 py-2 text-left font-bold">Nombre del Tipo</th>
        </tr>
    </thead>
    <tbody id="tbody_varios_tipo">
        <?php
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        $resultado = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo");
        while ($fila = $resultado->fetch_assoc()) {
            echo "
            <tr class='hover:bg-blue-50' onclick='toggleSelection(this)'>
                <td class='px-4 py-2'>
                    <input type='checkbox' name='tipos[]' value='{$fila['id_tipo']}' onchange='highlightRow(this)'>
                </td>
                <td class='px-4 py-2 text-gray-700'>{$fila['nombre_tipo']}</td>
            </tr>";
        }
        $conexion->close();
        ?>
    </tbody>
</table>

<!-- Controles de paginación -->
<div class="flex justify-between items-center mt-4">
    <button type="button" onclick="cambiarPaginaTipos(-1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Anterior</button>
    <span id="pagina_actual_varios_tipo" class="text-gray-700 font-semibold"></span>
    <button type="button" onclick="cambiarPaginaTipos(1)"
        class="bg-blue-500 text-white px-3 py-1 rounded-lg hover:bg-blue-600">Siguiente</button>
</div>

</div>

            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Guardar</button>
                <button type="button" onclick="cerrarModalClasificacion()" class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<script>
    function abrirVentanaEmergenteClasificacion() {
    document.getElementById('modalClasificacion').classList.remove('hidden');
}
function cerrarModalClasificacion() {
    document.getElementById('modalClasificacion').classList.add('hidden');
}

</script>

<script>
function toggleSeleccionRelacionada(nombre) {
    const valorSeleccionado = document.getElementById(`vincular_opcion_${nombre}`).value;

    document.getElementById(`relacionar_uno_${nombre}`).classList.add('hidden');
    document.getElementById(`vincular_varios_${nombre}`).classList.add('hidden');

    if (valorSeleccionado === 'uno') {
        document.getElementById(`relacionar_uno_${nombre}`).classList.remove('hidden');
    } else if (valorSeleccionado === 'varios') {
        document.getElementById(`vincular_varios_${nombre}`).classList.remove('hidden');
    }
}
</script>
<script>
let paginaActualTipos = 1;
const filasPorPaginaTipos = 5;

function cambiarPaginaTipos(direccion) {
    const tabla = document.getElementById("tbody_varios_tipo");
    const filas = tabla.querySelectorAll("tr");
    const totalPaginas = Math.ceil(filas.length / filasPorPaginaTipos);

    paginaActualTipos += direccion;
    if (paginaActualTipos < 1) paginaActualTipos = 1;
    if (paginaActualTipos > totalPaginas) paginaActualTipos = totalPaginas;

    mostrarPaginaTipos();
}

function mostrarPaginaTipos() {
    const tabla = document.getElementById("tbody_varios_tipo");
    const filas = tabla.querySelectorAll("tr");

    filas.forEach((fila, index) => {
        fila.style.display = (index >= (paginaActualTipos - 1) * filasPorPaginaTipos &&
                              index < paginaActualTipos * filasPorPaginaTipos)
                              ? "" : "none";
    });

    document.getElementById("pagina_actual_varios_tipo").innerText = "Página " + paginaActualTipos;
}

function filtrarTipos() {
    const input = document.getElementById("buscador_tipos");
    const filtro = input.value.toLowerCase();
    const tabla = document.getElementById("tbody_varios_tipo");
    const filas = tabla.getElementsByTagName("tr");

    for (let i = 0; i < filas.length; i++) {
        const celdas = filas[i].getElementsByTagName("td");
        if (celdas.length > 1) {
            const texto = celdas[1].textContent || celdas[1].innerText;
            filas[i].style.display = texto.toLowerCase().includes(filtro) ? "" : "none";
        }
    }

    paginaActualTipos = 1;
    mostrarPaginaTipos();
}

// Mostrar la primera página al cargar
document.addEventListener("DOMContentLoaded", mostrarPaginaTipos);
</script>
<script>
document.getElementById('form-tipo-varios_tipo').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch('guardar_tipo_producto.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Tipo guardado con éxito');
            cerrarModalTipo();
            // ✅ Usa IDs reales: tipo y clasificacion
            actualizarSelectsTipoYClasificacion(data.nuevo_tipo, data.clasificaciones);
        } else {
            alert(data.error || 'Ocurrió un error inesperado.');
        }
    })
    .catch(error => {
        console.error('Error al guardar tipo:', error);
        alert('Error de red o servidor');
    });
});

function actualizarSelectsTipoYClasificacion(nuevoTipo, clasificaciones) {
    const selectTipo = document.getElementById('tipo');
    const selectClasificacion = document.getElementById('clasificacion');

    // ✅ Añadir el nuevo tipo si no existe ya
    if (selectTipo && nuevoTipo) {
        const option = document.createElement('option');
        option.value = nuevoTipo.id;
        option.textContent = nuevoTipo.nombre;
        option.selected = true;
        selectTipo.appendChild(option);
    }

    // ✅ Limpiar clasificaciones actuales
    if (selectClasificacion) {
        selectClasificacion.innerHTML = '<option value="" data-abreviatura="">Seleccionar</option>';

        clasificaciones.forEach(clasif => {
            const option = document.createElement('option');
            option.value = clasif.id;
            option.textContent = clasif.nombre;
            option.setAttribute('data-abreviatura', clasif.abreviacion || '');
            selectClasificacion.appendChild(option);
        });
    }
}
</script>


<script>
function cargarTipos() {
    fetch('obtener_tipos.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('tipo');
            if (!select) return;

            // Guardar la selección actual si existe
            const seleccionActual = select.value;

            // Limpiar opciones anteriores
            select.innerHTML = '<option value="" disabled selected>Seleccione un tipo</option>';

            // Insertar nuevos tipos
            data.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nombre;
                select.appendChild(option);
            });

            // Restaurar la selección si todavía existe
            if ([...select.options].some(opt => opt.value === seleccionActual)) {
                select.value = seleccionActual;
            }
        })
        .catch(error => {
            console.error('Error al cargar tipos:', error);
        });
}

// Llamar al cargar la página
document.addEventListener('DOMContentLoaded', cargarTipos);
</script>

<script>
   function validarNombreTipo() {
    const nombreTipo = document.getElementById('nombre_tipo_varios_tipo').value.trim(); // Limpiar espacios
    const mensajeErrorInputTipo = document.getElementById('mensaje-error-input-tipo'); // Mensaje de error
    const mensajeExitoInputTipo = document.getElementById('mensaje-exito-input-tipo'); // Mensaje de éxito
    const mensajeGlobalTipo = document.getElementById('mensaje-global-tipo'); // Contenedor del mensaje global
    const tipoMensajeGlobalTipo = document.getElementById('tipo-mensaje-global-tipo'); // Tipo de mensaje global
    const textoMensajeGlobalTipo = document.getElementById('texto-mensaje-global-tipo'); // Texto del mensaje global
    const botonGuardar = document.getElementById('guardar'); // Botón de guardar

    if (nombreTipo !== '') { // Validación dinámica
        fetch(`validar_tipo.php?nombre=${encodeURIComponent(nombreTipo)}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    // Mostrar mensaje de error
                    mensajeErrorInputTipo.classList.remove('hidden');
                    mensajeExitoInputTipo.classList.add('hidden');

                    // Configurar mensaje global de error
                    tipoMensajeGlobalTipo.textContent = "Error:";
                    textoMensajeGlobalTipo.textContent = "Este tipo ya existe. Por favor, ingrese otro.";
                    mensajeGlobalTipo.classList.remove('hidden');
                    mensajeGlobalTipo.classList.add('bg-red-100', 'text-red-700');

                    // Deshabilitar botón guardar
                    botonGuardar.disabled = true;
                    botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
                    botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
                } else {
                    // Mostrar mensaje de éxito
                    mensajeErrorInputTipo.classList.add('hidden');
                    mensajeExitoInputTipo.classList.remove('hidden');

                    // Ocultar mensaje global
                    mensajeGlobalTipo.classList.add('hidden');

                    // Habilitar botón guardar
                    botonGuardar.disabled = false;
                    botonGuardar.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    botonGuardar.classList.add('bg-green-500', 'hover:bg-green-600');
                }
            })
            .catch(error => {
                console.error('Error en la validación:', error);
            });
    } else {
        // Si el campo está vacío, ocultar mensajes y deshabilitar botón
        mensajeErrorInputTipo.classList.add('hidden');
        mensajeExitoInputTipo.classList.add('hidden');
        mensajeGlobalTipo.classList.add('hidden');

        botonGuardar.disabled = true;
        botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
        botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
    }
}




  let isNombreClasificacionValid = false;
let isAbreviaturaValid = false;

function validarAbreviatura() {
    const abreviatura = document.getElementById('abreviatura_clasificacion_una_clasificacion').value.trim();
    const mensajeErrorAbreviatura = document.getElementById('mensaje-error-abreviatura');
    const mensajeExitoAbreviatura = document.getElementById('mensaje-exito-abreviatura');
    const mensajeGlobal = document.getElementById('mensaje-global-clasificacion'); // Cambiado a mensaje-global-clasificacion
    const tipoMensajeGlobal = document.getElementById('tipo-mensaje-global-clasificacion'); // Cambiado a tipo-mensaje-global-clasificacion
    const textoMensajeGlobal = document.getElementById('texto-mensaje-global-clasificacion'); // Cambiado a texto-mensaje-global-clasificacion

    if (/^[A-Za-z]{1,3}$/.test(abreviatura)) {
        fetch(`validar_abreviatura.php?abreviatura=${encodeURIComponent(abreviatura)}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    mensajeErrorAbreviatura.textContent = "Esta abreviatura ya existe.";
                    mensajeErrorAbreviatura.classList.remove('hidden');
                    mensajeExitoAbreviatura.classList.add('hidden');
                    tipoMensajeGlobal.textContent = "Error:";
                    textoMensajeGlobal.textContent = "Esta abreviatura ya existe. Por favor, ingrese otra.";
                    mensajeGlobal.classList.remove('hidden');
                    mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
                    isAbreviaturaValid = false;
                } else {
                    mensajeErrorAbreviatura.classList.add('hidden');
                    mensajeExitoAbreviatura.classList.remove('hidden');
                    isAbreviaturaValid = true;

                    if (!isNombreClasificacionValid) {
                        tipoMensajeGlobal.textContent = "Error:";
                        textoMensajeGlobal.textContent = "Debe ingresar un nombre de clasificación válido.";
                        mensajeGlobal.classList.remove('hidden');
                        mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
                    } else {
                        mensajeGlobal.classList.add('hidden');
                    }
                }
                toggleGuardarButton();
            })
            .catch(error => {
                console.error('Error en la validación de la abreviatura:', error);
            });
    } else {
        mensajeErrorAbreviatura.textContent = "La abreviatura debe tener entre 1 y 3 letras.";
        mensajeErrorAbreviatura.classList.remove('hidden');
        mensajeExitoAbreviatura.classList.add('hidden');
        tipoMensajeGlobal.textContent = "Error:";
        textoMensajeGlobal.textContent = "La abreviatura debe tener entre 1 y 3 letras.";
        mensajeGlobal.classList.remove('hidden');
        mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
        isAbreviaturaValid = false;
        toggleGuardarButton();
    }
}

function validarNombreClasificacion() {
    const nombreClasificacion = document.getElementById('nombre_clasificacion_una_clasificacion').value.trim();
    const mensajeErrorInput = document.getElementById('mensaje-error-input-clasificacion');
    const mensajeExitoInput = document.getElementById('mensaje-exito-input-clasificacion');
    const mensajeGlobal = document.getElementById('mensaje-global-clasificacion'); // Cambiado a mensaje-global-clasificacion
    const tipoMensajeGlobal = document.getElementById('tipo-mensaje-global-clasificacion'); // Cambiado a tipo-mensaje-global-clasificacion
    const textoMensajeGlobal = document.getElementById('texto-mensaje-global-clasificacion'); // Cambiado a texto-mensaje-global-clasificacion

    if (nombreClasificacion !== '') {
        fetch(`validar_clasificacion.php?nombre=${encodeURIComponent(nombreClasificacion)}`)
            .then(response => response.json())
            .then(data => {
                if (data.existe) {
                    mensajeErrorInput.textContent = "Este nombre de clasificación ya existe.";
                    mensajeErrorInput.classList.remove('hidden');
                    mensajeExitoInput.classList.add('hidden');
                    tipoMensajeGlobal.textContent = "Error:";
                    textoMensajeGlobal.textContent = "Este nombre de clasificación ya existe. Por favor, ingrese otro.";
                    mensajeGlobal.classList.remove('hidden');
                    mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
                    isNombreClasificacionValid = false;
                } else {
                    mensajeErrorInput.classList.add('hidden');
                    mensajeExitoInput.classList.remove('hidden');
                    isNombreClasificacionValid = true;

                    if (!isAbreviaturaValid && document.getElementById('abreviatura_clasificacion_una_clasificacion').value.trim() !== '') {
                        tipoMensajeGlobal.textContent = "Error:";
                        textoMensajeGlobal.textContent = "Debe ingresar una abreviatura válida.";
                        mensajeGlobal.classList.remove('hidden');
                        mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
                    } else {
                        mensajeGlobal.classList.add('hidden');
                    }
                }
                toggleGuardarButton();
            })
            .catch(error => {
                console.error('Error en la validación del nombre de clasificación:', error);
            });
    } else {
        mensajeErrorInput.textContent = "Por favor, ingrese un nombre de clasificación.";
        mensajeErrorInput.classList.remove('hidden');
        mensajeExitoInput.classList.add('hidden');
        tipoMensajeGlobal.textContent = "Error:";
        textoMensajeGlobal.textContent = "Por favor, ingrese un nombre de clasificación.";
        mensajeGlobal.classList.remove('hidden');
        mensajeGlobal.classList.add('bg-red-100', 'text-red-700');
        isNombreClasificacionValid = false;
        toggleGuardarButton();
    }
}

function toggleGuardarButton() {
    const botonGuardar = document.getElementById('guardar');
    if (isNombreClasificacionValid && isAbreviaturaValid) {
        botonGuardar.disabled = false;
        botonGuardar.classList.remove('bg-gray-400', 'cursor-not-allowed');
        botonGuardar.classList.add('bg-green-500', 'hover:bg-green-600');
    } else {
        botonGuardar.disabled = true;
        botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
        botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');
    }
}
</script>
<script>
document.getElementById('form-clasificacion-una_clasificacion').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita el envío normal

    const form = e.target;
    const formData = new FormData(form);
    const botonGuardar = form.querySelector('button[type="submit"]');
    const originalText = botonGuardar.textContent;

    // Desactiva el botón mientras se envía
    botonGuardar.disabled = true;
    botonGuardar.textContent = 'Guardando...';
    botonGuardar.classList.add('bg-gray-400', 'cursor-not-allowed');
    botonGuardar.classList.remove('bg-green-500', 'hover:bg-green-600');

    fetch('guardar_clasificacion_producto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Opcional: mostrar mensaje
            alert('Clasificación guardada con éxito');

            // Cerrar modal (debes tener esa función)
            cerrarModalClasificacion();

            // Opcional: recargar una parte de la vista o toda la página
            if (typeof recargarClasificaciones === 'function') {
                recargarClasificaciones(); // función tuya
            } else {
                location.reload(); // o recargar todo si no tienes una función parcial
            }
        } else {
            alert(data.message || 'Error al guardar la clasificación');
        }
    })
    .catch(error => {
        console.error('Error en AJAX:', error);
        alert('Ocurrió un error inesperado.');
    })
    .finally(() => {
        // Reactivar el botón
        botonGuardar.disabled = false;
        botonGuardar.textContent = originalText;
        botonGuardar.classList.remove('bg-gray-400', 'cursor-not-allowed');
        botonGuardar.classList.add('bg-green-500', 'hover:bg-green-600');
    });
});
</script>
<!-- Modal: Registrar Proveedor -->
<div id="modalProveedor" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-xl relative">
    <!-- Botón de cerrar -->
    <button onclick="cerrarVentanaEmergenteProveedor()" class="absolute top-2 right-2 text-gray-600 hover:text-red-500 text-xl">&times;</button>
    
    <h2 class="text-2xl font-bold mb-4 text-center text-green-600">
      <i class="fas fa-user-plus mr-2"></i>Nuevo Proveedor
    </h2>

    <!-- Aquí va el formulario que ya tienes -->
    <form id="providerForm" action="guardar_proveedor_producto.php" method="post">
      <!-- Nombre del Proveedor -->
      <div class="mb-4">
        <label for="nombre_proveedor" class="block font-semibold flex items-center">
          <i class="fas fa-user-tie text-green-500 mr-2"></i>
          Nombre del proveedor: <span class="text-red-600">*</span>
        </label>
        <input type="text" id="nombre_proveedor" name="nombre_proveedor" placeholder="Nombre del proveedor..." class="w-full border border-gray-300 rounded-lg p-2" required>
        <small class="text-gray-500">Ejemplo: Proveedor Global S.A.</small>
      </div>

      <!-- Teléfono del Proveedor -->
      <div class="mb-4">
        <label for="telefono" class="block font-semibold flex items-center">
          <i class="fas fa-phone-alt text-blue-500 mr-2"></i>
          Teléfono del proveedor:
        </label>
        <input type="text" id="telefono" name="telefono" placeholder="Teléfono del proveedor..." class="w-full border border-gray-300 rounded-lg p-2">
        <small class="text-gray-500">Ejemplo: 0414-1234567</small>
      </div>

      <!-- Email del Proveedor -->
      <div class="mb-4">
        <label for="email" class="block font-semibold flex items-center">
          <i class="fas fa-envelope text-orange-500 mr-2"></i>
          Email del proveedor:
        </label>
        <input type="email" id="email" name="email" placeholder="Email del proveedor..." class="w-full border border-gray-300 rounded-lg p-2">
        <small class="text-gray-500">Ejemplo: contacto@proveedor.com</small>
      </div>

      <!-- Dirección del Proveedor -->
      <div class="mb-4">
        <label for="direccion" class="block font-semibold flex items-center">
          <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
          Dirección del proveedor:
        </label>
        <textarea id="direccion" name="direccion" placeholder="Dirección del proveedor..." class="w-full border border-gray-300 rounded-lg p-2"></textarea>
        <small class="text-gray-500">Ejemplo: Av. Principal, Edificio X, Local Y.</small>
      </div>

      <!-- Botones -->
      <div class="flex justify-end mt-4">
        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition">
          <i class="fas fa-save mr-2"></i>Guardar proveedor
        </button>
      </div>
    </form>
  </div>
</div>
<script>
  function abrirVentanaEmergenteProveedor() {
    document.getElementById("modalProveedor").classList.remove("hidden");
  }

  function cerrarVentanaEmergenteProveedor() {
    document.getElementById("modalProveedor").classList.add("hidden");
  }

  window.addEventListener('click', function(e) {
  const modal = document.getElementById("modalProveedor");
  if (e.target === modal) {
    cerrarVentanaEmergenteProveedor();
  }
});

</script>
<script>
function cerrarVentanaEmergenteProveedor() {
    document.getElementById('modalProveedor').classList.add('hidden');
    document.getElementById('providerForm').reset();
}

function abrirVentanaEmergenteProveedor() {
    document.getElementById('modalProveedor').classList.remove('hidden');
}

// Enviar formulario por AJAX
document.getElementById("providerForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch("guardar_proveedor_producto.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            cerrarVentanaEmergenteProveedor();
            cargarProveedores(); // actualiza los <select> y tabla
        } else {
            alert("Error al guardar el proveedor.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Ocurrió un error al enviar los datos.");
    });
});

// Cargar proveedores dinámicamente en el select y la tabla
function cargarProveedores() {
    fetch("obtener_proveedores.php")
        .then(res => res.json())
        .then(data => {
            const selectUnico = document.getElementById("proveedorUnico");
            const tbody = document.getElementById("tbodyProveedores");

            // Limpiar anteriores
            selectUnico.innerHTML = `<option value="">Seleccionar proveedor</option>`;
            tbody.innerHTML = "";

            data.forEach(proveedor => {
                // Añadir a select único
                selectUnico.innerHTML += `
                    <option value="${proveedor.id_proveedor}">${proveedor.nombre_proveedor}</option>
                `;

                // Añadir a tabla múltiples
                tbody.innerHTML += `
                    <tr>
                        <td class="border px-4 py-2 text-center">
                            <input type="checkbox" name="proveedoresMultiples[]" value="${proveedor.id_proveedor}">
                        </td>
                        <td class="border px-4 py-2">${proveedor.nombre_proveedor}</td>
                    </tr>
                `;
            });
        });
}

// Cargar proveedores cuando cargue la página
window.addEventListener("DOMContentLoaded", cargarProveedores);
</script>



<script>
function mostrarOpcionesProveedor() {
    const valor = document.getElementById('vincularProveedor').value;
    const divUnico = document.getElementById('selectProveedor');
    const divMuchos = document.getElementById('tablaProveedores');
    const contenedor = document.getElementById('opcionesProveedor');

    contenedor.classList.remove('hidden');

    if (valor === 'uno') {
        divUnico.classList.remove('hidden');
        divMuchos.classList.add('hidden');
        actualizarSelectProveedor(); // ✅ aquí se carga
    } else if (valor === 'muchos') {
        divUnico.classList.add('hidden');
        divMuchos.classList.remove('hidden');
        actualizarTablaProveedores(); // ✅ aquí se carga
    } else {
        contenedor.classList.add('hidden');
        divUnico.classList.add('hidden');
        divMuchos.classList.add('hidden');
    }
}

function actualizarSelectProveedor() {
    fetch('obtener_proveedores.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('proveedorUnico');
            select.innerHTML = '<option value="">Seleccionar proveedor</option>';
            data.forEach(proveedor => {
                select.innerHTML += `<option value="${proveedor.id_proveedor}">${proveedor.nombre_proveedor}</option>`;
            });
        })
        .catch(error => console.error('Error cargando select:', error));
}

function actualizarTablaProveedores() {
    fetch('obtener_proveedores.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tablaProveedores tbody');
            tbody.innerHTML = '';
            data.forEach(proveedor => {
                const fila = `
                    <tr>
                        <td class='border border-gray-300 px-4 py-2 text-center'>
                            <input type='checkbox' name='proveedores[]' value='${proveedor.id_proveedor}'>
                        </td>
                        <td class='border border-gray-300 px-4 py-2'>${proveedor.nombre_proveedor}</td>
                    </tr>
                `;
                tbody.innerHTML += fila;
            });
        })
        .catch(error => console.error('Error cargando tabla:', error));
}
</script>
