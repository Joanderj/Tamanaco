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
$menu_actual = 3;

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

// Submenú actual: sucursal (id_submenu = 7)
$submenu_actual = 31;

// Verificar si el submenú "sucursal" está activo y si el perfil tiene permisos
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
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 9
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

// Consulta para obtener los submenús tipo 2 activos y permitidos para el menú actual
$sql_submenus_tipo_2 = "
    SELECT s.nombre_submenu, s.descripcion, s.url_submenu
    FROM submenus s
    INNER JOIN perfil_submenu ps ON s.id_submenu = ps.id_submenu
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 2 AND s.id_menu = ?
    ORDER BY s.id_submenu
";
$stmt_submenus_tipo_2 = $conexion->prepare($sql_submenus_tipo_2);
$stmt_submenus_tipo_2->bind_param("ii", $id_perfil, $menu_actual);
$stmt_submenus_tipo_2->execute();
$result_submenus_tipo_2 = $stmt_submenus_tipo_2->get_result();

$submenus_tipo_2 = [];
while ($submenu = $result_submenus_tipo_2->fetch_assoc()) {
    $submenus_tipo_2[] = $submenu;
}

// Variables para paginación
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Recuperar los filtros
$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$minimo = isset($_GET['minimo']) ? $_GET['minimo'] : '';
$almacen = isset($_GET['almacen'])? $_GET['almacen'] : '';

// Clasificación
$orderBy = 'id_inventario_repuesto';
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'cantidad_asc':
            $orderBy = 'cantidad ASC';
            break;
        case 'cantidad_desc':
            $orderBy = 'cantidad DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'nombre_repuesto ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'nombre_repuesto DESC';
            break;
        case 'nombre_almacen_asc':
            $orderBy = 'nombre ASC';
            break;
        case 'nombre_almacen_desc':
            $orderBy = 'nombre DESC';
            break;
    }
}

// Consulta base reutilizable
$baseQuery = "
    SELECT 
        ip.id_inventario_repuesto,
        a.nombre,
        p.nombre_repuesto,
        ip.cantidad,
        ip.stock_minimo,
        ip.costo_total,
        ip.id_almacen
    FROM inventario_repuesto ip 
    JOIN repuesto p ON ip.id_repuesto = p.id_repuesto
    JOIN almacen a ON ip.id_almacen = a.id_almacen
    WHERE 1=1
";

// Construir condiciones dinámicas
$conditions = "";
$params = [];

if (!empty($nombre)) {
    $conditions .= " AND p.nombre_repuesto LIKE ?";
    $params[] = '%' . $nombre . '%';
}

if (!empty($cantidad)) {
    $conditions .= " AND ip.cantidad = ?";
    $params[] = $cantidad;
}

if (!empty($cantidadminima)) {
    $conditions .= " AND ip.stock_minimo = ?";
    $params[] = $cantidadminima;
}

if (!empty($costo)) {
    $conditions .= " AND ip.costo_total = ?";
    $params[] = $costo;
}

if (!empty($almacen)) {
    $conditions .= " AND a.id_almacen = ?";
    $params[] = $almacen;
}

// Consulta para total de items (paginación)
$totalQuery = "SELECT COUNT(*) FROM (" . $baseQuery . $conditions . ") AS total";
$totalStmt = $conexion->prepare($totalQuery);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $totalStmt->bind_param($types, ...$params);
}

if (!$totalStmt->execute()) {
    die("Error al contar items: " . $totalStmt->error);
}

$totalItems = $totalStmt->get_result()->fetch_row()[0];
$totalPages = ceil($totalItems / $itemsPerPage);

// Consulta principal con paginación
$query = $baseQuery . $conditions . " ORDER BY " . $orderBy . " LIMIT ?, ?";
$paramsPaginated = $params;
$paramsPaginated[] = $offset;
$paramsPaginated[] = $itemsPerPage;

$stmt = $conexion->prepare($query);

if (!empty($paramsPaginated)) {
    $types = str_repeat('s', count($paramsPaginated));
    $stmt->bind_param($types, ...$paramsPaginated);
}

if (!$stmt->execute()) {
    die("Error al obtener inventario: " . $stmt->error);
}

$result = $stmt->get_result();
$inventario = $result->fetch_all(MYSQLI_ASSOC);

// Guardar Filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_filter') {
    $filterName = $_POST['filterName'];
    
    // Crear criterios como un JSON
    $criterios = json_encode([
        'nombre' => $nombre,
        'status' => $status,
        'fechaInicio' => $fechaInicio,
        'fechaFinal' => $fechaFinal,
    ]);

    $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado, usuario_id_filtro) 
              VALUES (?, 'sucursal', ?, NOW(), ?)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ssi", $filterName, $criterios, $id_perfil);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}

