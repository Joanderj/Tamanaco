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
    <!-- Título con foto de perfil -->
    <!-- Mostrar imagen de perfil -->
<div class="flex items-center justify-center mb-4">

</div>
  
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
<h2 class="text-3xl font-extrabold text-gray-800">Formulario de Usuario</h2>

<!-- Descripción del formulario -->
<p class="text-gray-600 mt-2 text-center">Registra la información de los usuarios y administra sus datos de manera eficiente.</p>
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
        <form action="guardar_usuario.php" method="post" enctype="multipart/form-data">
<!-- Subir imagen de usuario -->
<div class="text-center mb-6">
    <div class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center">
        <input type="file" id="imagen" name="nombre_imagen" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer"/>
        <img id="imagen-preview" src="" alt="Previsualización de la imagen" class="absolute inset-0 w-full h-full object-cover rounded-lg hidden" />
        <div id="imagen-placeholder" class="text-center">
            <i class="fas fa-user-circle text-3xl text-blue-500"></i>
            <p class="text-blue-500 font-medium">Haga clic para subir una foto</p>
            <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
        </div>
    </div>
</div>

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

<!-- Seleccionar empleado y rol -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <div>
        <label for="empleado" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-users text-green-500 mr-2"></i> Seleccionar Empleado: <span class="text-red-600">*</span>
        </label>
        <select id="empleado" name="empleado" class="w-full border border-gray-300 rounded-lg p-3 bg-white">
    <option value="">Seleccionar</option>
    <?php
    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener lista de empleados
    $query = "SELECT id_persona, CONCAT(primer_nombre, ' ', primer_apellido) AS nombre_completo FROM personas ORDER BY primer_nombre ASC";
    $resultado = $conexion->query($query);

    while ($fila = $resultado->fetch_assoc()) {
        echo "<option value='" . $fila['id_persona'] . "'>" . htmlspecialchars($fila['nombre_completo']) . "</option>";
    }

    $conexion->close();
    ?>
</select>
<small class="text-gray-500 block mt-1">Ejemplo: Juan Pérez</small>
    </div>
    <div>
        <label for="rol" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user-cog text-purple-500 mr-2"></i> Tipo de Perfil: <span class="text-red-600">*</span>
        </label>
        <select id="rol" name="rol" class="w-full border border-gray-300 rounded-lg p-3 bg-white" required>
    <option value="">Seleccionar</option>
    <?php
    // Conexión a la base de datos
    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Obtener perfiles excluyendo "Administrador"
    $query = "SELECT id_perfil, nombre_perfil FROM perfiles WHERE nombre_perfil != 'Administrador' ORDER BY nombre_perfil ASC";
    $resultado = $conexion->query($query);

    while ($fila = $resultado->fetch_assoc()) {
        echo "<option value='" . $fila['id_perfil'] . "'>" . htmlspecialchars($fila['nombre_perfil']) . "</option>";
    }

    $conexion->close();
    ?>
</select>
<small class="text-gray-500 block mt-1">Ejemplo: Ingeniero, Gerente, Mecánico, Asistente</small>
    </div>
</div>

<!-- Cédula (bloqueada) -->
<div class="mb-6">
    <label for="cedula" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-id-card text-red-500 mr-2"></i> Cédula: 
    </label>
    <input type="text" id="cedula" name="cedula" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-200 cursor-not-allowed" readonly>
    <small class="text-gray-500 block mt-1">Los datos aparecerán automáticamente</small>
</div>

<!-- Nombre y Apellido (bloqueados) -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <div>
        <label for="nombre" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user text-blue-500 mr-2"></i> Nombre:
        </label>
        <input type="text" id="nombre" name="nombre" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-200 cursor-not-allowed" readonly>
        <small class="text-gray-500 block mt-1">Los datos aparecerán automáticamente</small>
    </div>
    <div>
        <label for="apellido" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-user-tag text-teal-500 mr-2"></i> Apellido:
        </label>
        <input type="text" id="apellido" name="apellido" class="w-full border border-gray-300 rounded-lg p-3 bg-gray-200 cursor-not-allowed" readonly>
        <small class="text-gray-500 block mt-1">Los datos aparecerán automáticamente</small>
    </div>
</div>

<!-- Nombre de usuario -->
<div class="mb-6">
    <label for="usuario" class="block font-semibold text-lg flex items-center">
        <i class="fas fa-user-tag text-teal-500 mr-2"></i> Nombre de Usuario: <span class="text-red-600">*</span>
    </label>
    <input type="text" id="usuario" name="usuario" class="w-full border border-gray-300 rounded-lg p-3" required>
    <small id="mensaje-error-input" class="text-red-500 hidden">Este usuario ya existe</small>
    <small id="mensaje-exito-input" class="text-green-500 hidden">¡Este usuario está disponible!</small>
    <small class="text-gray-500 block mt-1">Ejemplo: carlos.perez</small>
</div>
<!-- Contraseña y Confirmación -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <div class="relative">
        <label for="password" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-key text-orange-500 mr-2"></i> Contraseña: <span class="text-red-600">*</span>
        </label>
        <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg p-3 pr-10" required onkeyup="verificarSeguridad()">
        <button type="button" onclick="togglePassword('password', 'togglePasswordIcon1')" class="absolute top-10 right-3 text-gray-500">
            <i id="togglePasswordIcon1" class="fas fa-eye"></i>
        </button>
        <small class="text-gray-500 block mt-1">La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.</small>
    </div>

    <div class="relative">
        <label for="confirm_password" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-lock text-purple-500 mr-2"></i> Confirmar Contraseña: <span class="text-red-600">*</span>
        </label>
        <input type="password" id="confirm_password" name="confirm_password" class="w-full border border-gray-300 rounded-lg p-3 pr-10" required>
        <button type="button" onclick="togglePassword('confirm_password', 'togglePasswordIcon2')" class="absolute top-10 right-3 text-gray-500">
            <i id="togglePasswordIcon2" class="fas fa-eye"></i>
        </button>
        <small id="mensaje-error-contraseña" class="text-red-500 hidden">Las contraseñas no coinciden</small>
