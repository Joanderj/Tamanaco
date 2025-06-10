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

// Submenú actual: Sede (id_submenu = 2)
$submenu_actual = 28;

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

// Recuperar filtros
$accion = $_GET['accion'] ?? '';
$modulo = $_GET['modulo'] ?? '';
$estado = $_GET['estado'] ?? '';
$importancia = $_GET['importancia'] ?? '';
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFinal = $_GET['fechaFinal'] ?? '';

// Ordenamiento
$orderBy = 'ra.fecha DESC';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'fecha_asc': $orderBy = 'ra.fecha ASC'; break;
        case 'fecha_desc': $orderBy = 'ra.fecha DESC'; break;
        case 'modulo_asc': $orderBy = 'ra.modulo ASC'; break;
        case 'modulo_desc': $orderBy = 'ra.modulo DESC'; break;
    }
}

// Consulta total de elementos
$totalQuery = "
    SELECT COUNT(*) 
    FROM registro_actividades ra 
    INNER JOIN usuarios u ON ra.id_usuario = u.id_usuario 
    WHERE 1=1";
$params = [];

// Filtros dinámicos
if (!empty($accion)) {
    $totalQuery .= " AND ra.accion LIKE ?";
    $params[] = "%$accion%";
}
if (!empty($modulo)) {
    $totalQuery .= " AND ra.modulo = ?";
    $params[] = $modulo;
}
if (!empty($estado)) {
    $totalQuery .= " AND ra.estado = ?";
    $params[] = $estado;
}
if (!empty($importancia)) {
    $totalQuery .= " AND ra.importancia = ?";
    $params[] = $importancia;
}
if (!empty($fechaInicio)) {
    $totalQuery .= " AND ra.fecha >= ?";
    $params[] = $fechaInicio;
}
if (!empty($fechaFinal)) {
    $totalQuery .= " AND ra.fecha <= ?";
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

// Consulta de datos
$query = "
    SELECT ra.*, u.usuario 
    FROM registro_actividades ra 
    INNER JOIN usuarios u ON ra.id_usuario = u.id_usuario 
    WHERE 1=1";

if (!empty($accion)) { $query .= " AND ra.accion LIKE ?"; }
if (!empty($modulo)) { $query .= " AND ra.modulo = ?"; }
if (!empty($estado)) { $query .= " AND ra.estado = ?"; }
if (!empty($importancia)) { $query .= " AND ra.importancia = ?"; }
if (!empty($fechaInicio)) { $query .= " AND ra.fecha >= ?"; }
if (!empty($fechaFinal)) { $query .= " AND ra.fecha <= ?"; }

$query .= " ORDER BY $orderBy LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

$stmt = $conexion->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$registros = $result->fetch_all(MYSQLI_ASSOC);

// Guardar filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save_filter') {
    $filterName = $_POST['filterName'];
    $criterios = json_encode([
        'accion' => $accion,
        'modulo' => $modulo,
        'estado' => $estado,
        'importancia' => $importancia,
        'fechaInicio' => $fechaInicio,
        'fechaFinal' => $fechaFinal,
    ]);

    $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado, usuario_id_filtro) 
              VALUES (?, 'registro_actividades', ?, NOW(), ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssi", $filterName, $criterios, $id_perfil);

    echo json_encode($stmt->execute()
        ? ['success' => true]
        : ['success' => false, 'error' => $stmt->error]
    );

    $stmt->close();
}

// Cargar filtros guardados
$sql_cargar_filtros = "SELECT nombre_filtro, criterios FROM filtros_guardados WHERE usuario_id_filtro = ?";
$stmt_cargar_filtros = $conexion->prepare($sql_cargar_filtros);
$stmt_cargar_filtros->bind_param("i", $id_perfil);
$stmt_cargar_filtros->execute();
$result_cargar_filtros = $stmt_cargar_filtros->get_result();

$filtros_guardados = [];
while ($filtro = $result_cargar_filtros->fetch_assoc()) {
    $filtros_guardados[] = [
        'nombre_filtro' => $filtro['nombre_filtro'],
        'criterios' => json_decode($filtro['criterios'], true),
    ];
}

