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
  <div class="flex justify-start items-center space-x-4 mb-4">
      <!-- Diseño del botón "Empleado" -->
      <div class="flex items-center space-x-6">
        <div class="flex flex-col items-center">
          <div class="p-2 rounded-full border-4 border-blue-300 shadow-md">
            <button class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center hover:bg-blue-600">
              <i class="fas fa-user-cog text-xl"></i>
            </button>
          </div>
          <span class="text-blue-700 font-bold text-sm mt-2">Empleado</span>
        </div>

        <!-- Contenedor para los botones de submenús -->
        <?php foreach ($submenus_tipo_1 as $submenu): ?>
          <?php if ($submenu['nombre_submenu'] === 'Usuario' || $submenu['nombre_submenu'] === 'Actividad' || $submenu['nombre_submenu'] === 'Permisos'): ?>
            <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
              <div class="flex flex-col items-center">
                <button class="
                  <?php 
                    if ($submenu['nombre_submenu'] === 'Usuario') {
                        echo 'bg-indigo-500 hover:bg-indigo-600';
                    } elseif ($submenu['nombre_submenu'] === 'Actividad') {
                        echo 'bg-green-500 hover:bg-green-600';
                    } elseif ($submenu['nombre_submenu'] === 'Permisos') {
                        echo 'bg-yellow-500 hover:bg-yellow-600';
                    }
                  ?> 
                  text-white w-16 h-16 rounded-full flex items-center justify-center">
                  <i class="
                    <?php 
                      if ($submenu['nombre_submenu'] === 'Usuario') {
                          echo 'fas fa-user';
                      } elseif ($submenu['nombre_submenu'] === 'Actividad') {
                          echo 'fas fa-calendar-check';
                      } elseif ($submenu['nombre_submenu'] === 'Permisos') {
                          echo 'fas fa-key';
                      }
                    ?> text-xl"></i>
                </button>
                <span class="text-gray-700 text-sm mt-2">
                  <?php echo htmlspecialchars($submenu['nombre_submenu']); ?>
                </span>
              </div>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
  </div>
  
    <?php
    include_once 'includes/permisos.php'; // Incluir el archivo de permisos
    $idPerfil = $_SESSION['id_perfil'];
    $idMenu = 2; // ID del menú actual
    $permisos = obtenerPermisosMenu($idPerfil, $idMenu);
    ?>

    <!-- Botones superiores -->
    <div class="flex justify-between items-center mb-4">
        <button 
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
            onclick="window.location.href='dashboard.php'">
            <i class="fas fa-arrow-left"></i> Regresar
        </button>

        <div class="space-x-2">
            <?php if (in_array(2, $permisos)): ?>
                <button 
                    onclick="window.location.href='formulario_guardar_empleado.php'" 
                    class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">
                    <i class="fas fa-user-plus"></i> Registrar
                </button>
            <?php endif; ?>

            <?php if (in_array(4, $permisos)): ?>
                <button 
                    onclick="window.location.href='imprimir_empleado.php'"  class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            <?php endif; ?>
        </div>
    </div>
    
  <!-- Filtros -->
<div class="flex items-center space-x-4 mb-4 border border-gray-300 p-2 rounded-lg">
   <!-- Input estilizado -->
<input 
  type="text" 
  id="searchInput" 
  class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
  placeholder="Buscar en la tabla..." 
/>



    <!-- Botón Filtrar -->
    <button 
      id="filterButton" 
      class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 flex items-center" 
      onclick="toggleFilterForm()">
      <i class="fas fa-filter mr-2"></i> Filtrar
    </button>