<small id="mensaje-exito-contraseña" class="text-green-500 hidden">¡Las contraseñas coinciden!</small>
    </div>
</div>


<!-- Nivel de seguridad de contraseña -->
<div class="mb-6">
    <label class="block font-semibold text-lg flex items-center">
        <i class="fas fa-shield-alt text-red-500 mr-2"></i> Nivel de Seguridad:
    </label>
    <div class="w-full bg-gray-300 rounded-lg overflow-hidden">
        <div id="barraSeguridad" class="h-3 w-1/5 bg-red-500 transition-all duration-300"></div>
    </div>
    <p id="nivelSeguridadTexto" class="text-center text-gray-700 font-semibold mt-2">Bajo</p>
</div>

 <!-- Nota sobre campos obligatorios --> 
 <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>
          
            <!-- Botones -->
            <div class="flex justify-between mt-4 space-x-4">
                <button id="guardar" type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg hover:bg-green-600 transition-all duration-300">
                    <i class="fas fa-save mr-2"></i> Guardar Usuario
                </button>
                <button type="button" onclick="location.href='usuario.php';" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600 transition-all duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Regresar
                </button>
            </div>
        </form>
    </div>
</div>
</div>
<script>
function validarCoincidencia() {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const mensajeError = document.getElementById("mensaje-error-contraseña");
    const mensajeExito = document.getElementById("mensaje-exito-contraseña");

    if (password === confirmPassword && password !== "") {
        mensajeExito.classList.remove("hidden");
        mensajeError.classList.add("hidden");
        return true;
    } else {
        mensajeError.classList.remove("hidden");
        mensajeExito.classList.add("hidden");
        return false;
    }
}

function verificarSeguridad() {
    const password = document.getElementById("password").value;
    const barraSeguridad = document.getElementById("barraSeguridad");
    const nivelSeguridadTexto = document.getElementById("nivelSeguridadTexto");

    let nivel = "Bajo";
    let ancho = "20%";
    let color = "bg-red-500";

    if (password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password)) {
        nivel = "Intermedio";
        ancho = "60%";
        color = "bg-yellow-500";
    }
    if (password.length >= 12 && /[!@#$%^&*]/.test(password)) {
        nivel = "Alto";
        ancho = "100%";
        color = "bg-green-500";
    }

    nivelSeguridadTexto.textContent = nivel;
    barraSeguridad.className = `h-3 ${color} transition-all duration-300`;
    barraSeguridad.style.width = ancho;

    return nivel;
}

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

// Validación en tiempo real
document.getElementById("password").addEventListener("keyup", () => {
    verificarSeguridad();
    validarCoincidencia();
});
document.getElementById("confirm_password").addEventListener("keyup", validarCoincidencia);

// Validación final al intentar guardar
document.getElementById("guardar").addEventListener("click", function (e) {
    const nivelSeguridad = verificarSeguridad();
    const coinciden = validarCoincidencia();

    if (nivelSeguridad === "Bajo" || !coinciden) {
    e.preventDefault(); // Detiene el envío del formulario
    mostrarModalError(); // Muestra el modal en pantalla
}

});
</script>

<!-- Modal de error -->
<div id="modalError" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full text-center animate-bounce-in">
    <h2 class="text-xl font-bold text-red-600 mb-2">Error al guardar</h2>
    <p class="text-gray-700 mb-4">La contraseña es insegura o las contraseñas no coinciden.</p>
    <button onclick="cerrarModalError()" class="mt-2 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-all">
      Cerrar
    </button>
  </div>
</div>
<style>
@keyframes bounce-in {
  0% {
    transform: scale(0.95);
    opacity: 0;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
.animate-bounce-in {
  animation: bounce-in 0.3s ease-out;
}
</style>
<script>
function mostrarModalError() {
  document.getElementById("modalError").classList.remove("hidden");
}
function cerrarModalError() {
  document.getElementById("modalError").classList.add("hidden");
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

</body>
</html>

<script>
    document.getElementById("empleado").addEventListener("change", function () {
    let idEmpleado = this.value;

    if (!idEmpleado) return; // No ejecutar si no hay selección

    fetch("obtener_empleado.php", {
        method: "POST",
        body: new URLSearchParams({ id_empleado: idEmpleado }),
        headers: { "Content-Type": "application/x-www-form-urlencoded" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("cedula").value = data.datos.cedula; // Mostrar cédula con nacionalidad
            document.getElementById("nombre").value = data.datos.primer_nombre;
            document.getElementById("apellido").value = data.datos.primer_apellido;
        } else {
            alert("❌ " + data.mensaje);
        }
    });
});
</script>

<script>
    document.getElementById("usuario").addEventListener("keyup", function () {
    let usuario = this.value;
    let idPerfil = document.getElementById("rol").value; // Obtener el perfil seleccionado

    if (!usuario || !idPerfil) return; // No ejecutar si faltan datos

    fetch("validar_perfil.php", {
        method: "POST",
        body: new URLSearchParams({ usuario, id_perfil: idPerfil }),
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
});
</script>