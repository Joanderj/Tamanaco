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

// Menú actual (empresa.php -> id_menu = 2)
$menu_actual = 2;

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
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 2
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
$cedula = isset($_GET['cedula']) ? $_GET['cedula'] : '';
$nacionalidad = isset($_GET['nacionalidad']) ? $_GET['nacionalidad'] : '';
$primerNombre = isset($_GET['primer_nombre']) ? $_GET['primer_nombre'] : '';
$primerApellido = isset($_GET['primer_apellido']) ? $_GET['primer_apellido'] : '';
$correoElectronico = isset($_GET['correo_electronico']) ? $_GET['correo_electronico'] : '';
$telefono = isset($_GET['telefono']) ? $_GET['telefono'] : '';
$genero = isset($_GET['genero']) ? $_GET['genero'] : '';
$pais = isset($_GET['pais_id']) ? (int)$_GET['pais_id'] : '';
$estado = isset($_GET['estado_id']) ? (int)$_GET['estado_id'] : '';
$cargo = isset($_GET['id_cargo']) ? (int)$_GET['id_cargo'] : '';
$status = isset($_GET['id_status']) ? (int)$_GET['id_status'] : '';
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFinal = isset($_GET['fecha_final']) ? $_GET['fecha_final'] : '';

// Clasificación y ordenamiento
$orderBy = 'p.id_persona'; // Usar alias para evitar conflictos
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'nombre_asc':
            $orderBy = 'p.primer_nombre ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'p.primer_nombre DESC';
            break;
        case 'apellido_asc':
            $orderBy = 'p.primer_apellido ASC';
            break;
        case 'apellido_desc':
            $orderBy = 'p.primer_apellido DESC';
            break;
        case 'cedula_asc':
            $orderBy = 'p.cedula ASC';
            break;
        case 'cedula_desc':
            $orderBy = 'p.cedula DESC';
            break;
    }
}

// Consulta total de elementos
$totalQuery = "SELECT COUNT(*) FROM personas AS p LEFT JOIN cargo AS c ON p.id_cargo = c.id_cargo WHERE 1=1";
$params = [];

if (!empty($cedula)) {
    $totalQuery .= " AND p.cedula LIKE ?";
    $params[] = '%' . $cedula . '%';
}

if (!empty($nacionalidad)) {
    $totalQuery .= " AND p.nacionalidad = ?";
    $params[] = $nacionalidad;
}

if (!empty($primerNombre)) {
    $totalQuery .= " AND p.primer_nombre LIKE ?";
    $params[] = '%' . $primerNombre . '%';
}

if (!empty($primerApellido)) {
    $totalQuery .= " AND p.primer_apellido LIKE ?";
    $params[] = '%' . $primerApellido . '%';
}

if (!empty($correoElectronico)) {
    $totalQuery .= " AND p.correo_electronico LIKE ?";
    $params[] = '%' . $correoElectronico . '%';
}

if (!empty($telefono)) {
    $totalQuery .= " AND p.telefono LIKE ?";
    $params[] = '%' . $telefono . '%';
}

if (!empty($genero)) {
    $totalQuery .= " AND p.genero = ?";
    $params[] = $genero;
}

if (!empty($pais)) {
    $totalQuery .= " AND p.pais_id = ?";
    $params[] = $pais;
}

if (!empty($estado)) {
    $totalQuery .= " AND p.estado_id = ?";
    $params[] = $estado;
}

if (!empty($cargo)) {
    $totalQuery .= " AND p.id_cargo = ?";
    $params[] = $cargo;
}

if (!empty($status)) {
    $totalQuery .= " AND p.id_status = ?";
    $params[] = $status;
}

if (!empty($fechaInicio)) {
    $totalQuery .= " AND p.fecha_nacimiento >= ?";
    $params[] = $fechaInicio;
}

