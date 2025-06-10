<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: iniciar_sesion.php");
    exit();
}

// Mostrar mensaje de bienvenida si es el primer inicio de sesión
$showWelcomeMessage = false;
if (isset($_SESSION['first_login']) && $_SESSION['first_login'] === true) {
    $showWelcomeMessage = true;
    unset($_SESSION['first_login']); // Desactivamos la variable para evitar que se muestre nuevamente
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener el id_perfil del usuario actual desde la sesión
$id_perfil = $_SESSION['id_perfil'];

// Consulta para obtener los menús principales (tipo_menu = 1) que están activos y cuyo perfil tiene permisos
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

// Consulta para obtener los menús del usuario (tipo_menu = 2) que están activos y cuyo perfil tiene permisos
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

<hr>




<!-- Contenedor para los botones superiores -->
<div class="flex justify-start items-center space-x-4 mb-4">

 <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">

    <!-- Botones superiores -->
    <div class="flex justify-between items-center mb-4">
      <button onclick="window.location.href='dashboard.php'" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
        <i class="fas fa-arrow-left"></i> Regresar
      </button>
      <div class="space-x-2">
      
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
    <form id="filterOptionsForm">
        <!-- Campo: Nombre de Sede -->
        <div class="mb-4">
            <label for="nombreSede" class="block text-gray-700 mb-2">Nombre de Sede</label>
            <input 
              type="text" 
              id="nombreSede" 
              class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
              placeholder="Escribe el nombre de la sede..."
            />
        </div>

        <!-- Campo: Status y Sucursal Juntos -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Status -->
            <div>
                <label for="status" class="block text-gray-700 mb-2">Status</label>
                <select 
                  id="status" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona una opción</option>
                    <option value="1">Activo</option>
                    <option value="2">Inactivo</option>
                </select>
            </div>
            <!-- Sucursal -->
            <div>
                <label for="sucursal" class="block text-gray-700 mb-2">Sucursal</label>
                <select 
                  id="sucursal" 
                  name="sucursal"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona una sucursal</option>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

                    // Comprobar conexión
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Consultar las sucursales
                    $query = "SELECT id_sucursal, nombre FROM sucursal";
                    $result = $conexion->query($query);

                    // Generar opciones dinámicamente
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idSucursal = htmlspecialchars($row["id_sucursal"]);
                            $nombreSucursal = htmlspecialchars($row["nombre"]);
                            echo "<option value='$idSucursal'>$nombreSucursal</option>";
                        }
                    } else {
                        echo "<option value=''>No hay sucursales disponibles</option>";
                    }

                    // Cerrar conexión
                    $conexion->close();
                    ?>
                </select>
            </div>
        </div>

        <!-- Campo: Fechas Juntas -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Fecha Inicio -->
            <div>
                <label for="fechaInicio" class="block text-gray-700 mb-2">Fecha Inicio</label>
                <input 
                  type="date" 
                  id="fechaInicio" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                />
            </div>
            <!-- Fecha Final -->
            <div>
                <label for="fechaFinal" class="block text-gray-700 mb-2">Fecha Final</label>
                <input 
                  type="date" 
                  id="fechaFinal" 
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                />
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between mt-4">
            <div class="flex space-x-2">
                <!-- Botón Aplicar Filtro -->
                <button 
                  type="button" 
                  class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600"
                  onclick="applyFilters()"
                >
                    Aplicar Filtro
                </button>
                <!-- Botón Borrar Filtro -->
                <button 
                  type="reset" 
                  class="border border-red-500 text-red-500 px-4 py-2 rounded hover:bg-red-50"
                >
                    Borrar Filtro
                </button>
            </div>
            <!-- Botón Guardar Filtro -->
            <button 
              type="button" 
              class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50"
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
        <h1 class="text-xl font-semibold text-gray-700">Tareas de hoy </h1>
        <?php
include 'db_connection.php';

// Configurar zona horaria si aplica
date_default_timezone_set('America/Caracas'); // O la que uses

$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fecha de hoy
$hoy = date('Y-m-d');