// Cargar Filtros Guardados
$sql_cargar_filtros = "
    SELECT nombre_filtro, criterios 
    FROM filtros_guardados 
    WHERE usuario_id_filtro = ?
";
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
       <!-- js -->

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


<!-- Contenedor principal -->

 <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
      <div class="space-x-2">
        <button  onclick="window.location.href='actualizar_inventario_repuesto.php'" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
          <i class="fas fa-sync-alt"></i> Actualizar Inventario
        </button>
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
        <!-- Campo: Nombre de repuesto -->
        <div class="mb-4">
            <label for="nombre" class="block text-gray-700 mb-2">Nombre del Repuesto</label>
            <input 
              type="text" 
              id="nombre" 
              class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
              placeholder="Escribe el nombre del repuesto..."
            />
        </div>

<!-- Campo: Status y pais Juntos -->
<div class="grid grid-cols-2 gap-4 mb-4">
            <!-- Status -->
            <div class="mb-4">
            <label for="minimo" class="block text-gray-700 mb-2">Cantidad Minima</label>
            <input 
              type="text" 
              id="minimo" 
              class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" 
              placeholder="Escribe la Cantidad Minima..."
            />
        </div>
            <!-- pais -->
            <div>
                <label for="almacen" class="block text-gray-700 mb-2">Almacen</label>
                <select 
                  id="almacen" 
                  name="almacen"
                  class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Selecciona un Almacen</option>
                    <?php
                    // Conexión a la base de datos
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");

                    // Comprobar conexión
                    if ($conexion->connect_error) {
                        die("Conexión fallida: " . $conexion->connect_error);
                    }

                    // Consultar las pais
                    $query = "SELECT id_almacen, nombre FROM almacen ORDER BY nombre ASC";
                    $result = $conexion->query($query);

                    // Generar opciones dinámicamente
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id_almacen = htmlspecialchars($row["id_almacen"]);
                            $nombre = htmlspecialchars($row["nombre"]);
                            echo "<option value='$id_almacen'>$nombre</option>";
                        }
                    } else {
                        echo "<option value=''>No hay estados disponibles</option>";
                    }

                    // Cerrar conexión
                    $conexion->close();
                    ?>
                </select>
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
        <h1 class="text-xl font-semibold text-gray-700">Inventario de repuesto</h1>
        <div class="relative">
            <select id="clasificacion" 
                    onchange="window.location.href='?sort=' + this.value + '&itemsPerPage=<?php echo $itemsPerPage; ?>&page=1'" 
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center appearance-none">
                <option value="" disabled selected>Clasificar</option>
                <option value="normal">Normal</option>
                <option value="cantidad_asc">Por Menor Cantidad</option>
                <option value="cantidad_desc">Por Mayor Cantidad</option>
                <option value="nombre_asc">Por Nombre de Repuesto (A-Z)</option>
                <option value="nombre_desc">Por Nombre de Repuesto (Z-A)</option>
                <option value="nombre_almacen_asc">Por Nombre del Almacen (A-Z)</option>
                <option value="nombre_almacen_desc">Por Nombre del Almacen (Z-A)</option>
            </select>
            <i class="fas fa-sort absolute right-4 top-3 text-gray-700 pointer-events-none"></i>
        </div>
    </div>
    
    <!-- Tabla HTML mejorada -->
<div class="overflow-x-auto">
    <table class="table-auto w-full border-collapse border border-gray-300 shadow-lg rounded-lg">
        <thead class="bg-indigo-500 text-white">
            <tr>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-hashtag"></i>
                        <span>ID</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-box"></i>
                        <span>Repuesto</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-cubes"></i>
                        <span>Cantidad</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Mínimo</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Costo</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-warehouse"></i>
                        <span>Almacén</span>
                    </span>
                </th>
                <th class="px-6 py-3 text-left font-medium">
                    <span class="flex items-center space-x-2">
                        <i class="fas fa-tools"></i>
                        <span>Acciones</span>
                    </span>
                </th>
            </tr>
            </thead>
            <tbody id="dataTable" class="bg-white divide-y divide-gray-200">
            <?php foreach ($inventario as $item): ?>
            <tr class="hover:bg-gray-100 <?= $item['cantidad'] < $item['stock_minimo'] ? 'bg-red-50' : '' ?>">
                <td class="px-6 py-4"><?= htmlspecialchars($item['id_inventario_repuesto']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($item['nombre_repuesto']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($item['cantidad']) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($item['stock_minimo']) ?></td>
                <td class="px-6 py-4">$<?= number_format($item['costo_total'], 2) ?></td>
                <td class="px-6 py-4"><?= htmlspecialchars($item['nombre']) ?></td>
                <td class="px-6 py-4 flex space-x-2">
                    <button onclick="verDetalle(<?= $item['id_inventario_repuesto'] ?>)" 
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                        <i class="fas fa-eye"></i> Ver
                    </button>
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

</body>
</html>