if (!empty($fechaFinal)) {
    $totalQuery .= " AND p.fecha_nacimiento <= ?";
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

// Consulta para obtener datos de personas junto con el nombre del cargo
$query = "
    SELECT 
    p.id_persona,
        p.cedula,
        p.nacionalidad,
        p.primer_nombre,
        p.primer_apellido,
        p.correo_electronico,
        p.telefono,
        p.fecha_nacimiento,
        p.genero,
        c.nombre_cargo
    FROM 
        personas AS p
    LEFT JOIN 
        cargo AS c ON p.id_cargo = c.id_cargo
    WHERE 
        1=1
";

// Agregar filtros a la consulta
if (!empty($cedula)) {
    $query .= " AND p.cedula LIKE ?";
}
if (!empty($nacionalidad)) {
    $query .= " AND p.nacionalidad = ?";
}
if (!empty($primerNombre)) {
    $query .= " AND p.primer_nombre LIKE ?";
}
if (!empty($primerApellido)) {
    $query .= " AND p.primer_apellido LIKE ?";
}
if (!empty($correoElectronico)) {
    $query .= " AND p.correo_electronico LIKE ?";
}
if (!empty($telefono)) {
    $query .= " AND p.telefono LIKE ?";
}
if (!empty($genero)) {
    $query .= " AND p.genero = ?";
}
if (!empty($pais)) {
    $query .= " AND p.pais_id = ?";
}
if (!empty($estado)) {
    $query .= " AND p.estado_id = ?";
}
if (!empty($cargo)) {
    $query .= " AND p.id_cargo = ?";
}
if (!empty($status)) {
    $query .= " AND p.id_status = ?";
}
if (!empty($fechaInicio)) {
    $query .= " AND p.fecha_nacimiento >= ?";
}
if (!empty($fechaFinal)) {
    $query .= " AND p.fecha_nacimiento <= ?";
}

// Agregar orden y límites a la consulta
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
$personas = $result->fetch_all(MYSQLI_ASSOC);

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
          <i class="fa fa-user mr-2"></i> Sede
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-box mr-2"></i> Sucursal
        </a>
        <hr class="border-gray-300 my-2">

        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-tools mr-2"></i> Almacen
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-chart-bar mr-2"></i> Planta
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-chart-bar mr-2"></i> Articulo
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-chart-bar mr-2"></i> Permisos
        </a>
      </nav>
</div>
<hr>

<!-- Contenedor principal -->
<div class="p-6 bg-gray-50 rounded shadow-md">

<div id="modalPersona" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden justify-center items-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-lg overflow-hidden">
        <!-- Encabezado del Modal -->
        <div class="px-6 py-4 flex justify-between items-center bg-indigo-500 text-white">
            <h2 class="text-xl font-semibold">Detalles de la Persona</h2>
            <button 
                onclick="closeModal()" 
                class="text-white hover:text-red-300 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Contenido del Modal -->
        <div class="px-6 py-4 space-y-4">
            <p><strong>Cédula:</strong> <span id="personaCedula"></span></p>
            <p><strong>Nacionalidad:</strong> <span id="personaNacionalidad"></span></p>
            <p><strong>Nombre:</strong> <span id="personaNombre"></span></p>
            <p><strong>Apellido:</strong> <span id="personaApellido"></span></p>
            <p><strong>Correo Electrónico:</strong> <span id="personaCorreo"></span></p>
            <p><strong>Teléfono:</strong> <span id="personaTelefono"></span></p>
            <p><strong>Fecha de Nacimiento:</strong> <span id="personaFechaNacimiento"></span></p>
            <p><strong>Género:</strong> <span id="personaGenero"></span></p>
            <p><strong>Dirección:</strong> <span id="personaDireccion"></span></p>
            <p><strong>Cargo:</strong> <span id="personaCargo"></span></p>
            <p><strong>Status:</strong> <span id="personaStatus"></span></p>
            <p><strong>País:</strong> <span id="personaPais"></span></p>
            <p><strong>Estado:</strong> <span id="personaEstado"></span></p>
        </div>
        
        <!-- Pie del Modal -->
        <div class="px-6 py-4 bg-gray-100 flex justify-end">
            <button 
                onclick="closeModal()" 
                class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 focus:outline-none">
                Cerrar
            </button>
        </div>
    </div>
</div>
<!-- Contenedor para los botones superiores -->
<!-- Contenedor Principal -->
          <!-- Contenedor Principal -->
          <div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col items-center mb-6">
            <!-- Ícono de empleado -->
            <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-user-tie text-3xl"></i> <!-- Ícono de empleado -->
            </div>
            <!-- Título del formulario -->
            <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Empleado</h2>
            <!-- Descripción del formulario -->
            <p class="text-gray-600 mt-2 text-center">Registra la información de los empleados y administra su cargo de manera eficiente.</p>
        </div>

        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Leer el mensaje de error desde la sesión
        $error_message = isset($_SESSION['mensaje_error']) ? $_SESSION['mensaje_error'] : "";
        unset($_SESSION['mensaje_error']); // Limpiar el mensaje de error después de mostrarlo
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
        <form action="guardar_empleado.php" method="post" id="formEmpleado">
<!-- Datos de Importancia -->
<div class="border-b-2 border-red-500 shadow-md mb-4 pb-2">
    <label for="datos_importancia" class="block text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-id-card text-red-500 text-3xl mr-3"></i> Datos de Importancia
    </label>
</div>
        
      <!-- Nacionalidad -->
<div class="mb-6">
    <label class="block font-semibold text-lg flex items-center">
        <i class="fas fa-flag text-blue-500 mr-2"></i> Nacionalidad: <span class="text-red-600">*</span>
    </label>
    <div class="flex items-center space-x-6 mt-2">
        <label class="flex items-center space-x-2 cursor-pointer">
            <input type="radio" name="nacionalidad" value="V" class="form-radio text-blue-500" required onchange="habilitarCedula()">
            <span class="text-gray-700">Venezolana</span>
        </label>
        <label class="flex items-center space-x-2 cursor-pointer">
            <input type="radio" name="nacionalidad" value="E" class="form-radio text-pink-500" required onchange="habilitarCedula()">
            <span class="text-gray-700">Extranjera</span>
        </label>
    </div>
</div>

<!-- Cédula -->
<div class="mb-6">
    <label for="cedula" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-id-card text-green-500 mr-2"></i> Cédula: <span class="text-red-600">*</span>
    </label>
    <input type="text" id="cedula" name="cedula" placeholder="Ingrese su cédula" 
           class="w-full border border-gray-300 rounded-lg p-3 bg-gray-100 cursor-not-allowed" disabled required onkeyup="validarCedula()">
    <small id="mensaje-error-input" class="text-red-500 hidden">Esta cédula ya existe</small>
    <small id="mensaje-exito-input" class="text-green-500 hidden">¡Esta cédula está disponible!</small>
    <small class="text-gray-500">Ejemplo: 12345678</small>
</div>
<!-- Información Personal -->
<div class="border-b-2 border-green-500 shadow-md mb-4 pb-2">
    <label for="info_personal" class="block text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-user text-green-500 text-3xl mr-3"></i> Información Personal
    </label>
</div>

<!-- Nombre y Apellido -->
<div class="grid grid-cols-2 gap-6">
    <div>
        <label for="nombre" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user text-teal-500 mr-2"></i> Primer Nombre: <span class="text-red-600">*</span>
        </label>
        <input oninput="this.value = this.value.toUpperCase();" type="text" id="nombre" name="nombre" placeholder="Primer nombre" class="w-full border border-gray-300 rounded-lg p-2" required>
        <small class="text-gray-500">Ejemplo: Juan</small>
    </div>
    <div>
        <label for="segundo_nombre" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user text-teal-500 mr-2"></i> Segundo Nombre:
        </label>
        <input oninput="this.value = this.value.toUpperCase();" type="text" id="segundo_nombre" name="segundo_nombre" placeholder="Segundo nombre" class="w-full border border-gray-300 rounded-lg p-2">
        <small class="text-gray-500">Ejemplo: Carlos</small>
    </div>
</div>

<div class="grid grid-cols-2 gap-6 mt-4">
    <div>
        <label for="apellido" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user-tag text-purple-500 mr-2"></i> Primer Apellido: <span class="text-red-600">*</span>
        </label>
        <input oninput="this.value = this.value.toUpperCase();" type="text" id="apellido" name="apellido" placeholder="Primer apellido" class="w-full border border-gray-300 rounded-lg p-2" required>
        <small class="text-gray-500">Ejemplo: Pérez</small>
    </div>
    <div>
        <label for="segundo_apellido" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user-tag text-purple-500 mr-2"></i> Segundo Apellido:
        </label>
        <input oninput="this.value = this.value.toUpperCase();" type="text" id="segundo_apellido" name="segundo_apellido" placeholder="Segundo apellido" class="w-full border border-gray-300 rounded-lg p-2">
        <small class="text-gray-500">Ejemplo: Gómez</small>
    </div>
</div>
    

<!-- Fecha de nacimiento y edad -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <!-- Fecha de Nacimiento -->
    <div>
        <label for="fecha_nacimiento" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Fecha de Nacimiento: <span class="text-red-600">*</span>
        </label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
               class="w-full border border-gray-300 rounded-lg p-3" required onchange="calcularEdad()">
        <small id="error-fecha" class="text-red-500"></small>
        <small class="text-gray-500 block mt-1">Ejemplo: 1990-05-10</small>
    </div>

    <!-- Edad -->
    <div>
        <label for="edad" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user-clock text-green-500 mr-2"></i> Edad: <span class="text-red-600">*</span>
        </label>
        <input type="number" id="edad" name="edad" placeholder="Edad" 
               class="w-full border border-gray-300 rounded-lg p-3 bg-gray-200 cursor-not-allowed" disabled required>
        <small class="text-gray-500 block mt-1">Se calculará automáticamente</small>
        <small id="error-edad" class="text-red-500"></small>
    </div>
</div>

<!-- Mensaje central -->
<div id="mensaje-central" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 flex items-center space-x-4 max-w-md mx-auto">
        <div class="text-red-500 text-3xl">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div>
            <p id="mensaje-texto" class="text-gray-800 font-semibold"></p>
        </div>
        <button id="cerrar-mensaje" class="text-gray-500 hover:text-gray-700" onclick="cerrarMensaje()">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>
</div>

           <!-- Género y Rol -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <!-- Género -->
    <div>
        <label for="genero" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-venus-mars text-pink-500 mr-2"></i> Género: <span class="text-red-600">*</span>
        </label>
        <select id="genero" name="genero" class="w-full border border-gray-300 rounded-lg p-3 bg-white" required>
            <option value="">Seleccionar</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
        </select>
        <small class="text-gray-500 block mt-1">Ejemplo: Masculino</small>
    </div>

    <!-- Cargo -->
<div>
    <label for="cargo" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-briefcase text-blue-500 mr-2"></i> Cargo: <span class="text-red-600">*</span>
    </label>
    <select id="cargo" name="cargo" class="w-full border border-gray-300 rounded-lg p-3 bg-white" required>
        <option value="">Seleccionar</option>
        <?php
        // Conexión a la base de datos
        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }
        $query = "SELECT * FROM cargo";
        $resultado = $conexion->query($query);
        while ($fila = $resultado->fetch_assoc()) {
            echo "<option value='" . $fila['id_cargo'] . "'>" . $fila['nombre_cargo'] . "</option>";
        }
        ?>
    </select>
    <small class="text-gray-500 block mt-1">Ejemplo: Mecánico</small>