// --------- Conteo total para paginación ----------
$countQuery = "
    SELECT COUNT(DISTINCT t.id_tarea) AS total
    FROM tareas t
    LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
    WHERE t.fecha_inicio = :fecha_hoy AND t.status_id IN (1, 5)
";
$stmt = $conn->prepare($countQuery);
$stmt->bindValue(':fecha_hoy', $hoy);
$stmt->execute();
$totalItems = $stmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// --------- Consulta principal de tareas ----------
$query = "
    SELECT 
        t.id_tarea, t.titulo_tarea, t.descripcion_tarea, t.fecha_inicio, t.hora_inicio, t.tiempo_programado,
        tm.nombre_tipo AS tipo_mantenimiento,
        mu.CodigoUnico AS codigo_maquina,
        m.nombre_maquina, m.nombre_imagen AS imagen_maquina, m.url AS url_maquina,
        ma.nombre_marca, mo.nombre_modelo,
        s.nombre_sede AS ubicacion_sede,
        e.nombre_status AS estado_tarea,
        GROUP_CONCAT(CONCAT(p.primer_nombre, ' ', p.primer_apellido) SEPARATOR ', ') AS responsables_asignados
    FROM tareas t
    LEFT JOIN tipo_mantenimiento tm ON t.tipo_mantenimiento_id = tm.id_tipo
    LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
    LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
    LEFT JOIN marca ma ON m.id_marca = ma.id_marca
    LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN sede s ON mu.id_sede = s.id_sede
    LEFT JOIN status e ON t.status_id = e.id_status
    LEFT JOIN responsable r ON t.id_tarea = r.tarea_id
    LEFT JOIN personas p ON r.persona_id = p.id_persona
    WHERE t.fecha_inicio = :fecha_hoy AND t.status_id IN (1, 5)
    GROUP BY t.id_tarea
    ORDER BY t.hora_inicio ASC
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':fecha_hoy', $hoy);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();

$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
</h1>
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
 <?php
include 'db_connection.php';

date_default_timezone_set('America/Caracas');

$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fechas del mes actual
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');

// Contar tareas correctivas del mes
$countQuery = "
    SELECT COUNT(DISTINCT t.id_tarea) AS total
    FROM tareas t
    WHERE t.tipo_mantenimiento_id = 2
    AND t.fecha_inicio BETWEEN :inicioMes AND :finMes
";
$stmt = $conn->prepare($countQuery);
$stmt->bindValue(':inicioMes', $inicioMes);
$stmt->bindValue(':finMes', $finMes);
$stmt->execute();
$totalItems = $stmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta principal solo para mantenimientos correctivos del mes actual
$query = "
    SELECT 
        t.id_tarea, t.titulo_tarea, t.descripcion_tarea, t.fecha_inicio, t.hora_inicio, t.tiempo_programado,
        tm.nombre_tipo AS tipo_mantenimiento,
        mu.CodigoUnico AS codigo_maquina,
        m.nombre_maquina, m.nombre_imagen AS imagen_maquina, m.url AS url_maquina,
        ma.nombre_marca, mo.nombre_modelo,
        s.nombre_sede AS ubicacion_sede,
        e.id_status,
        e.nombre_status AS estado_tarea,
        GROUP_CONCAT(CONCAT(p.primer_nombre, ' ', p.primer_apellido) SEPARATOR ', ') AS responsables_asignados
    FROM tareas t
    LEFT JOIN tipo_mantenimiento tm ON t.tipo_mantenimiento_id = tm.id_tipo
    LEFT JOIN maquina_unica mu ON t.id_maquina_unica = mu.id_maquina_unica
    LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
    LEFT JOIN marca ma ON m.id_marca = ma.id_marca
    LEFT JOIN modelo mo ON m.id_modelo = mo.id_modelo
    LEFT JOIN sede s ON mu.id_sede = s.id_sede
    LEFT JOIN status e ON t.status_id = e.id_status
    LEFT JOIN responsable r ON t.id_tarea = r.tarea_id
    LEFT JOIN personas p ON r.persona_id = p.id_persona
    WHERE t.tipo_mantenimiento_id = 2
    AND t.fecha_inicio BETWEEN :inicioMes AND :finMes
    GROUP BY t.id_tarea
    ORDER BY t.fecha_inicio ASC, t.hora_inicio ASC
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':inicioMes', $inicioMes);
$stmt->bindValue(':finMes', $finMes);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();