$stmt_cargar_filtros->close();

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
<div id="modalActividad" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Detalles de la Actividad</h2>

        <p><strong>Usuario:</strong> <span id="actividadUsuario"></span></p>
        <p><strong>Nombre:</strong> <span id="actividadNombre"></span></p>
        <p><strong>Acción:</strong> <span id="actividadAccion"></span></p>
        <p><strong>Actividad:</strong> <span id="actividadDescripcion"></span></p>
        <p><strong>Fecha:</strong> <span id="actividadFecha"></span></p>
        <p><strong>Módulo:</strong> <span id="actividadModulo"></span></p>
        <p><strong>IP:</strong> <span id="actividadIP"></span></p>
        <p><strong>Dispositivo:</strong> <span id="actividadDispositivo"></span></p>
        <p><strong>Estado:</strong> <span id="actividadEstado"></span></p>
        <p><strong>Importancia:</strong> <span id="actividadImportancia"></span></p>

        <button onclick="closeModalActividad()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">Cerrar</button>
    </div>
</div>
<script>
function showModalActividad(idRegistro) {
    fetch(`getActividad.php?id_registro=${idRegistro}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.error) {
                alert('Error al obtener los datos de la actividad.');
                return;
            }

            // Asignar los datos al modal
            document.getElementById('actividadUsuario').textContent = data.usuario || 'Desconocido';
            document.getElementById('actividadNombre').textContent = 
                `${data.primer_nombre} ${data.segundo_nombre || ''} ${data.primer_apellido} ${data.segundo_apellido || ''}`.trim();
            document.getElementById('actividadAccion').textContent = data.accion || '-';
            document.getElementById('actividadDescripcion').textContent = data.actividad || '-';
            document.getElementById('actividadFecha').textContent = data.fecha || '-';
            document.getElementById('actividadModulo').textContent = data.modulo || '-';
            document.getElementById('actividadIP').textContent = data.ip_address || '-';
            document.getElementById('actividadDispositivo').textContent = data.dispositivo || '-';
            document.getElementById('actividadEstado').textContent = data.estado || '-';
            document.getElementById('actividadImportancia').textContent = data.importancia || '-';

            // Mostrar modal
            const modal = document.getElementById('modalActividad');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al cargar los datos.');
        });
}

function closeModalActividad() {
    const modal = document.getElementById('modalActividad');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

 <!-- Contenedor para los botones superiores -->
 <div class="flex justify-start items-center space-x-4 mb-4">
      <!-- Diseño del botón "Empleado" -->
      <div class="flex items-center space-x-6">
        <?php if ($menu_actual == 2 && $permiso_menu['permiso'] > 0): ?>
          <div class="flex flex-col items-center">
        <div class="p-2 rounded-full border-4 border-blue-300 shadow-md">
          <a href="empleado.php">
            <button class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center hover:bg-blue-600">
          <i class="fas fa-user-cog text-xl"></i>
            </button>
          </a>
        </div>
        <span class="text-blue-700 font-bold text-sm mt-2">Empleado</span>
          </div>
        <?php endif; ?>
 

        <!-- Contenedor para los botones de submenús -->
        <?php foreach ($submenus_tipo_1 as $submenu): ?>
          <?php if ($submenu['nombre_submenu'] === 'Usuario' || $submenu['nombre_submenu'] === 'Actividad'): ?>
            <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
              <div class="flex flex-col items-center">
                <button class="
                  <?php echo $submenu['nombre_submenu'] === 'Usuario' ? 'bg-indigo-500 hover:bg-indigo-600' : 'bg-green-500 hover:bg-green-600'; ?> 
                  text-white w-16 h-16 rounded-full flex items-center justify-center">
                  <i class="<?php echo $submenu['nombre_submenu'] === 'Usuario' ? 'fas fa-user' : 'fas fa-calendar-check'; ?> text-xl"></i>
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
  
    <!-- Botones superiores -->
    <div class="flex justify-between items-center mb-4">
      
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
    <!-- Campo: Usuario -->
    <div class="mb-4">
        <label for="usuario" class="block text-gray-700 font-semibold mb-2">Usuario</label>
        <input 
            type="text" 
            id="usuario" 
            name="usuario"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            placeholder="Escribe el nombre de usuario..."
        />
    </div>

    <!-- Campo: Nombre de Persona -->
    <div class="mb-4">
        <label for="nombre_persona" class="block text-gray-700 font-semibold mb-2">Nombre</label>
        <input 
            type="text" 
            id="nombre_persona" 
            name="nombre_persona"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            placeholder="Escribe el nombre de la persona..."
        />
    </div>

    <!-- Campo: Apellido de Persona -->
    <div class="mb-4">
        <label for="apellido_persona" class="block text-gray-700 font-semibold mb-2">Apellido</label>
        <input 
            type="text" 
            id="apellido_persona" 
            name="apellido_persona"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
            placeholder="Escribe el apellido de la persona..."
        />
    </div>

    <!-- Campo: Perfil -->
    <div class="mb-4">
        <label for="id_perfil" class="block text-gray-700 font-semibold mb-2">Perfil</label>
        <select 
            id="id_perfil" 
            name="id_perfil"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            <option value="">Selecciona un perfil</option>
            <?php
            // Conexión a la base de datos
            $conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');

            // Comprobar la conexión
            if ($conexion->connect_error) {
                die('Error de conexión a la base de datos: ' . $conexion->connect_error);
            }

            // Consultar los perfiles
            $queryPerfiles = "SELECT id_perfil, nombre_perfil FROM perfiles";
            $resultPerfiles = $conexion->query($queryPerfiles);

            // Generar las opciones dinámicamente
            if ($resultPerfiles && $resultPerfiles->num_rows > 0) {
                while ($row = $resultPerfiles->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['id_perfil']) . "'>" . htmlspecialchars($row['nombre_perfil']) . "</option>";
                }
            } else {
                echo "<option value=''>No hay perfiles disponibles</option>";
            }

            // Cerrar la conexión
            $conexion->close();
            ?>
        </select>
    </div>

    <!-- Campo: Estado -->
    <div class="mb-4">
        <label for="id_status" class="block text-gray-700 font-semibold mb-2">Estado</label>
        <select 
            id="id_status" 
            name="id_status"
            class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
            <option value="">Selecciona una opción</option>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>
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
        <h1 class="text-xl font-semibold text-gray-700">Actividades de usuarios</h1>
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
            <!-- ID Registro -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-id-badge"></i>
                    <span>ID</span>
                </span>
            </th>
            <!-- Usuario -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-user"></i>
                    <span>Usuario</span>
                </span>
            </th>
            <!-- Acción -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-tasks"></i>
                    <span>Acción</span>
                </span>
            </th>
            <!-- Fecha -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Fecha</span>
                </span>
            </th>
            <!-- IP -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-network-wired"></i>
                    <span>IP</span>
                </span>
            </th>
            <!-- Módulo -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-cogs"></i>
                    <span>Módulo</span>
                </span>
            </th>
            <!-- Acciones -->
            <th class="px-6 py-3 text-left font-medium">
                <span class="flex items-center space-x-2">
                    <i class="fas fa-tools"></i>
                    <span>Acciones</span>
                </span>
            </th>
        </tr>
    </thead>
    <tbody id="dataTable" class="bg-white divide-y divide-gray-200">
        <?php foreach ($registros as $registro): ?>
        <tr class="hover:bg-gray-100">
            <td class="px-6 py-4 text-gray-800 font-medium"><?php echo htmlspecialchars($registro['id_registro']); ?></td>
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($registro['usuario']); ?></td>
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($registro['accion']); ?></td>
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($registro['fecha']); ?></td>
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($registro['ip_address']); ?></td>
            <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($registro['modulo']); ?></td>

            <?php
            include_once 'includes/permisos.php';

            $idPerfil = $_SESSION['id_perfil'];
            $idSubmenu = 27; // ID del submenú correspondiente a registro de actividades
            $permisos = obtenerPermisos($idPerfil, $idSubmenu);
            ?>

            <td class="px-6 py-4 flex space-x-2">
                <?php if (in_array(1, $permisos)): ?>
                    <button 
    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
    onclick="showModalActividad(<?= htmlspecialchars($registro['id_registro']) ?>)">
    <i class="fas fa-eye"></i> Ver
</button>
                <?php endif; ?>
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