</div>
</div>
<!-- Contacto -->
<div class="border-b-2 border-yellow-500 shadow-md mb-4 pb-2">
    <label for="contacto" class="block text-2xl font-bold text-gray-800 flex items-center">
        <i class="fas fa-phone text-yellow-500 text-3xl mr-3"></i> Datos de Contacto
    </label>
</div>

<!-- Teléfono y Correo Electrónico -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <!-- Teléfono -->
    <div class="w-[410px]">
    <label for="telefono" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-phone-alt text-green-500 mr-2"></i> Teléfono: <span class="text-red-600">*</span>
    </label>
    <input style="width: 410px;" type="tel" id="telefono" name="telefono" placeholder="+58 4149551156"
           class="border border-gray-300 rounded-lg p-3 w-[410px] min-w-[410px]" required>
          
    <small class="text-gray-500 block mt-1">Ejemplo: +58 4149551156</small>
</div>

    <!-- Correo Electrónico -->
    <div>
        <label for="email" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-envelope text-blue-500 mr-2"></i> Correo Electrónico: <span class="text-red-600">*</span>
        </label>
        <input oninput="this.value = this.value.toUpperCase();" type="email" id="email" name="email" placeholder="tamanaco@gmail.com"
               class="w-full border border-gray-300 rounded-lg p-3" required>
        <small class="text-gray-500 block mt-1">Ejemplo: tamanaco@gmail.com</small>
        <small id="mensaje-validar_correo-input" class="text-red-500 hidden">Este correo no esta permitido</small>
        <small id="mensaje-error_correo-input" class="text-red-500 hidden">Este correo ya existe</small>
        <small id="mensaje-exito_correo-input" class="text-green-500 hidden">¡Este correo está disponible!</small>
    </div>