$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>





<!-- Tabla de Tareas -->
<table class="table-auto w-full border border-gray-300 shadow-lg rounded-lg">
    <thead class="bg-indigo-500 text-white">
        <tr>
            <th class="px-6 py-3 text-left">ID Tarea</th>
            <th class="px-6 py-3 text-left">Tarea</th>
            <th class="px-6 py-3 text-left">Máquina</th>
            <th class="px-6 py-3 text-left">Ubicación</th>
            <th class="px-6 py-3 text-left">Responsables</th>
            <th class="px-6 py-3 text-left">Estado</th>
            <th class="px-6 py-3 text-center">Acciones</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
    <?php foreach ($tareas as $fila) { 
        $color = ($fila['estado_tarea'] === 'Pendiente') ? 'bg-red-500' :
                 (($fila['estado_tarea'] === 'Planificado') ? 'bg-yellow-500' : 'bg-green-500');
    ?>
        <tr class="hover:bg-gray-100">
            <!-- ID Tarea -->
            <td class="px-6 py-4 font-semibold">
                <i class="fas fa-hashtag text-indigo-500 mr-2"></i> <?= htmlspecialchars($fila['id_tarea']) ?>
            </td>

            <!-- Tarea -->
            <td class="px-6 py-4">
                <div class="flex flex-col items-start">
                    <span class="text-lg font-bold pb-3 border-b border-gray-300">
                        <?= htmlspecialchars($fila['titulo_tarea']) ?>
                    </span>
                    <span class="text-lg flex items-center py-3 border-b border-gray-300">
                        <i class="fas fa-clock text-blue-600 text-xl mr-3"></i> <?= htmlspecialchars($fila['tiempo_programado']) ?>
                    </span>
                    <span class="text-lg flex items-center py-3 border-b border-gray-300">
                        <i class="fas fa-calendar text-blue-600 text-xl mr-3"></i> <?= htmlspecialchars($fila['fecha_inicio']) ?>
                    </span>
                    <span class="text-lg flex items-center py-3">
                        <i class="fas fa-tag text-blue-600 text-xl mr-3"></i> <?= htmlspecialchars($fila['tipo_mantenimiento']) ?>
                    </span>
                </div>
            </td>

            <!-- Máquina -->
            <td class="px-6 py-4 text-center flex flex-col items-center">
                <?php if (!empty($fila['url_maquina'])): ?>
                    <img src="<?= htmlspecialchars($fila['url_maquina']) ?>" alt="<?= htmlspecialchars($fila['imagen_maquina']) ?>" 
                         class="w-20 h-20 object-cover rounded-md mb-2">
                <?php else: ?>
                    <span class="text-gray-500">Sin Imagen</span>
                <?php endif; ?>
                <span class="block text-sm text-gray-700"><?= htmlspecialchars($fila['nombre_maquina']) ?></span>
                <span class="block text-xs text-gray-500"><?= htmlspecialchars($fila['nombre_marca']) ?> | <?= htmlspecialchars($fila['nombre_modelo']) ?> | <?= htmlspecialchars($fila['codigo_maquina']) ?></span>
            </td>

            <!-- Ubicación -->
            <td class="px-6 py-4 items-center space-x-2">
                <i class="fas fa-map-marker-alt text-green-600"></i>
                <span><?= htmlspecialchars($fila['ubicacion_sede']) ?></span>
            </td>

            <!-- Responsables -->
            <td class="px-6 py-4 items-center space-x-2">
                <i class="fas fa-user-friends text-purple-600"></i>
                <span><?= htmlspecialchars($fila['responsables_asignados']) ?></span>
            </td>

            <!-- Estado -->
            <td class="px-6 py-4 text-center">
                <span class="px-2 py-1 rounded-md text-white <?= $color ?>">
                    <?= htmlspecialchars($fila['estado_tarea']) ?>
                </span>
            </td>

            <!-- Acciones -->
            <td class="px-6 py-4 text-center flex flex-col gap-2">
                <!-- Botón para ver tarea -->
                <button 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-all"
                    onclick="showModal(<?= htmlspecialchars($fila['id_tarea']) ?>)">
                    <i class="fas fa-eye"></i> Ver
                </button>

                <?php if ($fila['id_status'] == 1): ?>
                    <!-- Mostrar solo si está Activo -->
                <button 
    class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition-all iniciar-btn"
    data-id="<?= htmlspecialchars($fila['id_tarea']) ?>">
    <i class="fas fa-play"></i> Iniciar Mantenimiento
</button>

                <?php elseif ($fila['id_status'] == 5): ?>
                    <!-- Mostrar solo si está En Progreso -->
                    <button 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-all"
                        onclick="window.location.href='formulario_finalizar_mantenimiento.php?id=<?= htmlspecialchars($fila['id_tarea']) ?>'">
                        <i class="fas fa-check-circle"></i> Finalizar Tarea
                    </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php } ?>
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
    const nombreSede = document.getElementById('nombreSede').value;
    const status = document.getElementById('status').value;
    const sucursal = document.getElementById('sucursal').value;
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFinal = document.getElementById('fechaFinal').value;

    // Redirigir a la misma página con los filtros aplicados
    window.location.href = `?nombreSede=${encodeURIComponent(nombreSede)}&status=${status}&sucursal=${sucursal}&fechaInicio=${fechaInicio}&fechaFinal=${fechaFinal}`;
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

        if (rowContainsFilter) {
            rows[i].style.display = ""; // Mostrar la fila
        } else {
            rows[i].style.display = "none"; // Ocultar la fila
        }
    }
}

