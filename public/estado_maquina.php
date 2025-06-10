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

    <!-- Encabezado con título y opciones -->
    <div class="flex justify-between items-center mb-4 border-b border-gray-300 pb-2">
        <h1 class="text-xl font-semibold text-gray-700">Máquinas</h1>

        <!-- Clasificador -->
        <div class="relative">
            <select id="clasificacion" 
                    onchange="window.location.href='?sort=' + this.value + '&itemsPerPage=<?php echo $itemsPerPage; ?>&page=1'" 
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none">
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

  <?php
include 'db_connection.php';
date_default_timezone_set('America/Caracas');

$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Consulta total para paginación
$countQuery = "SELECT COUNT(*) FROM maquina_unica";
$stmt = $conn->prepare($countQuery);
$stmt->execute();
$totalItems = $stmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta principal con JOIN a estatus y tareas en progreso
$query = "
    SELECT 
        mu.id_maquina_unica,
        mu.CodigoUnico,
        mu.Almacen,
        mu.id_sede,
        mu.id_status AS status_maquina_unica,
        e.nombre_status AS nombre_status_maquina_unica,
        mu.FechaUltimaActualizacion,

        m.id_maquina,
        m.nombre_maquina,
        m.descripcion_funcionamiento,
        m.elaborada_por,
        m.id_marca,
        m.id_modelo,
        m.id_tipo,
        m.sugerencia_mantenimiento,
        m.nombre_imagen,
        m.url,
        m.id_status AS status_maquina,
        m.date_created,

        t.id_tarea
    FROM maquina_unica mu
    LEFT JOIN maquina m ON mu.id_maquina = m.id_maquina
    LEFT JOIN status e ON mu.id_status = e.id_status
    LEFT JOIN tareas t ON mu.id_maquina_unica = t.id_maquina_unica AND t.status_id = 5
    ORDER BY mu.id_maquina_unica ASC
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$maquinasUnicas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Tabla de máquinas -->
<div class="overflow-x-auto">
    <table class="table-auto w-full border border-collapse">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="border px-4 py-2">Código Único</th>
                <th class="border px-4 py-2">Nombre Máquina</th>
                <th class="border px-4 py-2">Estatus</th>
                <th class="border px-4 py-2">Imagen</th>
                <th class="border px-4 py-2">Actualización</th>
                <th class="border px-4 py-2">Acción</th>
            </tr>
        </thead>
        <tbody class="text-gray-800">
            <?php foreach ($maquinasUnicas as $maquina): ?>
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2"><?= htmlspecialchars($maquina['CodigoUnico']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($maquina['nombre_maquina']) ?></td>
                    <td class="border px-4 py-2">
                        <span class="px-2 py-1 rounded-full text-white text-sm
                            <?= $maquina['nombre_status_maquina_unica'] === 'Disponible' ? 'bg-green-500' :
                                ($maquina['nombre_status_maquina_unica'] === 'En mantenimiento' ? 'bg-yellow-500' :
                                'bg-gray-500') ?>">
                            <?= htmlspecialchars($maquina['nombre_status_maquina_unica']) ?>
                        </span>
                    </td>
                    <td class="border px-4 py-2 text-center">
                        <?php if ($maquina['url']): ?>
                            <img src="<?= htmlspecialchars($maquina['url']) ?>" alt="imagen" class="w-20 h-20 object-cover rounded">
                        <?php else: ?>
                            <span class="text-gray-500 text-sm">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($maquina['FechaUltimaActualizacion']) ?></td>
                    <td class="border px-4 py-2 text-center">
                        <?php if ((int)$maquina['status_maquina_unica'] === 13 && $maquina['id_tarea']): ?>
                            <!-- Botón Activar -->
                            <form method="POST" action="activar_maquina.php">
                                <input type="hidden" name="id_maquina_unica" value="<?= $maquina['id_maquina_unica'] ?>">
                                <input type="hidden" name="id_tarea" value="<?= $maquina['id_tarea'] ?>">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Activar</button>
                            </form>
                        <?php elseif ((int)$maquina['status_maquina_unica'] === 1 && $maquina['id_tarea']): ?>
                            <!-- Botón Parar -->
                            <form method="POST" action="parar_maquina.php">
                                <input type="hidden" name="id_maquina_unica" value="<?= $maquina['id_maquina_unica'] ?>">
                                <input type="hidden" name="id_tarea" value="<?= $maquina['id_tarea'] ?>">
                                <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Parar</button>
                            </form>
                        <?php else: ?>
                            <span class="text-gray-400">No disponible</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <!-- Paginación -->
    <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Página <?= $currentPage ?> de <?= $totalPages ?>
        </div>
        <div class="space-x-2">
            <?php if ($currentPage > 1): ?>
                <a href="?page=1&itemsPerPage=<?= $itemsPerPage ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Primera</a>
                <a href="?page=<?= $currentPage - 1 ?>&itemsPerPage=<?= $itemsPerPage ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Anterior</a>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&itemsPerPage=<?= $itemsPerPage ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Siguiente</a>
                <a href="?page=<?= $totalPages ?>&itemsPerPage=<?= $itemsPerPage ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Última</a>
            <?php endif; ?>
        </div>
    </div>
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