</div>
<!-- Dirección de Habitación -->
<div class="mb-6">
<div class="border-b-2 border-blue-500 shadow-md mb-4 pb-2">
        <label for="direccion" class="block text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-map-marker-alt text-blue-500 text-3xl mr-3"></i> Dirección de Habitación
        </label>
    </div>


    <div class="grid grid-cols-2 gap-6 mt-2">
        <!-- País -->
        <div>
            <label for="pais" class="block font-semibold flex items-center">
                <i class="fas fa-globe text-green-500 mr-2"></i> País: <span class="text-red-600">*</span>
            </label>
            <select id="pais" name="pais" class="w-full border border-gray-300 rounded-lg p-3 bg-white" required>
            <option value="">Seleccionar</option>
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

            <small class="text-gray-500 block mt-1">Ejemplo: Venezuela</small>
        </div>

        <!-- Estado -->
        <div>
            <label for="estado" class="block font-semibold flex items-center">
                <i class="fas fa-location-dot text-red-500 mr-2"></i> Estado: <span class="text-red-600">*</span>
            </label>
            <select id="estado" name="estado" class="w-full border border-gray-300 rounded-lg p-3 bg-white" required>
            <option value="">Seleccionar</option>
        </select>

            <small class="text-gray-500 block mt-1">Ejemplo: Estado Portuguesa</small>
        </div>
    </div>