</div>
<!-- Formulario para filtros -->
<div id="filterForm" class="hidden mt-2 bg-white border border-gray-300 rounded shadow-lg p-4">
   <!-- Sección para mostrar filtros guardados -->
   <div id="savedFilters" class="mt-4">
        <h4 class="text-lg font-bold mb-2 text-indigo-500">Filtros Guardados</h4>
        <ul id="filtersList" class="list-disc pl-5"></ul>
    </div>
    <h3 class="text-lg font-bold mb-4 text-indigo-500">Opciones de Filtro</h3>
    <form id="filterOptionsForm" class="space-y-6">
    <!-- Campo: Cédula -->
    <div class="mb-4">
        <label for="cedula" class="block text-gray-700 font-semibold mb-2">Cédula</label>
        <input 
            type="text" 
            id="cedula" 
            name="cedula"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            placeholder="Escribe la cédula..."
        />
    </div>

    <!-- Campo: Nombre y Apellido -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="primer_nombre" class="block text-gray-700 font-semibold mb-2">Nombre</label>
            <input 
                type="text" 
                id="primer_nombre" 
                name="primer_nombre"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                placeholder="Escribe el nombre..."
            />
        </div>
        <div>
            <label for="primer_apellido" class="block text-gray-700 font-semibold mb-2">Apellido</label>
            <input 
                type="text" 
                id="primer_apellido" 
                name="primer_apellido"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                placeholder="Escribe el apellido..."
            />
        </div>
    </div>

    <!-- Campo: Teléfono y Correo Electrónico -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="telefono" class="block text-gray-700 font-semibold mb-2">Teléfono</label>
            <input 
                type="text" 
                id="telefono" 
                name="telefono"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                placeholder="Escribe el teléfono..."
            />
        </div>
        <div>
            <label for="correo_electronico" class="block text-gray-700 font-semibold mb-2">Correo Electrónico</label>
            <input 
                type="email" 
                id="correo_electronico" 
                name="correo_electronico"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                placeholder="Escribe el correo electrónico..."
            />
        </div>
    </div>

    <!-- Campo: Cargo y Estado -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Dropdown para Cargo -->
        <div>
            <label for="nombreCargo" class="block text-gray-700 font-semibold mb-2">Nombre del Cargo</label>
            <select 
                id="nombreCargo" 
                name="nombreCargo"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <option value="">Selecciona un cargo</option>
                <?php
                // Conexión a la base de datos
                $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

                // Comprobar la conexión
                if ($conexion->connect_error) {
                    die('Error de conexión a la base de datos: ' . $conexion->connect_error);
                }

                // Consultar los cargos
                $queryCargos = "SELECT id_cargo, nombre_cargo FROM cargo";
                $resultCargos = $conexion->query($queryCargos);

                // Generar las opciones dinámicamente
                if ($resultCargos && $resultCargos->num_rows > 0) {
                    while ($row = $resultCargos->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['id_cargo']) . "'>" . htmlspecialchars($row['nombre_cargo']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No hay cargos disponibles</option>";
                }

                // Cerrar la conexión
                $conexion->close();
                ?>
            </select>
        </div>
        <!-- Select para Estado -->
        <div>
            <label for="status" class="block text-gray-700 font-semibold mb-2">Estado</label>
            <select 
                id="status" 
                name="status"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <option value="">Selecciona una opción</option>
                <option value="1">Activo</option>
                <option value="2">Inactivo</option>
            </select>
        </div>
    </div>

    <!-- Campo: Rango de Fechas de Nacimiento -->
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="fecha_inicio" class="block text-gray-700 font-semibold mb-2">Fecha Inicio</label>
            <input 
                type="date" 
                id="fecha_inicio" 
                name="fecha_inicio"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            />
        </div>
        <div>
            <label for="fecha_final" class="block text-gray-700 font-semibold mb-2">Fecha Final</label>
            <input 
                type="date" 
                id="fecha_final" 
                name="fecha_final"
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            />
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <!-- Botón Aplicar Filtro -->
            <button 
                type="button" 
                class="bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 focus:outline-none"
                onclick="applyFilters()"
            >
                Aplicar Filtro
            </button>
            <!-- Botón Borrar Filtro -->
            <button 
                type="reset" 
                class="border border-red-500 text-red-500 px-4 py-2 rounded-lg hover:bg-red-50 focus:outline-none"
            >
                Borrar Filtro
            </button>
        </div>
        <!-- Botón Guardar Filtro -->
        <button 
            type="button" 
            class="border border-blue-500 text-blue-500 px-4 py-2 rounded-lg hover:bg-blue-50 focus:outline-none"
            onclick="showSaveFilterModal()"
        >
            Guardar Filtro
        </button>
    </div>
</form>
</div>

<!-- Modal para guardar filtro -->
<div id="saveFilterModal" class="hidden fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
    <div class="bg-white rounded-lg p-6">
        <h3 class="text-lg font-bold mb-4">Nombre del Filtro</h3>
        <input type="text" id="filterName" class="border border-gray-300 rounded p-2 w-full" placeholder="Escribe un nombre para el filtro..." />
        <div class="flex justify-end mt-4">
            <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600" onclick="saveFilter()">Guardar</button>
            <button class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-50" onclick="closeSaveFilterModal()">Cancelar</button>
        </div>
    </div>
</div>


<!-- Contenedor principal con línea alrededor -->
<div class="border border-gray-300 rounded-lg shadow-md p-4">
    <!-- Encabezado encima de la tabla -->
    <div class="flex justify-between items-center mb-4 border-b border-gray-300 pb-2">
        <h1 class="text-xl font-semibold text-gray-700">Empleados</h1>
        <div class="relative">
            <select id="clasificacion" 
                    onchange="window.location.href='?sort=' + this.value + '&itemsPerPage=<?php echo $itemsPerPage; ?>&page=1'" 
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center appearance-none">
                <option value="" disabled selected>Clasificar</option>
                <option value="normal">Normal</option>
                <option value="fecha_asc">Por fecha más antigua</option>
                <option value="fecha_desc">Por fecha más reciente</option>
                <option value="nombre_asc">Por nombre (A-Z)</option>
                <option value="nombre_desc">Por nombre (Z-A)</option>
                <option value="numero_asc">Por número más bajo</option>
                <option value="numero_desc">Por número más alto</option>
            </select>
            <i class="fas fa-sort absolute right-4 top-3 text-gray-700 pointer-events-none"></i>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="overflow-x-auto">
    <table class="table-auto w-full border-collapse border border-gray-300 shadow-lg rounded-lg">
    <thead class="bg-indigo-600 text-white">
        <tr>
            <!-- Encabezado Cédula -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-id-card"></i>
                    <span>Cédula</span>
                </span>
            </th>
            <!-- Encabezado Nombre -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span>Nombre</span>
                </span>
            </th>
            <!-- Encabezado Apellido -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span>Apellido</span>
                </span>
            </th>
            <!-- Encabezado Fecha de Nacimiento -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Fecha Nacimiento</span>
                </span>
            </th>
            <!-- Encabezado Teléfono -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-phone"></i>
                    <span>Teléfono</span>
                </span>
            </th>
            <!-- Encabezado Correo Electrónico -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-envelope"></i>
                    <span>Correo</span>
                </span>
            </th>
            <!-- Encabezado Cargo -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-briefcase"></i>
                    <span>Cargo</span>
                </span>
            </th>
            <!-- Encabezado Acciones -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-tools"></i>
                    <span>Acciones</span>
                </span>
            </th>
        </tr>
    </thead>
    <tbody id="dataTable" class="bg-white divide-y divide-gray-200">
        <?php foreach ($personas as $persona): ?>
        <tr class="hover:bg-gray-100">
            <!-- Cédula -->
            <td class="px-6 py-4 text-gray-800 font-medium"><?php echo htmlspecialchars($persona['cedula']); ?></td>
            <!-- Nombre -->
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($persona['primer_nombre']); ?></td>
            <!-- Apellido -->
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($persona['primer_apellido']); ?></td>
            <!-- Fecha de Nacimiento -->
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($persona['fecha_nacimiento']); ?></td>
            <!-- Teléfono -->
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($persona['telefono']); ?></td>
            <!-- Correo Electrónico -->
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($persona['correo_electronico']); ?></td>
            <!-- Cargo -->
            <td class="px-6 py-4 text-gray-600">
                <?php echo !empty($persona['nombre_cargo']) ? htmlspecialchars($persona['nombre_cargo']) : 'Sin cargo'; ?>
            </td>
            <!-- Acciones -->
            <td class="px-6 py-4 flex space-x-2">
            <div class="flex space-x-2">
    <!-- Botón Ver -->
      <?php if (in_array(1, $permisos)): ?>
    <button 
    class="flex items-center space-x-2 bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-blue-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition-transform transform hover:scale-105" 
    onclick="showModal(<?php echo htmlspecialchars($persona['id_persona']); ?>)">
    <i class="fas fa-eye"></i>
    <span>Ver</span>
</button>
  <?php endif; ?>
    <!-- Botón Modificar -->
    <?php if (in_array(3, $permisos)): ?>
        <button 
         onclick="window.location.href='formulario_modificar_empleado.php?id_persona=<?php echo $persona['id_persona']; ?>'" 
            class="flex items-center space-x-2 bg-yellow-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-yellow-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-transform transform hover:scale-105">
            <i class="fas fa-edit"></i>
            <span>Modificar</span>
        </button>
    <?php endif; ?>

    <?php if (in_array(4, $permisos)): ?>
         <button 
               onclick="window.location.href='imprimir_empleado_individual.php?id_persona=<?php echo $persona['id_persona']; ?>'" 
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                <i class="fas fa-print"></i> Imprimir
            </button>
    <?php endif; ?>
</div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>

    <!-- Paginación -->
    <div class="mt-4 flex items-center justify-between">
        <div class="flex-1"></div>
        <div class="flex space-x-2">
            <a href="?page=<?php echo max(1, $currentPage - 1); ?>&itemsPerPage=<?php echo $itemsPerPage; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>" class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Anterior
            </a>

            <?php
            // Mostrar números de página
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo "<button class='bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500'>$i</button>";
                } elseif ($i <= 3 || $i > $totalPages - 3 || abs($currentPage - $i) < 2) {
                    echo "<a href='?page=$i&itemsPerPage=$itemsPerPage&sort=" . (isset($_GET['sort']) ? $_GET['sort'] : '') . "' class='border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500'>$i</a>";
                } elseif ($i == 4 && $currentPage > 5) {
                    echo "<span class='text-gray-500'>...</span>";
                }
            }
            ?>

            <a href="?page=<?php echo min($totalPages, $currentPage + 1); ?>&itemsPerPage=<?php echo $itemsPerPage; ?>&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>" class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Siguiente
            </a>
        </div>

        <!-- Elementos por página a la derecha -->
        <div class="flex items-center space-x-2 flex-1 justify-end">
            <label for="itemsPerPage" class="text-gray-600">Elementos por página:</label>
            <select id="itemsPerPage" onchange="window.location.href='?itemsPerPage=' + this.value + '&page=1&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>'" class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50">
                <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $itemsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                <option value="500" <?php echo $itemsPerPage == 500 ? 'selected' : ''; ?>>500</option>
                <option value="1000" <?php echo $itemsPerPage == 1000 ? 'selected' : ''; ?>>1000</option>
            </select>
            <span class="text-gray-600">Mostrando <?php echo $offset + 1; ?>-<?php echo min($offset + $itemsPerPage, $totalItems); ?> de <?php echo $totalItems; ?></span>
        </div>
    </div>