// Asignar evento al campo de búsqueda
document.getElementById('searchInput').addEventListener('keyup', filterTable);

  </script>
  <script>
  // Captura el evento de presionar una tecla
  document.getElementById('searchInput').addEventListener('keypress', function(event) {
    // Verifica si la tecla presionada es "Enter"
    if (event.key === 'Enter') {
      // Obtén el valor del input
      const searchValue = this.value;
      // Redirige a la misma página con el parámetro de búsqueda
      window.location.href = window.location.pathname + '?nombreSede=' + encodeURIComponent(searchValue);
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
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".iniciar-btn").forEach(button => {
        button.addEventListener("click", () => {
            const idTarea = button.getAttribute("data-id");

            fetch("actualizar_status.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `id_tarea=${idTarea}&nuevo_status=5`
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "ok") {
                    mostrarMensajeExito("Has iniciado el mantenimiento");
                    setTimeout(() => location.reload(), 3000);
                } else {
                    mostrarMensajeError("Error: " + data);
                }
            })
            .catch(error => {
                mostrarMensajeError("Error en la solicitud: " + error);
            });
        });
    });
});

function mostrarMensajeExito(texto) {
    const mensaje = document.createElement("div");
    mensaje.innerHTML = `
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black/30 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative animate-fade-in-down">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-green-100 p-4 rounded-full shadow-lg animate-pulse">
                        <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                    </div>
                </div>
                <div class="text-center">
                    <h2 class="text-xl font-bold text-green-600 mb-2">¡Éxito!</h2>
                    <p class="text-gray-700">${texto}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" 
                        class="absolute top-2 right-2 bg-green-500 hover:bg-green-600 text-white rounded-full p-2 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(mensaje);
}

function mostrarMensajeError(texto) {
    const mensaje = document.createElement("div");
    mensaje.innerHTML = `
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black/30 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-red-100 p-4 rounded-full shadow-lg animate-pulse">
                        <i class="fas fa-times-circle text-red-500 text-4xl"></i>
                    </div>
                </div>
                <div class="text-center">
                    <h2 class="text-xl font-bold text-red-600 mb-2">¡Error!</h2>
                    <p class="text-gray-700">${texto}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" 
                        class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(mensaje);
}
</script>