</div>

<!-- Descripción de Ubicación -->
<div class="mb-6">
    <label for="descripcion" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-map-marked-alt text-blue-500 mr-2"></i> Descripción de Ubicación: <span class="text-red-600">*</span>
    </label>
    <textarea oninput="this.value = this.value.toUpperCase();" id="descripcion" name="descripcion" rows="4" 
              class="w-full border border-gray-300 rounded-lg p-3 resize-none bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              placeholder="Ingrese detalles de su ubicación, como calle, edificio, referencias..."></textarea>
    <small class="text-gray-500 block mt-1">Ejemplo: Calle 5, Edificio Tamanaco, Oficina 301</small>
</div>
 <!-- Nota sobre campos obligatorios --> 
 <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>
          
            <!-- Botones -->
            <div class="flex justify-between mt-4 space-x-4">
                <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg hover:bg-green-600 transition-all duration-300">
                    <i class="fas fa-save mr-2"></i> Guardar Empleado
                </button>
                <button type="button" onclick="location.href='empleado.php';" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600 transition-all duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Regresar
                </button>
            </div>
        </form>
    </div>
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



<!-- Librería de Intl-Tel-Input -->
<link rel="stylesheet" href="lib/jackocnr-intl-tel-input-dd568ff/build/css/intlTelInput.css">

