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
$submenu_actual = 17;

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
<script>
      // Cargar modelos relacionados con la marca seleccionada
      function cargarModelos() {
            const idMarca = document.getElementById('marca').value;
            const modeloSelect = document.getElementById('modelo');

            fetch(`obtener_modelos_repuesto.php?id_marca=${idMarca}`)
                .then(response => response.json())
                .then(data => {
                    modeloSelect.innerHTML = '<option value="" disabled selected>Seleccione un modelo</option>';
                    data.forEach(modelo => {
                        modeloSelect.innerHTML += `<option value="${modelo.id_modelo}">${modelo.nombre_modelo}</option>`;
                    });
                })
                .catch(error => console.error('Error al cargar modelos:', error));
        }

    </script>
   <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
 <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
 <div class="flex flex-col items-center mb-6">
    <!-- Ícono de máquinas -->
    <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
    <i class="fas fa-concierge-bell text-3xl"></i> <!-- Ícono de servicio -->
    </div>
    <!-- Título del formulario -->
    <!-- Título del formulario -->
    <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Servicio</h2>
                <!-- Descripción del formulario -->
                <p class="text-gray-600 mt-2 text-center">Registra los servicios y sus especificaciones de forma organizada y profesional.</p>
</div>
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
    <!-- Contenedor global para mensajes de error -->
    <div id="mensaje-global" class="hidden bg-red-100 text-red-700 p-4 rounded-lg mb-4">
        <strong id="tipo-mensaje-global"></strong> <span id="texto-mensaje-global"></span>
    </div>
  
<?php
// Conexión a la BD con PDO
include("db_connection.php");