</div>




  </div>
  <script>
    // Mostrar/ocultar el formulario de filtros
    function toggleFilterForm() {
        const form = document.getElementById('filterForm');
        form.classList.toggle('hidden');
    }

    // Función para aplicar filtros
    function applyFilters() {
        const cedula = document.getElementById('cedula').value;
        const primerNombre = document.getElementById('primer_nombre').value;
        const primerApellido = document.getElementById('primer_apellido').value;
        const telefono = document.getElementById('telefono').value;
        const correoElectronico = document.getElementById('correo_electronico').value;
        const nombreCargo = document.getElementById('nombreCargo').value;
        const status = document.getElementById('status').value;
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFinal = document.getElementById('fecha_final').value;

        // Redirigir a la misma página con los parámetros del filtro aplicados
        const queryParams = new URLSearchParams({
            cedula: cedula || '',
            primer_nombre: primerNombre || '',
            primer_apellido: primerApellido || '',
            telefono: telefono || '',
            correo_electronico: correoElectronico || '',
            nombreCargo: nombreCargo || '',
            status: status || '',
            fecha_inicio: fechaInicio || '',
            fecha_final: fechaFinal || ''
        });

        // Recarga la página con los filtros aplicados
        window.location.href = `?${queryParams.toString()}`;
    }

    // Función para filtrar la tabla en tiempo real
    function filterTable() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('dataTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let rowContainsFilter = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    const cellValue = cells[j].textContent || cells[j].innerText;
                    if (cellValue.toLowerCase().indexOf(filter) > -1) {
                        rowContainsFilter = true;
                        break;
                    }
                }
            }

            rows[i].style.display = rowContainsFilter ? "" : "none"; // Mostrar/ocultar filas
        }
    }

    // Asignar evento al campo de búsqueda
    document.getElementById('searchInput').addEventListener('keyup', filterTable);

    // Captura el evento de presionar una tecla (Enter) en el campo de búsqueda
    document.getElementById('searchInput').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            const searchValue = this.value;
            window.location.href = `${window.location.pathname}?cedula=${encodeURIComponent(searchValue)}`;
        }
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
</body>
</html>
<script>
function showModal(idPersona) {
    fetch(`getPersona.php?id_persona=${idPersona}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta del servidor no válida');
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.error) {
                alert('Error: ' + (data.error || 'No se pudo obtener la persona.'));
                return;
            }

            document.getElementById('personaCedula').textContent = data.cedula ?? 'Sin cédula';
            document.getElementById('personaNacionalidad').textContent = data.nacionalidad ?? 'Sin nacionalidad';
            document.getElementById('personaNombre').textContent = `${data.primer_nombre ?? ''} ${data.segundo_nombre ?? ''}`.trim();
            document.getElementById('personaApellido').textContent = `${data.primer_apellido ?? ''} ${data.segundo_apellido ?? ''}`.trim();
            document.getElementById('personaCorreo').textContent = data.correo_electronico ?? 'Sin correo';
            document.getElementById('personaTelefono').textContent = data.telefono ?? 'Sin teléfono';
            document.getElementById('personaFechaNacimiento').textContent = data.fecha_nacimiento ?? 'Sin fecha';
            document.getElementById('personaGenero').textContent = data.genero ?? 'Sin género';
            document.getElementById('personaDireccion').textContent = data.direccion ?? 'Sin dirección';
            document.getElementById('personaCargo').textContent = data.nombre_cargo ?? 'Sin cargo';
            document.getElementById('personaStatus').textContent = (data.id_status == 1 ? 'Activo' : 'Inactivo');
            document.getElementById('personaPais').textContent = data.pais ?? 'Sin país';
            document.getElementById('personaEstado').textContent = data.estado ?? 'Sin estado';

            document.getElementById('modalPersona').classList.remove('hidden');
            document.getElementById('modalPersona').classList.add('flex');
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            alert('Error al cargar los datos de la persona.');
        });
}

function closeModal() {
    const modal = document.getElementById('modalPersona');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