<script src="lib/jackocnr-intl-tel-input-dd568ff/build/js/intlTelInput.min.js"></script>
    <script src="lib/jackocnr-intl-tel-input-dd568ff/build/js/utils.js"></script>

 
    </script>

</body>
</html>

<script>
  const input = document.querySelector("#telefono");

  const iti = window.intlTelInput(input, {
    initialCountry: "auto",
    geoIpLookup: function (callback) {
      fetch('https://ipinfo.io/json?token=8f5d6c61d1f54f') // Usa tu token real
        .then((resp) => resp.json())
        .then((data) => callback(data.country))
        .catch(() => callback("us"));
    },
    utilsScript: "public/lib/jackocnr-intl-tel-input-dd568ff/build/js/utils.js",
    preferredCountries: ["ve", "us", "es"],
    separateDialCode: true
  });

  // Al cambiar el país (selección de bandera), coloca el código en el input
  input.addEventListener('countrychange', function () {
    const dialCode = iti.getSelectedCountryData().dialCode;
    input.value = "+" + dialCode + " ";
    input.focus();
  });

  // Al cargar la página, agrega el código del país automáticamente
  window.addEventListener("load", function () {
    const initialDialCode = iti.getSelectedCountryData().dialCode;
    input.value = "+" + initialDialCode + " ";
  });

  // ✅ Restringir la entrada: solo números del 1 al 9 y el símbolo +
  input.addEventListener("input", function () {
    const permitido = /[^0-9+]/g;
    this.value = this.value.replace(permitido, "");
  });
</script>



<!-- JavaScript -->
<script>
    document.getElementById("fecha_nacimiento").addEventListener("change", function () {
        const fechaNacimiento = new Date(this.value);
        const hoy = new Date();
        const edadInput = document.getElementById("edad");
        const mensajeCentral = document.getElementById("mensaje-central");
        const mensajeTexto = document.getElementById("mensaje-texto");
        const cerrarMensaje = document.getElementById("cerrar-mensaje");
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
            edadInput.disabled = true;
            errorFecha.textContent = ""; // Limpiar mensaje bajo el campo
            mensajeCentral.classList.add("hidden"); // Ocultar mensaje central
        } else {
            edadInput.value = ""; // Limpiar la edad
            edadInput.disabled = true; // Bloquear el campo
            edadInput.classList.add("bg-gray-200", "cursor-not-allowed");
            edadInput.classList.remove("bg-white", "cursor-text");
            errorFecha.textContent = "Debe tener al menos 18 años para continuar."; // Mostrar mensaje bajo el campo

            // Mostrar mensaje central
            mensajeTexto.textContent = "Debe tener al menos 18 años para continuar.";
            mensajeCentral.classList.remove("hidden");

            // Reiniciar el campo de fecha
            this.value = "";

            // Ocultar automáticamente después de 5 segundos
            setTimeout(() => {
                mensajeCentral.classList.add("hidden");
            }, 5000);
        }

        // Permitir cerrar el mensaje manualmente
        cerrarMensaje.addEventListener("click", function () {
            mensajeCentral.classList.add("hidden");
        });
    });
</script>

<script>
    // Función para habilitar el campo de cédula cuando se selecciona una nacionalidad
    function habilitarCedula() {
        const cedulaInput = document.getElementById("cedula");
        cedulaInput.disabled = false; // Habilitar el campo
        cedulaInput.classList.remove("bg-gray-200", "cursor-not-allowed"); // Cambiar diseño
        cedulaInput.classList.add("bg-white", "cursor-text"); // Aplicar nuevos estilos
    }
</script>


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