// Obtener datos
try {
    // Repuestos con marca, modelo y tipo
    $stmtRepuestos = $conn->prepare("
        SELECT 
            r.*, 
            m.nombre_marca AS marca, 
            mo.nombre_modelo AS modelo, 
            t.nombre_tipo AS tipo
        FROM repuesto r
        LEFT JOIN marca m ON r.id_marca = m.id_marca
        LEFT JOIN modelo mo ON r.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON r.id_tipo = t.id_tipo
        WHERE r.id_status = 1
    ");
    $stmtRepuestos->execute();
    $repuestos = $stmtRepuestos->fetchAll();

    $stmt = $conn->prepare("
    SELECT 
        m.*, 
        ma.nombre_marca AS marca, 
        mo.nombre_modelo AS modelo, 
        t.nombre_tipo AS tipo
    FROM maquina m
    LEFT JOIN marca ma ON m.id_marca = ma.id_marca
    LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN tipo t ON m.id_tipo = t.id_tipo
    WHERE m.id_status = 1
");
$stmt->execute();
$maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Productos con marca, modelo, tipo, clasificación y unidad de medida
$stmtProductos = $conn->prepare("
    SELECT 
        p.*, 
        m.nombre_marca AS nombre_marca, 
        mo.nombre_modelo AS nombre_modelo, 
        t.nombre_tipo AS nombre_tipo,
        c.nombre_clasificacion AS nombre_clasificacion,
        p.unidad_medida
    FROM producto p
    LEFT JOIN marca m ON p.id_marca = m.id_marca
    LEFT JOIN modelo mo ON p.id_modelo = mo.id_modelo
    LEFT JOIN tipo t ON p.id_tipo = t.id_tipo
    LEFT JOIN clasificacion c ON p.id_clasificacion = c.id_clasificacion
    WHERE p.id_status = 1
");
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);


    // Herramientas con marca, modelo y tipo
    $stmtHerramientas = $conn->prepare("
        SELECT 
            h.*, 
            m.nombre_marca AS marca, 
            mo.nombre_modelo AS modelo, 
            t.nombre_tipo AS tipo
        FROM herramientas h
        LEFT JOIN marca m ON h.id_marca = m.id_marca
        LEFT JOIN modelo mo ON h.id_modelo = mo.id_modelo
        LEFT JOIN tipo t ON h.id_tipo = t.id_tipo
        WHERE h.id_status = 1
    ");
    $stmtHerramientas->execute();
    $herramientas = $stmtHerramientas->fetchAll();

} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>


<form action="guardar_servicio.php" method="post" enctype="multipart/form-data" id="serviceForm" class="space-y-6">
    <!-- Nombre del Servicio -->
    <div>
        <label for="nombre_servicio" class="font-semibold flex items-center">
            <i class="fas fa-concierge-bell text-green-500 mr-2"></i>
            Nombre del servicio <span class="text-red-600">*</span>
        </label>
        <input type="text" name="nombre_servicio" id="nombre_servicio" required placeholder="Ej: Mantenimiento preventivo"
            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500">
    </div>

    <!-- Descripción -->
    <div>
        <label class="font-semibold flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-2"></i> Descripción del servicio
        </label>
        <input type="hidden" name="descripcion" id="descripcion">
        <div id="editor" class="border border-gray-300 p-2 min-h-[120px]"></div>
    </div>

    <!-- Tiempos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="tiempo_programado" class="font-semibold">Tiempo programado (en horas)</label>
            <input type="number" name="tiempo_programado" id="tiempo_programado" min="0" step="0.1"
                class="w-full border border-gray-300 rounded-md p-3">
        </div>
        <div>
            <label for="tiempo_paro_maquina" class="font-semibold">Tiempo de paro de máquina (en horas)</label>
            <input type="number" name="tiempo_paro_maquina" id="tiempo_paro_maquina" min="0" step="0.1"
                class="w-full border border-gray-300 rounded-md p-3">
        </div>
    </div>
    <!-- Selección de tipo de servicio -->
<div class="mb-6">
    <label for="tipo_servicio" class="block font-semibold mb-2">¿A qué desea realizar el servicio?</label>
    <select id="tipo_servicio" class="w-full border border-gray-300 rounded-md p-2" onchange="mostrarSeleccion()">
        <option value="">Seleccione una opción...</option>
        <option value="maquina">Servicio a una máquina</option>
        <option value="pieza">Servicio a un repuesto</option>
    </select>
</div>
<input type="hidden" name="maquina" id="maquinaSeleccionada">


<!-- Contenedor de máquina -->
<div id="bloque_maquina" class="hidden">
   
<!-- Máquina (tipo select visual con detalles) -->
<div class="relative">
    <label class="font-semibold block mb-2">Máquina involucrada</label>
    <input type="hidden" name="maquinas" id="maquinaSeleccionada">

    <!-- Campo visual tipo select -->
    <div id="maquinaToggle" class="w-full border border-gray-300 rounded-md p-3 cursor-pointer flex items-center justify-between">
        <span id="maquinaLabel" class="text-gray-600">Seleccione una máquina...</span>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </div>

    <!-- Lista desplegable -->
    <div id="maquinaList" class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-64 overflow-y-auto hidden shadow-lg">
        <?php foreach($maquinas as $m): ?>
            <div 
                class="flex items-center space-x-3 p-2 hover:bg-blue-100 cursor-pointer maquina-option" 
                data-id="<?= $m['id_maquina'] ?>" 
                data-nombre="<?= htmlspecialchars($m['nombre_maquina']) ?>"
                data-marca="<?= htmlspecialchars($m['marca'] ?? 'Sin marca') ?>"
                data-modelo="<?= htmlspecialchars($m['modelo'] ?? 'Sin modelo') ?>"
                data-tipo="<?= htmlspecialchars($m['tipo'] ?? 'Sin tipo') ?>"
                data-img="<?= htmlspecialchars($m['url'] ?? 'img/maquina_default.png') ?>"
            >
                <img src="<?= htmlspecialchars($m['url'] ?? 'img/maquina_default.png') ?>" class="w-12 h-12 object-contain rounded border">
                <div class="text-sm">
                    <div class="font-semibold"><?= htmlspecialchars($m['nombre_maquina']) ?></div>
                    <div class="text-gray-500 text-xs">Marca: <?= htmlspecialchars($m['marca'] ?? '---') ?> | Modelo: <?= htmlspecialchars($m['modelo'] ?? '---') ?> | Tipo: <?= htmlspecialchars($m['tipo'] ?? '---') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script -->
<script>
    const toggleMaq = document.getElementById('maquinaToggle');
    const listMaq = document.getElementById('maquinaList');
    const labelMaq = document.getElementById('maquinaLabel');
    const inputHiddenMaq = document.getElementById('maquinaSeleccionada');

    toggleMaq.addEventListener('click', () => {
        listMaq.classList.toggle('hidden');
    });

    document.querySelectorAll('.maquina-option').forEach(option => {
        option.addEventListener('click', () => {
            const id = option.dataset.id;
            const nombre = option.dataset.nombre;
            const marca = option.dataset.marca;
            const modelo = option.dataset.modelo;
            const tipo = option.dataset.tipo;
            const img = option.dataset.img;

            labelMaq.innerHTML = `
                <div class="flex items-center space-x-3">
                    <img src="${img}" class="w-8 h-8 object-contain rounded border">
                    <div class="text-sm">
                        <div class="font-semibold">${nombre}</div>
                        <div class="text-gray-500 text-xs">${marca} | ${modelo} | ${tipo}</div>
                    </div>
                </div>`;
            inputHiddenMaq.value = id;
            listMaq.classList.add('hidden');
        });
    });

    // Cierra si se hace clic fuera del select visual
    document.addEventListener('click', function(event) {
        if (!toggleMaq.contains(event.target) && !listMaq.contains(event.target)) {
            listMaq.classList.add('hidden');
        }
    });
</script>
</div>

<!-- Contenedor de pieza -->
<div id="bloque_pieza" class="hidden">
  <!-- PIEZA (tipo select visual con detalles) -->
<div class="relative z-10">
    <label class="font-semibold block mb-2">Repuesto al que se le hará el servicio (pieza)</label>
    <!-- Input oculto que se enviará por POST -->
    <input type="hidden" name="pieza" id="piezaSeleccionada">

    <!-- Campo visual tipo select -->
    <div id="piezaToggle" class="w-full border border-gray-300 rounded-md p-3 cursor-pointer flex items-center justify-between bg-white">
        <span id="piezaLabel" class="text-gray-600">Seleccione una pieza...</span>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </div>

    <!-- Lista desplegable -->
    <div id="piezaList" class="absolute z-20 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-64 overflow-y-auto hidden shadow-lg">
        <?php foreach($repuestos as $r): ?>
            <div 
                class="flex items-center space-x-3 p-2 hover:bg-blue-100 cursor-pointer pieza-option" 
                data-id="<?= $r['id_repuesto'] ?>" 
                data-nombre="<?= htmlspecialchars($r['nombre_repuesto']) ?>"
                data-marca="<?= htmlspecialchars($r['marca'] ?? 'Sin marca') ?>"
                data-modelo="<?= htmlspecialchars($r['modelo'] ?? 'Sin modelo') ?>"
                data-tipo="<?= htmlspecialchars($r['tipo'] ?? 'Sin tipo') ?>"
                data-img="<?= htmlspecialchars($r['url'] ?? 'img/repuesto_default.png') ?>"
            >
                <img src="<?= htmlspecialchars($r['url'] ?? 'img/repuesto_default.png') ?>" class="w-12 h-12 object-contain rounded border">
                <div class="text-sm">
                    <div class="font-semibold"><?= htmlspecialchars($r['nombre_repuesto']) ?></div>
                    <div class="text-gray-500">Marca: <?= htmlspecialchars($r['marca'] ?? '---') ?></div>
                    <div class="text-gray-500">Modelo: <?= htmlspecialchars($r['modelo'] ?? '---') ?></div>
                    <div class="text-gray-500">Tipo: <?= htmlspecialchars($r['tipo'] ?? '---') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script funcional -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('piezaToggle');
    const list = document.getElementById('piezaList');
    const label = document.getElementById('piezaLabel');
    const inputHidden = document.getElementById('piezaSeleccionada');

    toggle.addEventListener('click', () => {
        list.classList.toggle('hidden');
    });

    document.querySelectorAll('.pieza-option').forEach(option => {
        option.addEventListener('click', () => {
            const id = option.dataset.id;
            const nombre = option.dataset.nombre;
            const marca = option.dataset.marca;
            const modelo = option.dataset.modelo;
            const tipo = option.dataset.tipo;
            const img = option.dataset.img;

            // Actualiza la vista del "select"
            label.innerHTML = `
                <div class="flex items-center space-x-3">
                    <img src="${img}" class="w-8 h-8 object-contain rounded border">
                    <div class="text-sm">
                        <div class="font-semibold">${nombre}</div>
                        <div class="text-gray-500 text-xs">${marca} | ${modelo} | ${tipo}</div>
                    </div>
                </div>`;

            // Asigna el valor oculto
            inputHidden.value = id;
            list.classList.add('hidden');
        });
    });

    // Cierra si se hace clic fuera del select visual
    document.addEventListener('click', function(event) {
        if (!toggle.contains(event.target) && !list.contains(event.target)) {
            list.classList.add('hidden');
        }
    });
});
</script>

</div>

<script>
    function mostrarSeleccion() {
        const tipo = document.getElementById('tipo_servicio').value;
        const bloqueMaq = document.getElementById('bloque_maquina');
        const bloquePieza = document.getElementById('bloque_pieza');

        bloqueMaq.classList.add('hidden');
        bloquePieza.classList.add('hidden');

        if (tipo === 'maquina') {
            bloqueMaq.classList.remove('hidden');
        } else if (tipo === 'pieza') {
            bloquePieza.classList.remove('hidden');
        }
    }
</script>




  <!-- Repuestos que serán reemplazados (tipo select visual múltiple) -->
<div class="relative">
    <label class="font-semibold block mb-2">Repuestos que serán reemplazados</label>
    <div id="repuestosSeleccionados" class="space-y-2 mb-2"></div>
    <div id="repuestosToggle" class="w-full border border-gray-300 rounded-md p-3 cursor-pointer flex items-center justify-between">
        <span id="repuestosLabel" class="text-gray-600">Seleccione repuestos...</span>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </div>

    <!-- Lista desplegable -->
    <div id="repuestosList" class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-64 overflow-y-auto hidden shadow-lg">
        <?php foreach($repuestos as $r): ?>
            <div 
                class="flex items-center space-x-3 p-2 hover:bg-blue-100 cursor-pointer repuesto-option" 
                data-id="<?= $r['id_repuesto'] ?>" 
                data-nombre="<?= htmlspecialchars($r['nombre_repuesto']) ?>"
                data-marca="<?= htmlspecialchars($r['marca'] ?? 'Sin marca') ?>"
                data-modelo="<?= htmlspecialchars($r['modelo'] ?? 'Sin modelo') ?>"
                data-tipo="<?= htmlspecialchars($r['tipo'] ?? 'Sin tipo') ?>"
                data-img="<?= htmlspecialchars($r['url'] ?? 'img/repuesto_default.png') ?>"
            >
                <img src="<?= htmlspecialchars($r['url'] ?? 'img/repuesto_default.png') ?>" class="w-12 h-12 object-contain rounded border">
                <div class="text-sm">
                    <div class="font-semibold"><?= htmlspecialchars($r['nombre_repuesto']) ?></div>
                    <div class="text-gray-500">Marca: <?= htmlspecialchars($r['marca'] ?? '---') ?></div>
                    <div class="text-gray-500">Modelo: <?= htmlspecialchars($r['modelo'] ?? '---') ?></div>
                    <div class="text-gray-500">Tipo: <?= htmlspecialchars($r['tipo'] ?? '---') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script -->
<script>
    const repuestosToggle = document.getElementById('repuestosToggle');
    const repuestosList = document.getElementById('repuestosList');
    const repuestosSeleccionados = document.getElementById('repuestosSeleccionados');
    const repuestosLabel = document.getElementById('repuestosLabel');
    const repuestoIds = new Set();

    repuestosToggle.addEventListener('click', () => {
        repuestosList.classList.toggle('hidden');
    });

    document.querySelectorAll('.repuesto-option').forEach(option => {
        option.addEventListener('click', () => {
            const id = option.dataset.id;
            if (repuestoIds.has(id)) return;

            repuestoIds.add(id);
            const nombre = option.dataset.nombre;
            const marca = option.dataset.marca;
            const modelo = option.dataset.modelo;
            const tipo = option.dataset.tipo;
            const img = option.dataset.img;

            const item = document.createElement('div');
            item.className = 'flex items-center space-x-3 border rounded p-2';
            item.innerHTML = `
                <input type="hidden" name="repuestos[${id}][id]" value="${id}">
                <img src="${img}" class="w-10 h-10 object-contain rounded border">
                <div class="text-sm flex-grow">
                    <div class="font-semibold">${nombre}</div>
                    <div class="text-gray-500 text-xs">${marca} | ${modelo} | ${tipo}</div>
                </div>
                <input type="number" name="repuestos[${id}][cantidad]" min="1" placeholder="Cantidad"
                    class="border border-gray-300 rounded p-1 w-20 text-sm">
                <button type="button" onclick="this.parentElement.remove(); repuestoIds.delete('${id}')"
                    class="text-red-500 text-sm ml-2 hover:text-red-700">Quitar</button>
            `;
            repuestosSeleccionados.appendChild(item);
            repuestosList.classList.add('hidden');
        });
    });

    document.addEventListener('click', function(event) {
        if (!repuestosToggle.contains(event.target) && !repuestosList.contains(event.target)) {
            repuestosList.classList.add('hidden');
        }
    });
</script>

<!-- Productos a utilizar -->
<div class="relative">
    <label class="font-semibold block mb-2">Productos que se utilizarán</label>
    <div id="productosSeleccionados" class="space-y-2 mb-2"></div>

    <!-- Caja de activación del desplegable -->
    <div id="productosToggle" class="w-full border border-gray-300 rounded-md p-3 cursor-pointer flex items-center justify-between">
        <span id="productosLabel" class="text-gray-600">Seleccione productos...</span>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </div>

    <!-- Lista desplegable -->
    <div id="productosList" class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-64 overflow-y-auto hidden shadow-lg">
        <?php foreach($productos as $p): ?>
            <div 
                class="flex items-center space-x-3 p-2 hover:bg-blue-100 cursor-pointer producto-option" 
                data-id="<?= $p['id_producto'] ?>" 
                data-nombre="<?= htmlspecialchars($p['nombre_producto']) ?>"
                data-marca="<?= htmlspecialchars($p['nombre_marca']) ?>"
                data-modelo="<?= htmlspecialchars($p['nombre_modelo']) ?>"
                data-tipo="<?= htmlspecialchars($p['nombre_tipo']) ?>"
                data-clasificacion="<?= htmlspecialchars($p['nombre_clasificacion']) ?>"
                data-unidad="<?= htmlspecialchars($p['unidad_medida']) ?>"
                data-img="<?= htmlspecialchars($p['url'] ?? 'img/producto_default.png') ?>"
            >
                <img src="<?= htmlspecialchars($p['url'] ?? 'img/producto_default.png') ?>" class="w-12 h-12 object-contain rounded border">
                <div class="text-sm">
                    <div class="font-semibold"><?= htmlspecialchars($p['nombre_producto']) ?></div>
                    <div class="text-gray-500 text-xs">
                        <?= htmlspecialchars($p['nombre_marca']) ?> | <?= htmlspecialchars($p['nombre_modelo']) ?> | <?= htmlspecialchars($p['nombre_tipo']) ?>
                    </div>
                    <div class="text-gray-500 text-xs">
                        Clasificación: <?= htmlspecialchars($p['nombre_clasificacion']) ?> | Unidad: <?= htmlspecialchars($p['unidad_medida']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script -->
<script>
    const productosToggle = document.getElementById('productosToggle');
    const productosList = document.getElementById('productosList');
    const productosSeleccionados = document.getElementById('productosSeleccionados');
    const productoIds = new Set();

    productosToggle.addEventListener('click', () => {
        productosList.classList.toggle('hidden');
    });

    document.querySelectorAll('.producto-option').forEach(option => {
        option.addEventListener('click', () => {
            const id = option.dataset.id;
            if (productoIds.has(id)) return;

            productoIds.add(id);
            const nombre = option.dataset.nombre;
            const marca = option.dataset.marca;
            const modelo = option.dataset.modelo;
            const tipo = option.dataset.tipo;
            const clasificacion = option.dataset.clasificacion;
            const unidad = option.dataset.unidad;
            const img = option.dataset.img;

            const item = document.createElement('div');
            item.className = 'flex items-center space-x-3 border rounded p-2';
            item.innerHTML = `
                <input type="hidden" name="productos[${id}][id]" value="${id}">
                <img src="${img}" class="w-10 h-10 object-contain rounded border">
                <div class="text-sm flex-grow">
                    <div class="font-semibold">${nombre}</div>
                    <div class="text-gray-500 text-xs">${marca} | ${modelo} | ${tipo}</div>
                    <div class="text-gray-500 text-xs">${clasificacion} ${unidad}</div>
                </div>
                <input type="number" name="productos[${id}][cantidad]" min="1" placeholder="Cantidad"
                    class="border border-gray-300 rounded p-1 w-20 text-sm">
                <button type="button" onclick="this.parentElement.remove(); productoIds.delete('${id}')"
                    class="text-red-500 text-sm ml-2 hover:text-red-700">Quitar</button>
            `;
            productosSeleccionados.appendChild(item);
            productosList.classList.add('hidden');
        });
    });

    document.addEventListener('click', function(event) {
        if (!productosToggle.contains(event.target) && !productosList.contains(event.target)) {
            productosList.classList.add('hidden');
        }
    });
</script>

  
   <!-- Herramientas a utilizar (tipo select visual múltiple) -->
<div class="relative">
    <label class="font-semibold block mb-2">Herramientas que se utilizarán</label>
    <div id="herramientasSeleccionadas" class="space-y-2 mb-2"></div>
    <div id="herramientasToggle" class="w-full border border-gray-300 rounded-md p-3 cursor-pointer flex items-center justify-between">
        <span id="herramientasLabel" class="text-gray-600">Seleccione herramientas...</span>
        <i class="fas fa-chevron-down text-gray-500"></i>
    </div>

    <!-- Lista desplegable -->
    <div id="herramientasList" class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 w-full max-h-64 overflow-y-auto hidden shadow-lg">
        <?php foreach($herramientas as $h): ?>
            <div 
                class="flex items-center space-x-3 p-2 hover:bg-blue-100 cursor-pointer herramienta-option" 
                data-id="<?= $h['id_herramienta'] ?>" 
                data-nombre="<?= htmlspecialchars($h['nombre_herramienta']) ?>"
                data-marca="<?= htmlspecialchars($h['marca'] ?? 'Sin marca') ?>"
                data-modelo="<?= htmlspecialchars($h['modelo'] ?? 'Sin modelo') ?>"
                data-tipo="<?= htmlspecialchars($h['tipo'] ?? 'Sin tipo') ?>"
                data-img="<?= htmlspecialchars($h['url'] ?? 'img/herramienta_default.png') ?>"
            >
                <img src="<?= htmlspecialchars($h['url'] ?? 'img/herramienta_default.png') ?>" class="w-12 h-12 object-contain rounded border">
                <div class="text-sm">
                    <div class="font-semibold"><?= htmlspecialchars($h['nombre_herramienta']) ?></div>
                    <div class="text-gray-500">Marca: <?= htmlspecialchars($h['marca'] ?? '---') ?></div>
                    <div class="text-gray-500">Modelo: <?= htmlspecialchars($h['modelo'] ?? '---') ?></div>
                    <div class="text-gray-500">Tipo: <?= htmlspecialchars($h['tipo'] ?? '---') ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script -->
<script>
    const herramientasToggle = document.getElementById('herramientasToggle');
    const herramientasList = document.getElementById('herramientasList');
    const herramientasSeleccionadas = document.getElementById('herramientasSeleccionadas');
    const herramientaIds = new Set();

    herramientasToggle.addEventListener('click', () => {
        herramientasList.classList.toggle('hidden');
    });

    document.querySelectorAll('.herramienta-option').forEach(option => {
        option.addEventListener('click', () => {
            const id = option.dataset.id;
            if (herramientaIds.has(id)) return;

            herramientaIds.add(id);
            const nombre = option.dataset.nombre;
            const marca = option.dataset.marca;
            const modelo = option.dataset.modelo;
            const tipo = option.dataset.tipo;
            const img = option.dataset.img;

            const item = document.createElement('div');
            item.className = 'flex items-center space-x-3 border rounded p-2';
            item.innerHTML = `
                <input type="hidden" name="herramientas[${id}][id]" value="${id}">
                <img src="${img}" class="w-10 h-10 object-contain rounded border">
                <div class="text-sm flex-grow">
                    <div class="font-semibold">${nombre}</div>
                    <div class="text-gray-500 text-xs">${marca} | ${modelo} | ${tipo}</div>
                </div>
                <input type="number" name="herramientas[${id}][cantidad]" min="1" placeholder="Cantidad"
                    class="border border-gray-300 rounded p-1 w-20 text-sm">
                <button type="button" onclick="this.parentElement.remove(); herramientaIds.delete('${id}')"
                    class="text-red-500 text-sm ml-2 hover:text-red-700">Quitar</button>
            `;
            herramientasSeleccionadas.appendChild(item);
            herramientasList.classList.add('hidden');
        });
    });

    document.addEventListener('click', function(event) {
        if (!herramientasToggle.contains(event.target) && !herramientasList.contains(event.target)) {
            herramientasList.classList.add('hidden');
        }
    });
</script>
<!-- ¿Vincular a proveedores? -->
<div class="mt-6">
    <label class="font-semibold block mb-2">¿Desea vincularlo a uno o varios proveedores?</label>
    <select id="vincularProveedor" class="w-full border border-gray-300 rounded p-2">
        <option value="no">No</option>
        <option value="si">Sí</option>
    </select>
</div>

<!-- Selector de proveedores (oculto por defecto) -->
<div id="contenedorProveedores" class="mt-4 hidden">
    <label class="font-semibold block mb-2">Seleccionar proveedores (máx ilimitado)</label>

    <!-- Buscador -->
    <input type="text" id="buscarProveedor" placeholder="Buscar proveedor..." class="w-full p-2 border border-gray-300 rounded mb-2">

    <!-- Lista paginada -->
    <div id="listaProveedores" class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded p-2">
        <?php
        $stmt = $conn->query("SELECT * FROM proveedor ORDER BY nombre_proveedor ASC");
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($proveedores as $prov):
        ?>
            <div class="proveedor-item flex justify-between items-center bg-white p-2 rounded border cursor-pointer" data-nombre="<?= strtolower($prov['nombre_proveedor']) ?>" data-id="<?= $prov['id_proveedor'] ?>">
                <span class="font-medium"><?= htmlspecialchars($prov['nombre_proveedor']) ?></span>
                <span class="check hidden text-green-600 font-bold">✔️</span>
                <input type="checkbox" name="proveedor_id[]" value="<?= $prov['id_proveedor'] ?>" class="hidden">
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <div id="paginacionProveedores" class="flex justify-center space-x-2 mt-2"></div>
</div>
<script>
    const toggleSelect = document.getElementById('vincularProveedor');
    const contenedor = document.getElementById('contenedorProveedores');
    const buscador = document.getElementById('buscarProveedor');
    const lista = document.getElementById('listaProveedores');
    const paginacion = document.getElementById('paginacionProveedores');

    const itemsPorPagina = 5;
    let paginaActual = 1;
    let proveedoresFiltrados = [];

    // Mostrar/Ocultar selector
    toggleSelect.addEventListener('change', () => {
        if (toggleSelect.value === 'si') {
            contenedor.classList.remove('hidden');
            filtrar();
        } else {
            contenedor.classList.add('hidden');
        }
    });

    // Selección múltiple visual
    lista.addEventListener('click', (e) => {
        const item = e.target.closest('.proveedor-item');
        if (!item) return;

        const checkbox = item.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        actualizarSeleccionVisual(item, checkbox.checked);
    });

    function actualizarSeleccionVisual(item, seleccionado) {
        const check = item.querySelector('.check');
        if (seleccionado) {
            item.classList.add('bg-blue-100', 'border-blue-400');
            check.classList.remove('hidden');
        } else {
            item.classList.remove('bg-blue-100', 'border-blue-400');
            check.classList.add('hidden');
        }
    }

    // Búsqueda + paginación
    function filtrar() {
        const texto = buscador.value.toLowerCase();
        proveedoresFiltrados = Array.from(lista.querySelectorAll('.proveedor-item'))
            .filter(item => item.dataset.nombre.includes(texto));

        paginaActual = 1;
        actualizarVista();
    }

    buscador.addEventListener('input', filtrar);

    function actualizarVista() {
        const items = Array.from(lista.querySelectorAll('.proveedor-item'));
        items.forEach(item => item.classList.add('hidden'));

        const visibles = proveedoresFiltrados.slice((paginaActual - 1) * itemsPorPagina, paginaActual * itemsPorPagina);
        visibles.forEach(item => item.classList.remove('hidden'));

        // Paginación
        const totalPaginas = Math.ceil(proveedoresFiltrados.length / itemsPorPagina);
        paginacion.innerHTML = '';

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded ${i === paginaActual ? 'bg-blue-500 text-white' : 'bg-white text-blue-600'}`;
            btn.addEventListener('click', () => {
                paginaActual = i;
                actualizarVista();
            });
            paginacion.appendChild(btn);
        }
    }

    // Inicialización
    window.addEventListener('DOMContentLoaded', () => {
        filtrar();

        // Aplicar visuales a checkboxes marcados (al recargar con datos previos)
        lista.querySelectorAll('.proveedor-item').forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox.checked) {
                actualizarSeleccionVisual(item, true);
            }
        });
    });
</script>


    <!-- Botones -->
    <div class="flex justify-between mt-6">
        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-all">
            <i class="fas fa-save mr-2"></i> Guardar Servicio
        </button>
        <a href="servicio.php" class="bg-gray-400 text-white px-6 py-3 rounded-lg hover:bg-gray-500 transition-all">
            <i class="fas fa-arrow-left mr-2"></i> Volver
        </a>
    </div>
</form>

<!-- Quill & Script -->
<link href="css/quill.snow.css" rel="stylesheet">
<script src="js/quill.min.js"></script>
<script>
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']]
        }
    });

    document.getElementById('serviceForm').addEventListener('submit', () => {
        document.getElementById('descripcion').value = quill.root.innerHTML;
    });
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