<script>
    function validarCedula() {
    let cedula = document.getElementById("cedula").value;
    let nacionalidad = document.querySelector('input[name="nacionalidad"]:checked')?.value;

    if (!cedula || !nacionalidad) return; // No ejecutar si faltan datos

    fetch("validar_cedula.php", {
        method: "POST",
        body: new URLSearchParams({ cedula, nacionalidad }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(response => response.json())
    .then(data => {
        let mensajeError = document.getElementById("mensaje-error-input");
        let mensajeExito = document.getElementById("mensaje-exito-input");

        if (data.status === "error") {
            mensajeError.textContent = data.mensaje;
            mensajeError.classList.remove("hidden");
            mensajeExito.classList.add("hidden");
        } else {
            mensajeExito.textContent = data.mensaje;
            mensajeExito.classList.remove("hidden");
            mensajeError.classList.add("hidden");
        }
    });
}

// Habilitar el campo de cédula cuando el usuario elija nacionalidad
function habilitarCedula() {
    document.getElementById("cedula").disabled = false;
    document.getElementById("cedula").classList.remove("cursor-not-allowed");
}



    document.getElementById("formEmpleado").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("guardar_empleado.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            alert("✅ " + data.mensaje);
            this.reset(); // Limpiar formulario
        } else {
            alert("❌ " + data.mensaje);
        }
    });
});
</script>

<script>
const inputEmail = document.getElementById("email");
const mensajeFormatoError = document.getElementById("mensaje-validar_correo-input");
const mensajeError = document.getElementById("mensaje-error_correo-input");
const mensajeExito = document.getElementById("mensaje-exito_correo-input");

const API_KEY = "68ca8b6e145e7b545663e81329a5efea"; // Reemplaza con tu clave real

inputEmail.addEventListener("blur", function () {
    const email = this.value.trim();
    limpiarMensajes();

    const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!regexCorreo.test(email)) {
        mostrarMensaje(mensajeFormatoError, '<i class="fas fa-times-circle mr-1"></i> El formato del correo es incorrecto.', 'text-red-600');
        return;
    }

    mostrarMensaje(mensajeExito, '<i class="fas fa-spinner fa-spin mr-1"></i> Verificando existencia del correo...', 'text-blue-600');

    fetch(`https://apilayer.net/api/check?access_key=${API_KEY}&email=${email}&smtp=1&format=1`)
        .then(res => res.json())
        .then(data => {
            limpiarMensajes();

            if (data.format_valid && data.mx_found && data.smtp_check) {
                // Si el correo existe realmente, verificamos disponibilidad en la base de datos
                validarDisponibilidadEnBD(email);
            } else {
                mostrarMensaje(mensajeError, '<i class="fas fa-times-circle mr-1"></i> El correo no existe o no puede ser verificado.', 'text-red-600');
            }
        })
        .catch(() => {
            limpiarMensajes();
            mostrarMensaje(mensajeError, '<i class="fas fa-exclamation-triangle mr-1"></i> No se pudo verificar el correo. Problema con la red o la API.', 'text-yellow-600');
        });
});

function validarDisponibilidadEnBD(email) {
    fetch("validar_correo.php", {
        method: "POST",
        body: new URLSearchParams({ email }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "error") {
            mostrarMensaje(mensajeError, '<i class="fas fa-times-circle mr-1"></i> ' + data.mensaje, 'text-red-600');
        } else {
            mostrarMensaje(mensajeExito, '<i class="fas fa-check-circle mr-1"></i> ' + data.mensaje, 'text-green-600');
        }
    })
    .catch(() => {
        mostrarMensaje(mensajeError, '<i class="fas fa-exclamation-triangle mr-1"></i> Error al consultar la base de datos.', 'text-yellow-600');
    });
}

function limpiarMensajes() {
    [mensajeFormatoError, mensajeError, mensajeExito].forEach(el => {
        el.classList.add("hidden");
        el.classList.remove("text-red-600", "text-green-600", "text-yellow-600", "text-blue-600");
        el.innerHTML = "";
    });
}

function mostrarMensaje(elemento, mensaje, colorClass) {
    elemento.innerHTML = mensaje;
    elemento.classList.remove("hidden");
    elemento.classList.add(colorClass);
}
</script>
