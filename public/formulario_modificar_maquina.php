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
// Variables para paginación
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Recuperar los filtros
$nombreProducto = isset($_GET['nombreProducto']) ? $_GET['nombreProducto'] : '';
$idMarca = isset($_GET['idMarca']) ? (int)$_GET['idMarca'] : '';
$idModelo = isset($_GET['idModelo']) ? (int)$_GET['idModelo'] : '';
$idTipo = isset($_GET['idTipo']) ? (int)$_GET['idTipo'] : '';
$idClasificacion = isset($_GET['idClasificacion']) ? (int)$_GET['idClasificacion'] : '';
$fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
$fechaFinal = isset($_GET['fechaFinal']) ? $_GET['fechaFinal'] : '';
$status = isset($_GET['status']) ? (int)$_GET['status'] : '';

// Clasificación
$orderBy = 'p.id_producto'; // Prefijo añadido para evitar ambigüedad
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'fecha_asc':
            $orderBy = 'p.date_created ASC';
            break;
        case 'fecha_desc':
            $orderBy = 'p.date_created DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'p.nombre_producto ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'p.nombre_producto DESC';
            break;
        case 'numero_asc':
            $orderBy = 'p.id_producto ASC';
            break;
        case 'numero_desc':
            $orderBy = 'p.id_producto DESC';
            break;
    }
}

// Consulta total de elementos
$totalQuery = "SELECT COUNT(*) FROM producto p WHERE 1=1";
$params = [];

if (!empty($nombreProducto)) {
    $totalQuery .= " AND p.nombre_producto LIKE ?";
    $params[] = '%' . $nombreProducto . '%'; // Agregar wildcard para búsqueda
}

if (!empty($idMarca)) {
    $totalQuery .= " AND p.id_marca = ?";
    $params[] = $idMarca;
}

if (!empty($idModelo)) {
    $totalQuery .= " AND p.id_modelo = ?";
    $params[] = $idModelo; // Prefijo añadido
}

if (!empty($idTipo)) {
    $totalQuery .= " AND p.id_tipo = ?";
    $params[] = $idTipo;
}

if (!empty($idClasificacion)) {
    $totalQuery .= " AND p.id_clasificacion = ?";
    $params[] = $idClasificacion;
}

// Filtro por estado
if (!empty($status)) {
    $totalQuery .= " AND p.id_status = ?";
    $params[] = $status;
}

// Filtro por fechas
if (!empty($fechaInicio)) {
    $totalQuery .= " AND p.date_created >= ?";
    $params[] = $fechaInicio;
}
if (!empty($fechaFinal)) {
    $totalQuery .= " AND p.date_created <= ?";
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

// Consulta de datos
$query = "SELECT 
            p.id_producto, 
            p.nombre_producto, 
            m.nombre_marca, 
            mo.nombre_modelo, 
            t.nombre_tipo, 
            c.nombre_clasificacion, 
            p.unidad_medida, 
            p.nombre_imagen, 
            p.url, 
            s.nombre_status, 
            p.date_created 
          FROM producto p
          LEFT JOIN marca m ON p.id_marca = m.id_marca
          LEFT JOIN modelo mo ON p.id_modelo = mo.id_modelo
          LEFT JOIN tipo t ON p.id_tipo = t.id_tipo
          LEFT JOIN clasificacion c ON p.id_clasificacion = c.id_clasificacion
          LEFT JOIN status s ON p.id_status = s.id_status
          WHERE 1=1";

if (!empty($nombreProducto)) {
    $query .= " AND p.nombre_producto LIKE ?";
}

if (!empty($idMarca)) {
    $query .= " AND p.id_marca = ?";
}

if (!empty($idModelo)) {
    $query .= " AND p.id_modelo = ?";
}

if (!empty($idTipo)) {
    $query .= " AND p.id_tipo = ?";
}

if (!empty($idClasificacion)) {
    $query .= " AND p.id_clasificacion = ?";
}

if (!empty($status)) {
    $query .= " AND p.id_status = ?";
}

if (!empty($fechaInicio)) {
    $query .= " AND p.date_created >= ?";
}

if (!empty($fechaFinal)) {
    $query .= " AND p.date_created <= ?";
}

// Agregar orden y límites
$query .= " ORDER BY $orderBy LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Preparar y ejecutar la consulta
$stmt = $conexion->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$productos = $result->fetch_all(MYSQLI_ASSOC);

// Guardar Filtros
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_filter') {
    $filterName = $_POST['filterName'];

    // Crear criterios como un JSON
    $criterios = json_encode([
        'nombreProducto' => $nombreProducto,
        'idMarca' => $idMarca,
        'idModelo' => $idModelo,
        'idTipo' => $idTipo,
        'idClasificacion' => $idClasificacion,
        'status' => $status,
        'fechaInicio' => $fechaInicio,
        'fechaFinal' => $fechaFinal,
    ]);

    $query = "INSERT INTO filtros_guardados (nombre_filtro, tabla_destino, criterios, fecha_guardado, usuario_id_filtro) 
              VALUES (?, 'producto', ?, NOW(), ?)";

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
        <i class="fas fa-tools text-3xl"></i> <!-- Ícono de herramientas -->
    </div>
    <!-- Título del formulario -->
    <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Máquina</h2>
    <!-- Descripción del formulario -->
    <p class="text-gray-600 mt-2 text-center">Registra las máquinas y sus especificaciones técnicas de forma organizada y profesional.</p>
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
// Conexión a la base de datos
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener ID desde GET
$id_maquina = isset($_GET['id_maquina']) ? intval($_GET['id_maquina']) : 0;
if ($id_maquina <= 0) {
    die("ID de máquina no válido.");
}

// Consultar datos de la máquina con JOIN optimizados
$consulta = $conexion->prepare("
    SELECT m.id_maquina, m.color, m.nombre_maquina, m.descripcion_funcionamiento, m.elaborada_por,
           marca.id_marca, marca.nombre_marca, modelo.id_modelo, modelo.nombre_modelo,
           tipo.id_tipo, tipo.nombre_tipo, m.nombre_imagen, m.url, m.color, m.id_status, m.date_created
    FROM maquina m
    LEFT JOIN marca ON m.id_marca = marca.id_marca
    LEFT JOIN modelo ON m.id_modelo = modelo.id_modelo
    LEFT JOIN tipo ON m.id_tipo = tipo.id_tipo
    WHERE m.id_maquina = ?
");

$consulta->bind_param("i", $id_maquina);
$consulta->execute();
$resultado = $consulta->get_result();
$maquina = $resultado->fetch_assoc();

if (!$maquina) {
    die("Máquina no encontrada.");
}

// Consultar características de la máquina
$caracteristicas = [];
$caracQuery = $conexion->prepare("SELECT nombre_caracteristica, descripcion_caracteristica FROM caracteristicas_maquina WHERE id_maquina = ?");
$caracQuery->bind_param("i", $id_maquina);
$caracQuery->execute();
$resCaracteristicas = $caracQuery->get_result();
while ($row = $resCaracteristicas->fetch_assoc()) {
    $caracteristicas[] = $row;
}

// Consultar especificaciones de la máquina
$especificaciones = [];
$especQuery = $conexion->prepare("SELECT nombre_especificacion, descripcion_especificacion FROM especificaciones_maquina WHERE id_maquina = ?");
$especQuery->bind_param("i", $id_maquina);
$especQuery->execute();
$resEspecificaciones = $especQuery->get_result();
while ($row = $resEspecificaciones->fetch_assoc()) {
    $especificaciones[] = $row;
}

// Cierre de conexión
$consulta->close();
$caracQuery->close();
$especQuery->close();

?>
    <form id="machineForm" action="modificar_maquina.php" method="post" enctype="multipart/form-data">
 <input type="hidden" name="id_maquina" value="<?php echo $maquina['id_maquina']; ?>">

 <!-- Imagen -->
    <div class="text-center mb-6">
        <label for="imagen" class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center cursor-pointer group overflow-hidden">
            <input type="file" id="imagen" name="nombre_imagen" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="mostrarPreview()" />
            <img id="imagen-preview"
                 src="<?= $maquina['url'] ?? '' ?>"
                 alt="Imagen actual"
                 class="absolute inset-0 w-full h-full object-cover rounded-lg <?= !empty($maquina['url']) ? '' : 'hidden' ?>" />
            <div id="imagen-placeholder"
                 class="text-center absolute inset-0 flex flex-col items-center justify-center text-blue-500 bg-white/60 group-hover:bg-white/70 transition <?= empty($maquina['url_imagen']) ? '' : 'hidden' ?>">
                <i class="fas fa-tools text-3xl"></i>
                <p class="font-medium">Haga clic para subir una nueva foto</p>
                <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
            </div>
        </label>
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
   <!-- Nombre -->
<div class="mb-4">
    <label for="nombre_maquina" class="block font-semibold flex items-center">
        <i class="fas fa-cogs text-green-500 mr-2"></i> Nombre de la Máquina: <span class="text-red-600">*</span>
    </label>
    <input type="text" id="nombre_maquina" name="nombre_maquina" class="w-full border border-gray-300 rounded-lg p-2" value="<?= htmlspecialchars($maquina['nombre_maquina']) ?>" required>
</div>

<!-- Descripción -->
<div class="mb-4">
    <label for="descripcion_funcionamiento" class="block font-semibold flex items-center">
        <i class="fas fa-info-circle text-blue-500 mr-2"></i> Descripción de Funcionamiento:
    </label>
    <input type="hidden" id="descripcion_funcionamiento" name="descripcion_funcionamiento" value="<?= htmlspecialchars($maquina['descripcion_funcionamiento']) ?>">
    <div id="editor" class="w-full border border-gray-300 p-2 min-h-[150px] bg-white"><?= $maquina['descripcion_funcionamiento'] ?></div>
</div>

<script src="js/quill.min.js"></script>
<script>
    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Establecer el contenido inicial
    quill.root.innerHTML = document.getElementById('descripcion_funcionamiento').value;

    // Al enviar el formulario, guardar el contenido del editor
    document.getElementById('machineForm').onsubmit = function () {
        document.getElementById('descripcion_funcionamiento').value = quill.root.innerHTML;
        return true;
    };
</script>

<!-- Elaborada por -->
<div class="mb-4">
    <label for="elaborada_por" class="block font-semibold flex items-center">
        <i class="fas fa-user-tie text-purple-500 mr-2"></i> Elaborada por:
    </label>
    <input type="text" id="elaborada_por" name="elaborada_por" class="w-full border border-gray-300 rounded-lg p-2" value="<?= htmlspecialchars($maquina['elaborada_por']) ?>">
</div>

<!-- Marca -->
<div class="mb-4">
    <label for="marca" class="block font-semibold flex items-center">
        <i class="fas fa-industry text-orange-500 mr-2"></i> Marca: <span class="text-red-600">*</span>
    </label>
    <select id="marca" name="marca" onchange="cargarModelos(true)" class="w-full border border-gray-300 rounded-lg p-2" required>
        <option value="" disabled>Seleccione una marca</option>
        <?php
        $resultado = $conexion->query("SELECT id_marca, nombre_marca FROM marca");
        while ($fila = $resultado->fetch_assoc()) {
            $selected = ($fila['id_marca'] == $maquina['id_marca']) ? 'selected' : '';
            echo "<option value='{$fila['id_marca']}' $selected>{$fila['nombre_marca']}</option>";
        }
        ?>
    </select>
</div>

<!-- Modelo -->
<div class="mb-4">
    <label for="modelo" class="block font-semibold flex items-center">
        <i class="fas fa-cube text-yellow-500 mr-2"></i> Modelo: <span class="text-red-600">*</span>
    </label>
    <select id="modelo" name="modelo" class="w-full border border-gray-300 rounded-lg p-2" required>
        <option value="" disabled>Seleccione un modelo</option>
    <?php
$modeloQuery = $conexion->query("
    SELECT mm.id_modelo, m.nombre_modelo
    FROM marca_modelo mm
    INNER JOIN modelo m ON mm.id_modelo = m.id_modelo
    WHERE mm.id_marca = {$maquina['id_marca']}
");

while ($fila = $modeloQuery->fetch_assoc()) {
    $selected = ($fila['id_modelo'] == $maquina['id_modelo']) ? 'selected' : '';
    echo "<option value='{$fila['id_modelo']}' $selected>{$fila['nombre_modelo']}</option>";
}
?>
    </select>
</div>

<!-- Tipo -->
<div class="mb-4">
    <label for="tipo" class="block font-semibold flex items-center">
        <i class="fas fa-layer-group text-teal-500 mr-2"></i> Tipo:
    </label>
    <select id="tipo" name="tipo" class="w-full border border-gray-300 rounded-lg p-2">
        <option value="">Seleccionar</option>
        <?php
        $resultado = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo WHERE id_status = 1");
        while ($fila = $resultado->fetch_assoc()) {
            $selected = ($fila['id_tipo'] == $maquina['id_tipo']) ? 'selected' : '';
            echo "<option value='{$fila['id_tipo']}' $selected>{$fila['nombre_tipo']}</option>";
        }
        ?>
    </select>
</div>

<!-- Sugerencia mantenimiento -->
<div class="mb-4">
    <label for="sugerencia_mantenimiento" class="block font-semibold flex items-center">
        <i class="fas fa-calendar-check text-indigo-500 mr-2"></i> Sugerencia de Mantenimiento:
    </label>
    <select id="sugerencia_mantenimiento" name="sugerencia_mantenimiento" class="w-full border border-gray-300 rounded-lg p-2" required>
        <?php
// Array de frecuencias predefinidas
$frecuencias = ['diario', 'semanal', 'quincenal', 'mensual', 'bimestral', 'trimestral', 'cuatrimestral', 'semestral', 'anual', 'bienal', 'trienal', 'quinquenal'];

// Validar que el valor de $maquina['sugerencia_mantenimiento'] exista y coincida con las opciones
$sugerencia = isset($maquina['sugerencia_mantenimiento']) ? strtolower(trim($maquina['sugerencia_mantenimiento'])) : '';

foreach ($frecuencias as $freq) {
    $selected = ($sugerencia === $freq) ? 'selected' : '';
    echo "<option value='$freq' $selected>" . ucfirst($freq) . "</option>";
}
?>
    </select>
</div>


<?php
$caracteristicas = [];

$consultaCaracteristicas = $conexion->prepare("
    SELECT id_caracteristica, nombre_caracteristica, descripcion_caracteristica 
    FROM caracteristicas_maquina 
    WHERE id_maquina = ?
");
$consultaCaracteristicas->bind_param("i", $id_maquina);
$consultaCaracteristicas->execute();
$resultado = $consultaCaracteristicas->get_result();

while ($fila = $resultado->fetch_assoc()) {
    $caracteristicas[] = $fila;
}

$consultaCaracteristicas->close();
?>
<div class="mb-6">
  <label class="block text-lg font-semibold mb-2 flex items-center text-gray-800">
    <i class="fas fa-cogs text-green-600 mr-2"></i>
    Características registradas de la máquina
  </label>

  <div id="caracteristicas-container" class="space-y-4">
    <?php foreach ($caracteristicas as $car): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 caracteristica-item bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 relative">

        <!-- ID oculta de la característica -->
        <input type="hidden" name="id_caracteristica[]" value="<?= $car['id_caracteristica'] ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de característica:</label>
          <input type="text" name="nombres_caracteristica[]" class="w-full border border-gray-300 rounded-lg p-2 text-sm" value="<?= htmlspecialchars($car['nombre_caracteristica']) ?>" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción:</label>
          <input type="text" name="descripciones_caracteristica[]" class="w-full border border-gray-300 rounded-lg p-2 text-sm" value="<?= htmlspecialchars($car['descripcion_caracteristica']) ?>">
        </div>

        <div class="absolute top-2 right-2">
          <button type="button" class="text-red-600 hover:text-red-800" onclick="this.closest('.caracteristica-item').remove()">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="mb-4">
    <label class="block font-semibold mb-2 flex items-center">
        <i class="fas fa-cogs text-green-500 mr-2"></i>
        Características de la máquina:
    </label>
    
<div id="caracteristicas-container">
    <!-- Aquí se insertan los bloques de características -->
</div>

<div class="mt-4">
     <button type="button" onclick="agregarCaracteristica()" class="text-white bg-blue-600 px-3 py-1 rounded hover:bg-blue-700 mt-2">
        <i class="fas fa-plus"></i> Agregar otra característica
    </button>
</div>

<template id="caracteristica-template">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2 caracteristica-item relative">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de característica:</label>
            <select name="nombres_caracteristica[]" class="w-full border border-gray-300 rounded-lg p-2" required>
                    <option value="" disabled selected>Seleccione una característica</option>
<option value="Adaptabilidad">Adaptabilidad</option>
<option value="Alcance operativo">Alcance operativo</option>
<option value="Altura ajustable">Altura ajustable</option>
<option value="Amortiguación de vibraciones">Amortiguación de vibraciones</option>
<option value="Anticorrosión">Anticorrosión</option>
<option value="Autonomía">Autonomía</option>
<option value="Calibración">Calibración</option>
<option value="Capacidad">Capacidad</option>
<option value="Certificaciones">Certificaciones</option>
<option value="Clasificación de riesgo">Clasificación de riesgo</option>
<option value="Color">Color</option>
<option value="Compatibilidad">Compatibilidad</option>
<option value="Composición">Composición</option>
<option value="Conectividad">Conectividad</option>
<option value="Consumo de aire">Consumo de aire</option>
<option value="Consumo eléctrico">Consumo eléctrico</option>
<option value="Control de temperatura">Control de temperatura</option>
<option value="Control remoto">Control remoto</option>
<option value="Corriente de operación">Corriente de operación</option>
<option value="Dimensiones">Dimensiones</option>
<option value="Durabilidad">Durabilidad</option>
<option value="Eficiencia energética">Eficiencia energética</option>
<option value="Estabilidad">Estabilidad</option>
<option value="Facilidad de limpieza">Facilidad de limpieza</option>
<option value="Facilidad de uso">Facilidad de uso</option>
<option value="Factor de potencia">Factor de potencia</option>
<option value="Frecuencia de operación">Frecuencia de operación</option>
<option value="Función de autoapagado">Función de autoapagado</option>
<option value="Garantía">Garantía</option>
<option value="Impermeabilidad">Impermeabilidad</option>
<option value="Indicadores LED">Indicadores LED</option>
<option value="Integración con sistemas">Integración con sistemas</option>
<option value="Intensidad de corriente">Intensidad de corriente</option>
<option value="Largo del cable">Largo del cable</option>
<option value="Lugar de fabricación">Lugar de fabricación</option>
<option value="Maniobrabilidad">Maniobrabilidad</option>
<option value="Material">Material</option>
<option value="Memoria interna">Memoria interna</option>
<option value="Método de enfriamiento">Método de enfriamiento</option>
<option value="Nivel de ruido">Nivel de ruido</option>
<option value="Peso">Peso</option>
<option value="Potencia">Potencia</option>
<option value="Precisión">Precisión</option>
<option value="Presión de operación">Presión de operación</option>
<option value="Protección térmica">Protección térmica</option>
<option value="Puerto de conexión">Puerto de conexión</option>
<option value="Resistencia al agua">Resistencia al agua</option>
<option value="Resistencia al calor">Resistencia al calor</option>
<option value="Resistencia a impactos">Resistencia a impactos</option>
<option value="Requiere mantenimiento">Requiere mantenimiento</option>
<option value="Rendimiento">Rendimiento</option>
<option value="Robustez">Robustez</option>
<option value="Rotación">Rotación</option>
<option value="Seguridad">Seguridad</option>
<option value="Sistema de enfriamiento">Sistema de enfriamiento</option>
<option value="Sistema de monitoreo">Sistema de monitoreo</option>
<option value="Sistema hidráulico">Sistema hidráulico</option>
<option value="Soporte técnico">Soporte técnico</option>
<option value="Tamaño">Tamaño</option>
<option value="Tensión">Tensión</option>
<option value="Tiempo de respuesta">Tiempo de respuesta</option>
<option value="Tipo de alimentación">Tipo de alimentación</option>
<option value="Tipo de conexión">Tipo de conexión</option>
<option value="Tipo de control">Tipo de control</option>
<option value="Tipo de enfriamiento">Tipo de enfriamiento</option>
<option value="Tipo de motor">Tipo de motor</option>
<option value="Tipo de uso">Tipo de uso</option>
<option value="Tolerancia térmica">Tolerancia térmica</option>
<option value="Transmisión">Transmisión</option>
<option value="Ubicación de operación">Ubicación de operación</option>
<option value="Uso continuo">Uso continuo</option>
<option value="Velocidad">Velocidad</option>
<option value="Voltaje">Voltaje</option>
<option value="Adaptabilidad">Adaptabilidad</option>
<option value="Alcance operativo">Alcance operativo</option>
<option value="Altura ajustable">Altura ajustable</option>
<option value="Amperaje">Amperaje</option>
<option value="Antivibración">Antivibración</option>
<option value="Autonomía">Autonomía</option>
<option value="Calibración automática">Calibración automática</option>
<option value="Capacidad de carga">Capacidad de carga</option>
<option value="Capacidad térmica">Capacidad térmica</option>
<option value="Clase de aislamiento">Clase de aislamiento</option>
<option value="Compatibilidad de software">Compatibilidad de software</option>
<option value="Control remoto">Control remoto</option>
<option value="Controladores integrados">Controladores integrados</option>
<option value="Corrosión resistente">Corrosión resistente</option>
<option value="Ciclo de trabajo">Ciclo de trabajo</option>
<option value="Diagnóstico automático">Diagnóstico automático</option>
<option value="Eficiencia térmica">Eficiencia térmica</option>
<option value="Estabilidad">Estabilidad</option>
<option value="Factor de potencia">Factor de potencia</option>
<option value="Fuerza de presión">Fuerza de presión</option>
<option value="Grado de automatización">Grado de automatización</option>
<option value="Humedad máxima soportada">Humedad máxima soportada</option>
<option value="Interfaz de usuario">Interfaz de usuario</option>
<option value="Mantenimiento requerido">Mantenimiento requerido</option>
<option value="Memoria interna">Memoria interna</option>
<option value="Modularidad">Modularidad</option>
<option value="Nivel de vibración">Nivel de vibración</option>
<option value="Par de torque">Par de torque</option>
<option value="Precisión">Precisión</option>
<option value="Presión máxima">Presión máxima</option>
<option value="Programable">Programable</option>
<option value="Protección contra sobrecarga">Protección contra sobrecarga</option>
<option value="Refrigeración">Refrigeración</option>
<option value="Resistencia al polvo">Resistencia al polvo</option>
<option value="Rendimiento">Rendimiento</option>
<option value="Rango de temperatura operativa">Rango de temperatura operativa</option>
<option value="Sensorización">Sensorización</option>
<option value="Sistema de enfriamiento">Sistema de enfriamiento</option>
<option value="Soporte técnico">Soporte técnico</option>
<option value="Tecnología de fabricación">Tecnología de fabricación</option>
<option value="Tensión eléctrica">Tensión eléctrica</option>
<option value="Tiempo de arranque">Tiempo de arranque</option>
<option value="Tipo de alimentación">Tipo de alimentación</option>
<option value="Tipo de transmisión">Tipo de transmisión</option>
<option value="Vida útil estimada">Vida útil estimada</option>
<option value="Aislamiento acústico">Aislamiento acústico</option>
<option value="Alarma de fallos">Alarma de fallos</option>
<option value="Autoapagado">Autoapagado</option>
<option value="Capacidad de producción">Capacidad de producción</option>
<option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
<option value="Carga máxima">Carga máxima</option>
<option value="Conectividad Wi-Fi">Conectividad Wi-Fi</option>
<option value="Conectividad Bluetooth">Conectividad Bluetooth</option>
<option value="Consumo de agua">Consumo de agua</option>
<option value="Control de velocidad">Control de velocidad</option>
<option value="Control de temperatura">Control de temperatura</option>
<option value="Control por voz">Control por voz</option>
<option value="Corte automático de energía">Corte automático de energía</option>
<option value="Desempeño bajo carga">Desempeño bajo carga</option>
<option value="Diagnóstico remoto">Diagnóstico remoto</option>
<option value="Eficiencia operativa">Eficiencia operativa</option>
<option value="Enfriamiento pasivo">Enfriamiento pasivo</option>
<option value="Estándar de fabricación">Estándar de fabricación</option>
<option value="Filtro de aire">Filtro de aire</option>
<option value="Filtro de partículas">Filtro de partículas</option>
<option value="Frecuencia de operación">Frecuencia de operación</option>
<option value="Indicador de mantenimiento">Indicador de mantenimiento</option>
<option value="Inteligencia artificial integrada">Inteligencia artificial integrada</option>
<option value="Lubricación automática">Lubricación automática</option>
<option value="Modo de ahorro energético">Modo de ahorro energético</option>
<option value="Modo de emergencia">Modo de emergencia</option>
<option value="Nivel de protección IP">Nivel de protección IP</option>
<option value="Operación continua">Operación continua</option>
<option value="Pantalla táctil">Pantalla táctil</option>
<option value="Protección contra sobrecalentamiento">Protección contra sobrecalentamiento</option>
<option value="Protección contra polvo y agua">Protección contra polvo y agua</option>
<option value="Puerto USB">Puerto USB</option>
<option value="Reciclaje de residuos">Reciclaje de residuos</option>
<option value="Reducción de emisiones">Reducción de emisiones</option>
<option value="Resistencia a químicos">Resistencia a químicos</option>
<option value="Retroalimentación háptica">Retroalimentación háptica</option>
<option value="Sistema anticaídas">Sistema anticaídas</option>
<option value="Sistema de monitoreo remoto">Sistema de monitoreo remoto</option>
<option value="Sistema de respaldo de energía">Sistema de respaldo de energía</option>
<option value="Sistema neumático">Sistema neumático</option>
<option value="Sistema hidráulico">Sistema hidráulico</option>
<option value="Sistema modular">Sistema modular</option>
<option value="Soporte multilenguaje">Soporte multilenguaje</option>
<option value="Tiempo de respuesta">Tiempo de respuesta</option>
<option value="Tipo de interfaz">Tipo de interfaz</option>
<option value="Tolerancia térmica">Tolerancia térmica</option>
<option value="Ubicación GPS">Ubicación GPS</option>
<option value="Uso en exteriores">Uso en exteriores</option>
<option value="Uso en interiores">Uso en interiores</option>
<option value="Velocidad variable">Velocidad variable</option>
<option value="Voltaje de operación">Voltaje de operación</option> 
                </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción:</label>
            <div class="flex gap-2">
            <input type="text" name="descripciones_caracteristica[]" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Ej: Hasta 60 dB" required>
            <button type="button" onclick="quitarCaracteristica(this)" class="text-white bg-red-600 px-2 py-1 rounded hover:bg-red-700">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
  </div>
</template>

<script>
function agregarCaracteristica() {
    const template = document.getElementById('caracteristica-template');
    const clone = template.content.cloneNode(true);
    document.getElementById('caracteristicas-container').appendChild(clone);
}

function quitarCaracteristica(button) {
    const item = button.closest('.caracteristica-item');
    if (item) {
        item.remove();
    }
}
</script>
<?php
$especificaciones = [];

$especQuery = $conexion->prepare("SELECT id_especificacion, nombre_especificacion, descripcion_especificacion FROM especificaciones_maquina WHERE id_maquina = ?");
$especQuery->bind_param("i", $id_maquina);
$especQuery->execute();
$resEspec = $especQuery->get_result();

while ($row = $resEspec->fetch_assoc()) {
    $especificaciones[] = $row;
}

$especQuery->close();
?>
<div class="mb-6">
  <label class="block text-lg font-semibold mb-2 flex items-center text-gray-800">
    <i class="fas fa-sliders-h text-indigo-600 mr-2"></i>
    Especificaciones registradas de la máquina
  </label>

  <div id="especificaciones-container" class="space-y-4">
    <?php foreach ($especificaciones as $espec): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 especificacion-item bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200 relative">
        
        <!-- ID oculta de la especificación -->
        <input type="hidden" name="id_especificacion[]" value="<?= $espec['id_especificacion'] ?>">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de especificación:</label>
          <input type="text" name="nombres_especificacion[]" class="w-full border border-gray-300 rounded-lg p-2 text-sm" value="<?= htmlspecialchars($espec['nombre_especificacion']) ?>" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción:</label>
          <input type="text" name="descripciones_especificacion[]" class="w-full border border-gray-300 rounded-lg p-2 text-sm" value="<?= htmlspecialchars($espec['descripcion_especificacion']) ?>">
        </div>

        <div class="absolute top-2 right-2">
          <button type="button" class="text-red-600 hover:text-red-800" onclick="this.closest('.especificacion-item').remove()">
            <i class="fas fa-trash-alt"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Especificaciones -->
<div class="mb-6">
    <label class="block font-semibold flex items-center mb-2">
        <i class="fas fa-list-alt text-blue-600 mr-2"></i>
        Especificaciones de la máquina:
    </label>
    <div id="especificaciones-container">
        <div class="mb-2 grid grid-cols-1 md:grid-cols-2 gap-2 items-center">
        <select id="miSelect" name="nombres_especificacion[]" class="border border-gray-300 rounded-lg p-2" required>
  <option value="" disabled selected>Seleccione una especificación</option>
<option value="Accesorios incluidos">Accesorios incluidos</option>
<option value="Altura máxima">Altura máxima</option>
<option value="Amperaje">Amperaje</option>
<option value="Ancho brazo">Ancho brazo</option>
<option value="Ancho de trabajo">Ancho de trabajo</option>
<option value="Área de trabajo">Área de trabajo</option>
<option value="Autonomía">Autonomía</option>
<option value="Capacidad">Capacidad</option>
<option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
<option value="Capacidad de análisis de datos">Capacidad de análisis de datos</option>
<option value="Capacidad de auto-limpieza">Capacidad de auto-limpieza</option>
<option value="Capacidad de calibración automática">Capacidad de calibración automática</option>
<option value="Capacidad de carga">Capacidad de carga</option>
<option value="Capacidad de carga dinámica">Capacidad de carga dinámica</option>
<option value="Capacidad de diagnóstico">Capacidad de diagnóstico</option>
<option value="Capacidad de detección de fallas">Capacidad de detección de fallas</option>
<option value="Capacidad de expansión">Capacidad de expansión</option>
<option value="Capacidad de integración">Capacidad de integración</option>
<option value="Capacidad de memoria">Capacidad de memoria</option>
<option value="Capacidad de monitoreo remoto">Capacidad de monitoreo remoto</option>
<option value="Capacidad de operación continua">Capacidad de operación continua</option>
<option value="Capacidad de operación en altitudes elevadas">Capacidad de operación en altitudes elevadas</option>
<option value="Capacidad de operación en ambientes con alta densidad de aire">Capacidad de operación en ambientes con alta densidad de aire</option>
<option value="Capacidad de operación en ambientes con alta densidad de animales">Capacidad de operación en ambientes con alta densidad de animales</option>
<option value="Capacidad de operación en ambientes con alta densidad de energía">Capacidad de operación en ambientes con alta densidad de energía</option>
<option value="Capacidad de operación en ambientes con alta densidad de luz">Capacidad de operación en ambientes con alta densidad de luz</option>
<option value="Capacidad de operación en ambientes con alta densidad de maquinaria">Capacidad de operación en ambientes con alta densidad de maquinaria</option>
<option value="Capacidad de operación en ambientes con alta densidad de personal">Capacidad de operación en ambientes con alta densidad de personal</option>
<option value="Capacidad de operación en ambientes con alta densidad de sonido">Capacidad de operación en ambientes con alta densidad de sonido</option>
<option value="Capacidad de operación en ambientes con alta densidad de tráfico">Capacidad de operación en ambientes con alta densidad de tráfico</option>
<option value="Capacidad de operación en ambientes con alta densidad de vegetación">Capacidad de operación en ambientes con alta densidad de vegetación</option>
<option value="Capacidad de operación en ambientes con alta densidad de agua">Capacidad de operación en ambientes con alta densidad de agua</option>
<option value="Capacidad de operación en ambientes con cambios bruscos de temperatura">Capacidad de operación en ambientes con cambios bruscos de temperatura</option>
<option value="Capacidad de operación en ambientes con gases tóxicos">Capacidad de operación en ambientes con gases tóxicos</option>
<option value="Capacidad de operación en ambientes con humedad alta">Capacidad de operación en ambientes con humedad alta</option>
<option value="Capacidad de operación en ambientes con niebla salina">Capacidad de operación en ambientes con niebla salina</option>
<option value="Capacidad de operación en ambientes con partículas abrasivas">Capacidad de operación en ambientes con partículas abrasivas</option>
<option value="Capacidad de operación en ambientes con polvo fino">Capacidad de operación en ambientes con polvo fino</option>
<option value="Capacidad de operación en ambientes con radiación solar directa">Capacidad de operación en ambientes con radiación solar directa</option>
<option value="Capacidad de operación en ambientes corrosivos">Capacidad de operación en ambientes corrosivos</option>
<option value="Capacidad de operación en ambientes explosivos">Capacidad de operación en ambientes explosivos</option>
<option value="Capacidad de operación en ambientes húmedos">Capacidad de operación en ambientes húmedos</option>
<option value="Capacidad de operación en ambientes polvorientos">Capacidad de operación en ambientes polvorientos</option>
<option value="Capacidad de operación en ambientes con vibraciones">Capacidad de operación en ambientes con vibraciones</option>
<option value="Capacidad de operación en bajas temperaturas">Capacidad de operación en bajas temperaturas</option>
<option value="Capacidad de operación en altas temperaturas">Capacidad de operación en altas temperaturas</option>
<option value="Capacidad de reciclaje">Capacidad de reciclaje</option>
<option value="Capacidad de respuesta">Capacidad de respuesta</option>
<option value="Capacidad de succión">Capacidad de succión</option>
<option value="Capacidad térmica">Capacidad térmica</option>
<option value="Capacidad de actualización">Capacidad de actualización</option>
<option value="Capacidad de aislamiento acústico">Capacidad de aislamiento acústico</option>
<option value="Capacidad de aislamiento térmico">Capacidad de aislamiento térmico</option>
<option value="Capacidad de programación">Capacidad de programación</option>
<option value="Capacidad de procesamiento">Capacidad de procesamiento</option>
<option value="Certificado">Certificado</option>
<option value="Certificaciones">Certificaciones</option>
<option value="Color">Color</option>
<option value="Compatibilidad con accesorios">Compatibilidad con accesorios</option>
<option value="Compatibilidad con entornos industriales">Compatibilidad con entornos industriales</option>
<option value="Compatibilidad con protocolos de comunicación">Compatibilidad con protocolos de comunicación</option>
<option value="Compatibilidad con redes">Compatibilidad con redes</option>
<option value="Compatibilidad con software">Compatibilidad con software</option>
<option value="Compatibilidad con estándares">Compatibilidad con estándares</option>
<option value="Compatibilidad con sistemas de gestión">Compatibilidad con sistemas de gestión</option>
<option value="Compatibilidad con sistemas de seguridad">Compatibilidad con sistemas de seguridad</option>
<option value="Compatibilidad eléctrica">Compatibilidad eléctrica</option>
<option value="Conectividad">Conectividad</option>
<option value="Consumo energético">Consumo energético</option>
<option value="Dimensiones">Dimensiones</option>
<option value="Eficiencia energética">Eficiencia energética</option>
<option value="Facilidad de mantenimiento">Facilidad de mantenimiento</option>
<option value="Flujo de volumen">Flujo de volumen</option>
<option value="Frecuencia">Frecuencia</option>
<option value="Garantía">Garantía</option>
<option value="Interfaz de usuario">Interfaz de usuario</option>
<option value="Material">Material</option>
<option value="Método de refrigeración">Método de refrigeración</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Nivel de ruido">Nivel de ruido</option>
<option value="Opciones de personalización">Opciones de personalización</option>
<option value="Peso">Peso</option>
<option value="Potencia">Potencia</option>
<option value="Precisión">Precisión</option>
<option value="Presión de trabajo">Presión de trabajo</option>
<option value="Presión máxima">Presión máxima</option>
<option value="Profundidad de corte">Profundidad de corte</option>
<option value="Punto de rocío">Punto de rocío</option>
<option value="RAM">RAM</option>
<option value="Rango de operación">Rango de operación</option>
<option value="Rango de presión">Rango de presión</option>
<option value="Rango de temperatura">Rango de temperatura</option>
<option value="Refrigerante">Refrigerante</option>
<option value="Resolución">Resolución</option>
<option value="Resistencia a impactos">Resistencia a impactos</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Resistencia a rayos UV">Resistencia a rayos UV</option>
<option value="Resistencia a temperaturas extremas">Resistencia a temperaturas extremas</option>
<option value="Resistencia a vibraciones">Resistencia a vibraciones</option>
<option value="Resistencia al agua">Resistencia al agua</option>
<option value="RPM">RPM</option>
<option value="Temperatura ambiente">Temperatura ambiente</option>
<option value="Temperatura de entrada">Temperatura de entrada</option>
<option value="Tiempo de carga">Tiempo de carga</option>
<option value="Tiempo de respuesta">Tiempo de respuesta</option>
<option value="Tiempo de vida del filtro">Tiempo de vida del filtro</option>
<option value="Tipo de batería">Tipo de batería</option>
<option value="Tipo de combustible">Tipo de combustible</option>
<option value="Tipo de conexión">Tipo de conexión</option>
<option value="Tipo de control">Tipo de control</option>
<option value="Tipo de iluminación">Tipo de iluminación</option>
<option value="Tipo de material de construcción">Tipo de material de construcción</option>
<option value="Tipo de montaje">Tipo de montaje</option>
<option value="Tipo de motor">Tipo de motor</option>
<option value="Tipo de pantalla">Tipo de pantalla</option>
<option value="Tipo de sensor">Tipo de sensor</option>
<option value="Tipo de transmisión">Tipo de transmisión</option>
<option value="Uso recomendado">Uso recomendado</option>
<option value="Velocidad">Velocidad</option>
<option value="Vida útil">Vida útil</option>
<option value="Voltaje">Voltaje</option>
<option value="Nivel de vibración">Nivel de vibración</option>
<option value="Clase de aislamiento">Clase de aislamiento</option>
<option value="Índice de protección IP">Índice de protección IP</option>
<option value="Clase de eficiencia IE">Clase de eficiencia IE</option>
<option value="Tipo de rodamientos">Tipo de rodamientos</option>
<option value="Tipo de eje">Tipo de eje</option>
<option value="Material del eje">Material del eje</option>
<option value="Material de la carcasa">Material de la carcasa</option>
<option value="Tipo de refrigeración">Tipo de refrigeración</option>
<option value="Nivel de precisión">Nivel de precisión</option>
<option value="Capacidad de aislamiento">Capacidad de aislamiento</option>
<option value="Nivel de protección térmica">Nivel de protección térmica</option>
<option value="Consumo en reposo">Consumo en reposo</option>
<option value="Tiempo de arranque">Tiempo de arranque</option>
<option value="Tipo de arranque">Tipo de arranque</option>
<option value="Método de control">Método de control</option>
<option value="Voltaje de control">Voltaje de control</option>
<option value="Corriente de control">Corriente de control</option>
<option value="Tiempo entre mantenimientos">Tiempo entre mantenimientos</option>
<option value="Tipo de lubricación">Tipo de lubricación</option>
<option value="Frecuencia de lubricación">Frecuencia de lubricación</option>
<option value="Diámetro de entrada/salida">Diámetro de entrada/salida</option>
<option value="Número de fases">Número de fases</option>
<option value="Número de polos">Número de polos</option>
<option value="Clase de precisión">Clase de precisión</option>
<option value="Tolerancia">Tolerancia</option>
<option value="Índice de eficiencia">Índice de eficiencia</option>
<option value="Margen de error">Margen de error</option>
<option value="Tasa de compresión">Tasa de compresión</option>
<option value="Diámetro de cilindro">Diámetro de cilindro</option>
<option value="Carrera del pistón">Carrera del pistón</option>
<option value="Par máximo">Par máximo</option>
<option value="Torque nominal">Torque nominal</option>
<option value="Frecuencia máxima de trabajo">Frecuencia máxima de trabajo</option>
<option value="Capacidad del compresor">Capacidad del compresor</option>
<option value="Capacidad de mezcla">Capacidad de mezcla</option>
<option value="Capacidad de producción">Capacidad de producción</option>
<option value="Capacidad de enfriamiento">Capacidad de enfriamiento</option>
<option value="Capacidad de calentamiento">Capacidad de calentamiento</option>
<option value="Tiempo de retención">Tiempo de retención</option>
<option value="Número de ciclos">Número de ciclos</option>
<option value="Rango de presión diferencial">Rango de presión diferencial</option>
<option value="Nivel de exactitud">Nivel de exactitud</option>
<option value="Capacidad de adaptación">Capacidad de adaptación</option>
<option value="Requerimiento de alimentación">Requerimiento de alimentación</option>
<option value="Tamaño de partícula">Tamaño de partícula</option>
<option value="Tipo de filtro">Tipo de filtro</option>
<option value="Método de carga">Método de carga</option>
<option value="Tiempo de inactividad">Tiempo de inactividad</option>
<option value="Tiempo de cambio de herramienta">Tiempo de cambio de herramienta</option>
<option value="Velocidad de proceso">Velocidad de proceso</option>
<option value="Velocidad de corte">Velocidad de corte</option>
<option value="Velocidad de desplazamiento">Velocidad de desplazamiento</option>
<option value="Resistencia a la tracción">Resistencia a la tracción</option>
<option value="Resistencia a la flexión">Resistencia a la flexión</option>
<option value="Índice de dureza">Índice de dureza</option>
<option value="Tiempo de recuperación">Tiempo de recuperación</option>
<option value="Índice de deformación">Índice de deformación</option>
<option value="Índice de elasticidad">Índice de elasticidad</option>
<option value="Densidad">Densidad</option>
<option value="Capacidad de amortiguamiento">Capacidad de amortiguamiento</option>
<option value="Factor de potencia">Factor de potencia</option>
<option value="Resistencia térmica">Resistencia térmica</option>
<option value="Tiempo de reacción">Tiempo de reacción</option>
<option value="Capacidad de ajuste automático">Capacidad de ajuste automático</option>
<option value="Capacidad de aprendizaje automático">Capacidad de aprendizaje automático</option>
<option value="Capacidad de respuesta adaptativa">Capacidad de respuesta adaptativa</option>
<option value="Integración con IoT">Integración con IoT</option>
<option value="Requiere mantenimiento especializado">Requiere mantenimiento especializado</option>
<option value="Interfaz hombre-máquina">Interfaz hombre-máquina</option>
<option value="Capacidad de autoevaluación">Capacidad de autoevaluación</option>
<option value="Tipo de interfaz de comunicación">Tipo de interfaz de comunicación</option>
<option value="Tipo de codificador">Tipo de codificador</option>
<option value="Longitud del cable">Longitud del cable</option>
<option value="Tipo de señal">Tipo de señal</option>
<option value="Nivel de interferencia electromagnética">Nivel de interferencia electromagnética</option>
<option value="Rango de detección">Rango de detección</option>
<option value="Tipo de sensor">Tipo de sensor</option>
<option value="Tipo de actuador">Tipo de actuador</option>
<option value="Tiempo de ciclo">Tiempo de ciclo</option>
<option value="Precisión de posicionamiento">Precisión de posicionamiento</option>
<option value="Compatibilidad de voltaje">Compatibilidad de voltaje</option>
<option value="Compatibilidad de red">Compatibilidad de red</option>
<option value="Protocolo de comunicación">Protocolo de comunicación</option>
<option value="Longitud máxima de carrera">Longitud máxima de carrera</option>
<option value="Capacidad de carga axial">Capacidad de carga axial</option>
<option value="Capacidad de carga radial">Capacidad de carga radial</option>
<option value="Velocidad angular">Velocidad angular</option>
<option value="Frecuencia de oscilación">Frecuencia de oscilación</option>
<option value="Nivel de ruido acústico">Nivel de ruido acústico</option>
<option value="Ruido de operación">Ruido de operación</option>
<option value="Nivel de emisión de calor">Nivel de emisión de calor</option>
<option value="Autonomía en batería">Autonomía en batería</option>
<option value="Tipo de batería">Tipo de batería</option>
<option value="Velocidad de carga de batería">Velocidad de carga de batería</option>
<option value="Tipo de conector eléctrico">Tipo de conector eléctrico</option>
<option value="Requiere UPS">Requiere UPS</option>
<option value="Consumo pico">Consumo pico</option>
<option value="Protección contra sobrecarga">Protección contra sobrecarga</option>
<option value="Protección contra cortocircuito">Protección contra cortocircuito</option>
<option value="Protección contra sobretensión">Protección contra sobretensión</option>
<option value="Protección contra inversión de polaridad">Protección contra inversión de polaridad</option>
<option value="Nivel de resistencia química">Nivel de resistencia química</option>
<option value="Compatibilidad con fluidos">Compatibilidad con fluidos</option>
<option value="Capacidad de presurización">Capacidad de presurización</option>
<option value="Medición de caudal">Medición de caudal</option>
<option value="Presión de trabajo nominal">Presión de trabajo nominal</option>
<option value="Material del sello">Material del sello</option>
<option value="Tipo de sellado">Tipo de sellado</option>
<option value="Rango de temperatura ambiente">Rango de temperatura ambiente</option>
<option value="Rango de humedad operativa">Rango de humedad operativa</option>
<option value="Rango de altitud">Rango de altitud</option>
<option value="Resistencia al polvo">Resistencia al polvo</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Revestimiento anticorrosivo">Revestimiento anticorrosivo</option>
<option value="Protección UV">Protección UV</option>
<option value="Protección contra impactos">Protección contra impactos</option>
<option value="Protección contra fuego">Protección contra fuego</option>
<option value="Sistema de ventilación">Sistema de ventilación</option>
<option value="Sistema de climatización">Sistema de climatización</option>
<option value="Capacidad de autodiagnóstico">Capacidad de autodiagnóstico</option>
<option value="Capacidad de actualización de firmware">Capacidad de actualización de firmware</option>
<option value="Capacidad de conectividad remota">Capacidad de conectividad remota</option>
<option value="Soporte para mantenimiento remoto">Soporte para mantenimiento remoto</option>
<option value="Compatibilidad con PLC">Compatibilidad con PLC</option>
<option value="Tipo de entrada digital">Tipo de entrada digital</option>
<option value="Tipo de salida analógica">Tipo de salida analógica</option>
<option value="Tipo de protocolo industrial">Tipo de protocolo industrial</option>
<option value="Nivel de seguridad operacional">Nivel de seguridad operacional</option>
<option value="Certificación de seguridad">Certificación de seguridad</option>
<option value="Normativa de cumplimiento">Normativa de cumplimiento</option>
<option value="Certificado CE">Certificado CE</option>
<option value="Certificado ISO">Certificado ISO</option>
<option value="Capacidad de conexión en red">Capacidad de conexión en red</option>
<option value="Puerto de comunicación disponible">Puerto de comunicación disponible</option>
<option value="Puerto Ethernet">Puerto Ethernet</option>
<option value="Puerto RS-232">Puerto RS-232</option>
<option value="Puerto RS-485">Puerto RS-485</option>
<option value="Puerto CAN">Puerto CAN</option>
<option value="Puerto USB">Puerto USB</option>
<option value="Puerto HDMI">Puerto HDMI</option>
<option value="Sistema operativo embebido">Sistema operativo embebido</option>
<option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
<option value="Memoria RAM">Memoria RAM</option>
<option value="Unidad de procesamiento">Unidad de procesamiento</option>
<option value="Frecuencia del procesador">Frecuencia del procesador</option>
<option value="Soporte para pantallas táctiles">Soporte para pantallas táctiles</option>
<option value="Capacidad de programación">Capacidad de programación</option>
<option value="Lenguaje de programación compatible">Lenguaje de programación compatible</option>
<option value="Actualización vía OTA">Actualización vía OTA</option>
<option value="Modo de calibración">Modo de calibración</option>
<option value="Indicador de mantenimiento">Indicador de mantenimiento</option>
<option value="Requiere calibración periódica">Requiere calibración periódica</option>
<option value="Tiempo promedio entre fallos (MTBF)">Tiempo promedio entre fallos (MTBF)</option>
<option value="Tiempo promedio de reparación (MTTR)">Tiempo promedio de reparación (MTTR)</option>
<option value="Tiempo estimado de vida útil">Tiempo estimado de vida útil</option>
<option value="Frecuencia de verificación">Frecuencia de verificación</option>
<option value="Indicador de error">Indicador de error</option>
<option value="Tipo de interfaz de usuario">Tipo de interfaz de usuario</option>
<option value="Idiomas soportados">Idiomas soportados</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Modo de operación (manual/automático)">Modo de operación (manual/automático)</option>
<option value="Soporte para Industria 4.0">Soporte para Industria 4.0</option>
<option value="Soporte para IoT">Soporte para IoT</option>
<option value="Integración con SCADA">Integración con SCADA</option>
<option value="Integración con ERP">Integración con ERP</option>
<option value="Compatibilidad con sensores inteligentes">Compatibilidad con sensores inteligentes</option>
<option value="Capacidad de integración modular">Capacidad de integración modular</option>
<option value="Soporte para mantenimiento predictivo">Soporte para mantenimiento predictivo</option>
<option value="Requiere mantenimiento especializado">Requiere mantenimiento especializado</option>
<option value="Tiempo estimado de instalación">Tiempo estimado de instalación</option>
<option value="Nivel de entrenamiento requerido">Nivel de entrenamiento requerido</option>
<option value="Curva de aprendizaje">Curva de aprendizaje</option>
<option value="Sustentabilidad ambiental">Sustentabilidad ambiental</option>
<option value="Consumo energético anual">Consumo energético anual</option>
<option value="Modo de ahorro de energía">Modo de ahorro de energía</option>
<option value="Emisión de carbono">Emisión de carbono</option>
<option value="Materiales reciclables">Materiales reciclables</option>
<option value="Compatibilidad con energías renovables">Compatibilidad con energías renovables</option>
<option value="Nivel de vibración">Nivel de vibración</option>
<option value="Requiere cimentación especial">Requiere cimentación especial</option>
<option value="Requiere aislamiento acústico">Requiere aislamiento acústico</option>
<option value="Altura máxima de operación">Altura máxima de operación</option>
<option value="Accesibilidad para mantenimiento">Accesibilidad para mantenimiento</option>
<option value="Diseño ergonómico">Diseño ergonómico</option>
<option value="Diseño compacto">Diseño compacto</option>
<option value="Capacidad de personalización">Capacidad de personalización</option>
<option value="Interfaz de usuario personalizable">Interfaz de usuario personalizable</option>
<option value="Requiere licencia de software">Requiere licencia de software</option>
<option value="Número de ciclos por minuto">Número de ciclos por minuto</option>
<option value="Velocidad máxima de producción">Velocidad máxima de producción</option>
<option value="Exactitud de corte">Exactitud de corte</option>
<option value="Rendimiento en condiciones extremas">Rendimiento en condiciones extremas</option>
<option value="Tipo de señal de control">Tipo de señal de control</option>
<option value="Tipo de filtro incorporado">Tipo de filtro incorporado</option>
<option value="Capacidad de monitoreo remoto">Capacidad de monitoreo remoto</option>
<option value="Sistema de alarma integrado">Sistema de alarma integrado</option>
<option value="Sistema de frenado de emergencia">Sistema de frenado de emergencia</option>
<option value="Sistema de respaldo energético">Sistema de respaldo energético</option>
<option value="Sistema de supresión de incendios">Sistema de supresión de incendios</option>
<option value="Tipo de iluminación">Tipo de iluminación</option>
<option value="Tipo de motor">Tipo de motor</option>
<option value="Tipo de transmisión">Tipo de transmisión</option>
<option value="Sistema de lubricación">Sistema de lubricación</option>
<option value="Sistema neumático">Sistema neumático</option>
<option value="Sistema hidráulico">Sistema hidráulico</option>
<option value="Sistema de control de presión">Sistema de control de presión</option>
<option value="Presión de entrada mínima">Presión de entrada mínima</option>
<option value="Presión de entrada máxima">Presión de entrada máxima</option>
<option value="Caída de presión permitida">Caída de presión permitida</option>
<option value="Tiempo de respuesta del sistema">Tiempo de respuesta del sistema</option>
<option value="Latencia del sistema">Latencia del sistema</option>
<option value="Respaldo de configuraciones">Respaldo de configuraciones</option>
<option value="Modo de diagnóstico">Modo de diagnóstico</option>
<option value="Compatibilidad con software CAD/CAM">Compatibilidad con software CAD/CAM</option>
<option value="Soporte para realidad aumentada">Soporte para realidad aumentada</option>
<option value="Soporte para visión artificial">Soporte para visión artificial</option>
<option value="Compatibilidad con impresoras industriales">Compatibilidad con impresoras industriales</option>
<option value="Sistema de impresión integrado">Sistema de impresión integrado</option>
<option value="Interfaz multilenguaje">Interfaz multilenguaje</option>
<option value="Accesibilidad para personas con discapacidad">Accesibilidad para personas con discapacidad</option>
<option value="Tiempo de enfriamiento">Tiempo de enfriamiento</option>
<option value="Tiempo de calentamiento">Tiempo de calentamiento</option>
<option value="Ciclo térmico">Ciclo térmico</option>
<option value="Compatibilidad electromagnética">Compatibilidad electromagnética</option>
<option value="Nivel de interferencia eléctrica">Nivel de interferencia eléctrica</option>
<option value="Nivel de interferencia magnética">Nivel de interferencia magnética</option>
<option value="Protección contra sobrecarga">Protección contra sobrecarga</option>
<option value="Protección contra sobrecalentamiento">Protección contra sobrecalentamiento</option>
<option value="Sistema de enclavamiento de seguridad">Sistema de enclavamiento de seguridad</option>
<option value="Sistema de parada de emergencia">Sistema de parada de emergencia</option>
<option value="Detección de fallos automática">Detección de fallos automática</option>
<option value="Monitoreo en tiempo real">Monitoreo en tiempo real</option>
<option value="Sistema de autodiagnóstico">Sistema de autodiagnóstico</option>
<option value="Soporte para actualizaciones OTA (Over the Air)">Soporte para actualizaciones OTA (Over the Air)</option>
<option value="Compatibilidad con protocolos OPC UA/MQTT">Compatibilidad con protocolos OPC UA/MQTT</option>
<option value="Soporte para Edge Computing">Soporte para Edge Computing</option>
<option value="Certificación ISO 9001">Certificación ISO 9001</option>
<option value="Certificación ISO 14001">Certificación ISO 14001</option>
<option value="Certificación CE">Certificación CE</option>
<option value="Certificación RoHS">Certificación RoHS</option>
<option value="Certificación UL">Certificación UL</option>
<option value="Cumplimiento con normativas OSHA">Cumplimiento con normativas OSHA</option>
<option value="Cumplimiento con normativas IEC">Cumplimiento con normativas IEC</option>
<option value="Cumplimiento con normativas ANSI">Cumplimiento con normativas ANSI</option>
<option value="Tipo de embalaje para transporte">Tipo de embalaje para transporte</option>
<option value="Método de transporte recomendado">Método de transporte recomendado</option>
<option value="Documentación técnica incluida">Documentación técnica incluida</option>
<option value="Idiomas disponibles de la documentación">Idiomas disponibles de la documentación</option>
<option value="Requiere manual de usuario físico">Requiere manual de usuario físico</option>
<option value="Tipo de garantía ofrecida">Tipo de garantía ofrecida</option>
<option value="Duración de la garantía">Duración de la garantía</option>
<option value="Vida útil estimada">Vida útil estimada</option>
<option value="Número de ciclos garantizados">Número de ciclos garantizados</option>
<option value="Sistema de sensores integrados">Sistema de sensores integrados</option>
<option value="Tipo de sensores incluidos">Tipo de sensores incluidos</option>
<option value="Precisión de sensores">Precisión de sensores</option>
<option value="Frecuencia de actualización de datos">Frecuencia de actualización de datos</option>
<option value="Consumo energético por hora de operación">Consumo energético por hora de operación</option>
<option value="Coeficiente de eficiencia energética (CEE)">Coeficiente de eficiencia energética (CEE)</option>
<option value="Modo de espera automático">Modo de espera automático</option>
<option value="Capacidad de regeneración de energía">Capacidad de regeneración de energía</option>
<option value="Soporte para baterías de respaldo">Soporte para baterías de respaldo</option>
<option value="Tipo de batería interna">Tipo de batería interna</option>
<option value="Duración de batería en respaldo">Duración de batería en respaldo</option>
<option value="Compatibilidad con sistemas BMS (Battery Management System)">Compatibilidad con sistemas BMS (Battery Management System)</option>
<option value="Sistema de monitoreo de temperatura">Sistema de monitoreo de temperatura</option>
<option value="Tipo de interfaz de operación">Tipo de interfaz de operación</option>
<option value="Pantalla táctil incorporada">Pantalla táctil incorporada</option>
<option value="Resolución de la interfaz">Resolución de la interfaz</option>
<option value="Interfaz por voz">Interfaz por voz</option>
<option value="Puerto USB para actualizaciones">Puerto USB para actualizaciones</option>
<option value="Puerto Ethernet">Puerto Ethernet</option>
<option value="Puerto serial (RS232/RS485)">Puerto serial (RS232/RS485)</option>
<option value="Compatibilidad con PLC">Compatibilidad con PLC</option>
<option value="Control por aplicación móvil">Control por aplicación móvil</option>
<option value="Soporte para conexión Bluetooth">Soporte para conexión Bluetooth</option>
<option value="Soporte para conexión Wi-Fi">Soporte para conexión Wi-Fi</option>
<option value="Requiere calibración periódica">Requiere calibración periódica</option>
<option value="Frecuencia de mantenimiento preventivo">Frecuencia de mantenimiento preventivo</option>
<option value="Frecuencia de mantenimiento correctivo">Frecuencia de mantenimiento correctivo</option>
<option value="Disponibilidad de repuestos">Disponibilidad de repuestos</option>
<option value="Tiempo promedio de reparación">Tiempo promedio de reparación</option>
<option value="Tiempo promedio de mantenimiento">Tiempo promedio de mantenimiento</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Capacidad de programación personalizada">Capacidad de programación personalizada</option>
<option value="Lenguajes de programación compatibles">Lenguajes de programación compatibles</option>
<option value="Compatibilidad con SCADA">Compatibilidad con SCADA</option>
<option value="Compatibilidad con ERP">Compatibilidad con ERP</option>
<option value="Compatibilidad con MES">Compatibilidad con MES</option>
<option value="Interfaz web integrada">Interfaz web integrada</option>
<option value="Permite configuración remota">Permite configuración remota</option>
<option value="Soporte técnico remoto">Soporte técnico remoto</option>
<option value="Historial de eventos/logs">Historial de eventos/logs</option>
<option value="Exportación de datos en CSV/JSON/XML">Exportación de datos en CSV/JSON/XML</option>
<option value="Condiciones ideales de almacenamiento">Condiciones ideales de almacenamiento</option>
<option value="Rango de humedad permitida">Rango de humedad permitida</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Tipo de pintura o recubrimiento">Tipo de pintura o recubrimiento</option>
<option value="Grado de protección IP">Grado de protección IP</option>
<option value="Protección contra interferencias electromagnéticas (EMI)">Protección contra interferencias electromagnéticas (EMI)</option>
<option value="Protección contra vibraciones">Protección contra vibraciones</option>
<option value="Nivel de aislamiento acústico">Nivel de aislamiento acústico</option>
<option value="Estructura modular">Estructura modular</option>
<option value="Material del bastidor">Material del bastidor</option>
<option value="Tipo de fijación al suelo">Tipo de fijación al suelo</option>
<option value="Sistema de amortiguación">Sistema de amortiguación</option>
<option value="Indicadores LED de estado">Indicadores LED de estado</option>
<option value="Pantalla de diagnóstico">Pantalla de diagnóstico</option>
<option value="Alertas visuales y auditivas">Alertas visuales y auditivas</option>
<option value="Modo de operación silenciosa">Modo de operación silenciosa</option>
<option value="Diseño ergonómico">Diseño ergonómico</option>
<option value="Accesibilidad para personas con discapacidad">Accesibilidad para personas con discapacidad</option>
<option value="Botones de emergencia accesibles">Botones de emergencia accesibles</option>
<option value="Manejo sencillo sin herramientas especiales">Manejo sencillo sin herramientas especiales</option>
<option value="Sistema de identificación por usuario">Sistema de identificación por usuario</option>
<option value="Niveles de acceso configurables">Niveles de acceso configurables</option>
<option value="Registro de actividad por usuario">Registro de actividad por usuario</option>
<option value="Requiere permisos de administrador">Requiere permisos de administrador</option>
<option value="Sistema de licencias de software">Sistema de licencias de software</option>
<option value="Modo de operación segura">Modo de operación segura</option>
<option value="Consumo de recursos optimizado">Consumo de recursos optimizado</option>
<option value="Impacto ambiental del ciclo de vida">Impacto ambiental del ciclo de vida</option>
<option value="Nivel de reciclabilidad de componentes">Nivel de reciclabilidad de componentes</option>
<option value="Uso de materiales sostenibles">Uso de materiales sostenibles</option>
<option value="Compatibilidad con energías renovables">Compatibilidad con energías renovables</option>
<option value="Soporte para paneles solares">Soporte para paneles solares</option>
<option value="Capacidad de integración con sistema domótico">Capacidad de integración con sistema domótico</option>
<option value="Control por comandos API">Control por comandos API</option>
<option value="Tipo de conectores estándar">Tipo de conectores estándar</option>
<option value="Longitud máxima de cableado permitido">Longitud máxima de cableado permitido</option>
<option value="Requiere conexión trifásica">Requiere conexión trifásica</option>
<option value="Tipo de refrigeración">Tipo de refrigeración</option>
<option value="Refrigeración líquida">Refrigeración líquida</option>
<option value="Refrigeración por aire forzado">Refrigeración por aire forzado</option>
<option value="Soporte para mantenimiento predictivo">Soporte para mantenimiento predictivo</option>
<option value="Soporte para mantenimiento proactivo">Soporte para mantenimiento proactivo</option>
<option value="Diagnóstico de fallas con IA">Diagnóstico de fallas con IA</option>
<option value="Optimización de rendimiento con IA">Optimización de rendimiento con IA</option>
<option value="Reconocimiento de patrones de uso">Reconocimiento de patrones de uso</option>
<option value="Adaptación automática a condiciones externas">Adaptación automática a condiciones externas</option>
<option value="Monitoreo en tiempo real 24/7">Monitoreo en tiempo real 24/7</option>
<option value="Sistema de autoaprendizaje">Sistema de autoaprendizaje</option>
<option value="Detección automática de anomalías">Detección automática de anomalías</option>
<option value="Recomendaciones de mantenimiento automatizadas">Recomendaciones de mantenimiento automatizadas</option>
<option value="Cumplimiento con normas ISO 9001">Cumplimiento con normas ISO 9001</option>
<option value="Cumplimiento con normas ISO 14001">Cumplimiento con normas ISO 14001</option>
<option value="Cumplimiento con normas OSHA">Cumplimiento con normas OSHA</option>
<option value="Cumplimiento con normas CE">Cumplimiento con normas CE</option>
<option value="Cumplimiento con normas UL">Cumplimiento con normas UL</option>
<option value="Compatibilidad con Industria 4.0">Compatibilidad con Industria 4.0</option>
<option value="Integración con blockchain para trazabilidad">Integración con blockchain para trazabilidad</option>
<option value="Simulación digital gemela (Digital Twin)">Simulación digital gemela (Digital Twin)</option>
<option value="Entrenamiento en entorno virtual (VR/AR)">Entrenamiento en entorno virtual (VR/AR)</option>
<option value="Control por voz">Control por voz</option>
<option value="Asistente virtual integrado">Asistente virtual integrado</option>
<option value="Gestión de consumo energético en tiempo real">Gestión de consumo energético en tiempo real</option>
<option value="Modo eco inteligente">Modo eco inteligente</option>
<option value="Control de temperatura ambiental automático">Control de temperatura ambiental automático</option>
<option value="Registro climático ambiental de la zona">Registro climático ambiental de la zona</option>
<option value="Capacidad de replicación remota">Capacidad de replicación remota</option>
<option value="Configuración de parámetros por código QR">Configuración de parámetros por código QR</option>
<option value="Personalización de panel HMI">Personalización de panel HMI</option>
<option value="Distribución inteligente de tareas">Distribución inteligente de tareas</option>
<option value="Sincronización con múltiples unidades">Sincronización con múltiples unidades</option>
<option value="Capacidad de producción adaptativa">Capacidad de producción adaptativa</option>
<option value="Monitoreo de eficiencia OEE">Monitoreo de eficiencia OEE</option>
<option value="Tasa de utilización real">Tasa de utilización real</option>
<option value="Medición de tiempo de ciclo en vivo">Medición de tiempo de ciclo en vivo</option>
<option value="Paradas técnicas programadas automáticamente">Paradas técnicas programadas automáticamente</option>
<option value="Compatibilidad con mantenimiento TPM">Compatibilidad con mantenimiento TPM</option>
<option value="Indicadores KPI integrados">Indicadores KPI integrados</option>
<option value="Visualización dinámica de datos históricos">Visualización dinámica de datos históricos</option>
<option value="Curva de aprendizaje incorporada">Curva de aprendizaje incorporada</option>
<option value="Simulación de escenarios de fallo">Simulación de escenarios de fallo</option>
<option value="Capacidad de control de calidad integrada">Capacidad de control de calidad integrada</option>
<option value="Registros de inspección automatizados">Registros de inspección automatizados</option>
<option value="Sistema de alertas inteligentes">Sistema de alertas inteligentes</option>
<option value="Conexión a plataformas en la nube">Conexión a plataformas en la nube</option>
<option value="Actualización de firmware automatizada">Actualización de firmware automatizada</option>
<option value="Auditoría técnica automática">Auditoría técnica automática</option>
<option value="Operación en temperaturas extremas (-40 °C a +85 °C)">Operación en temperaturas extremas (-40 °C a +85 °C)</option>
<option value="Resistencia a ambientes salinos o corrosivos">Resistencia a ambientes salinos o corrosivos</option>
<option value="Aislamiento sísmico de alta precisión">Aislamiento sísmico de alta precisión</option>
<option value="Resistencia a explosiones controladas">Resistencia a explosiones controladas</option>
<option value="Sistema de respaldo ante cortes de energía">Sistema de respaldo ante cortes de energía</option>
<option value="Aislamiento electromagnético">Aislamiento electromagnético</option>
<option value="Modo operación silenciosa (reducción de dB)">Modo operación silenciosa (reducción de dB)</option>
<option value="Resistencia al polvo y a la arena">Resistencia al polvo y a la arena</option>
<option value="Capacidad de funcionar en altura (> 3500 msnm)">Capacidad de funcionar en altura (> 3500 msnm)</option>
<option value="Protección contra sobrecarga atmosférica">Protección contra sobrecarga atmosférica</option>
<option value="Sensores ambientales integrados">Sensores ambientales integrados</option>
<option value="Sistema de autolimpieza">Sistema de autolimpieza</option>
<option value="Cumplimiento con regulaciones medioambientales internacionales">Cumplimiento con regulaciones medioambientales internacionales</option>
<option value="Medición de huella de carbono en tiempo real">Medición de huella de carbono en tiempo real</option>
<option value="Uso de materiales reciclables">Uso de materiales reciclables</option>
<option value="Sistema de reciclaje interno de fluidos">Sistema de reciclaje interno de fluidos</option>
<option value="Cero emisiones contaminantes">Cero emisiones contaminantes</option>
<option value="Modo ultra-eficiente">Modo ultra-eficiente</option>
<option value="Capacidad de operar con energía solar">Capacidad de operar con energía solar</option>
<option value="Interfaz de usuario multilingüe">Interfaz de usuario multilingüe</option>
<option value="Panel de control personalizable por usuario">Panel de control personalizable por usuario</option>
<option value="Iluminación LED integrada">Iluminación LED integrada</option>
<option value="Cubierta con acabado antimanchas">Cubierta con acabado antimanchas</option>
<option value="Diseño estético adaptativo">Diseño estético adaptativo</option>
<option value="Modo nocturno visual">Modo nocturno visual</option>
<option value="Sistema de bloqueo por reconocimiento facial">Sistema de bloqueo por reconocimiento facial</option>
<option value="Control de acceso por huella digital">Control de acceso por huella digital</option>
<option value="Cerradura biométrica">Cerradura biométrica</option>
<option value="Alarmas físicas y digitales">Alarmas físicas y digitales</option>
<option value="Registro automático de incidentes">Registro automático de incidentes</option>
<option value="Geolocalización en tiempo real">Geolocalización en tiempo real</option>
<option value="Conexión satelital de emergencia">Conexión satelital de emergencia</option>
<option value="Sistema de apagado remoto">Sistema de apagado remoto</option>
<option value="Capacidad de ser transportada modularmente">Capacidad de ser transportada modularmente</option>
<option value="Sistema de anclaje rápido">Sistema de anclaje rápido</option>
<option value="Autonivelación automática en superficies irregulares">Autonivelación automática en superficies irregulares</option>
<option value="Indicadores de desgaste predictivo">Indicadores de desgaste predictivo</option>
<option value="Sistema de registro de trazabilidad logística">Sistema de registro de trazabilidad logística</option>
<option value="Compatibilidad con AGVs (vehículos autónomos)">Compatibilidad con AGVs (vehículos autónomos)</option>
<option value="Empaquetado automatizado para traslado">Empaquetado automatizado para traslado</option>
<option value="Soporte para transporte en drones industriales">Soporte para transporte en drones industriales</option>
<option value="Certificación para transporte marítimo">Certificación para transporte marítimo</option>
<option value="Soporte para operación en plataformas offshore">Soporte para operación en plataformas offshore</option>
<option value="Capacidad de operar en ambientes polvorientos">Capacidad de operar en ambientes polvorientos</option>
<option value="Capacidad de operar en ambientes húmedos">Capacidad de operar en ambientes húmedos</option>
<option value="Protección IP67 contra polvo y agua">Protección IP67 contra polvo y agua</option>
<option value="Compatibilidad con norma ISO 13849 (seguridad funcional)">Compatibilidad con norma ISO 13849 (seguridad funcional)</option>
<option value="Eficiencia energética clase IE3">Eficiencia energética clase IE3</option>
<option value="Sistema de detección de fugas">Sistema de detección de fugas</option>
<option value="Sistema de monitoreo de vibraciones">Sistema de monitoreo de vibraciones</option>
<option value="Conexión a red SCADA">Conexión a red SCADA</option>
<option value="Sistema de frenado regenerativo">Sistema de frenado regenerativo</option>
<option value="Protección contra sobrecargas térmicas">Protección contra sobrecargas térmicas</option>
<option value="Motor con arranque suave (soft starter)">Motor con arranque suave (soft starter)</option>
<option value="Variador de frecuencia incorporado">Variador de frecuencia incorporado</option>
<option value="Cumplimiento con normativa RoHS">Cumplimiento con normativa RoHS</option>
<option value="Lubricación automática programable">Lubricación automática programable</option>
<option value="Compatibilidad con mantenimiento predictivo">Compatibilidad con mantenimiento predictivo</option>
<option value="Sensor de proximidad inductivo">Sensor de proximidad inductivo</option>
<option value="Sistema neumático con regulación de presión">Sistema neumático con regulación de presión</option>
<option value="Controlador PLC integrado">Controlador PLC integrado</option>
<option value="Sistema de respaldo UPS">Sistema de respaldo UPS</option>
<option value="Resistencia a productos químicos agresivos">Resistencia a productos químicos agresivos</option>
<option value="Temperatura de operación entre -20°C y 70°C">Temperatura de operación entre -20°C y 70°C</option>
<option value="Material inoxidable grado 316L">Material inoxidable grado 316L</option>
<option value="Nivel de ruido inferior a 60 dB">Nivel de ruido inferior a 60 dB</option>
<option value="Sistema de puesta a tierra certificado">Sistema de puesta a tierra certificado</option>
<option value="Detección de fallas en tiempo real">Detección de fallas en tiempo real</option>
<option value="Ejes con rodamientos sellados">Ejes con rodamientos sellados</option>
<option value="Conectores industriales M12">Conectores industriales M12</option>
<option value="Sistema de corte por emergencia (E-Stop)">Sistema de corte por emergencia (E-Stop)</option>
<option value="Pantalla HMI a color táctil">Pantalla HMI a color táctil</option>
<option value="Capacidad de monitoreo remoto por IoT">Capacidad de monitoreo remoto por IoT</option>
<option value="Soporte de protocolo Modbus/TCP">Soporte de protocolo Modbus/TCP</option>
<option value="Certificación CE de seguridad">Certificación CE de seguridad</option>
<option value="Sistema de enclavamiento de seguridad">Sistema de enclavamiento de seguridad</option>
<option value="Transmisión mecánica con acoplamiento flexible">Transmisión mecánica con acoplamiento flexible</option>
<option value="Sistema de ventilación forzada">Sistema de ventilación forzada</option>
<option value="Compatible con Industria 4.0">Compatible con Industria 4.0</option>
<option value="Estructura modular para fácil mantenimiento">Estructura modular para fácil mantenimiento</option>
<option value="Sistema de alarma por sobrecalentamiento">Sistema de alarma por sobrecalentamiento</option>
<option value="Pintura electrostática resistente a la corrosión">Pintura electrostática resistente a la corrosión</option>
<option value="Filtro de aire HEPA industrial">Filtro de aire HEPA industrial</option>
<option value="Sensor de nivel ultrasónico">Sensor de nivel ultrasónico</option>
<option value="Certificación ATEX para atmósferas explosivas">Certificación ATEX para atmósferas explosivas</option>
<option value="Integración con software de mantenimiento CMMS">Integración con software de mantenimiento CMMS</option>


</select>

            <input type="text" name="descripciones_especificacion[]" placeholder="Descripción (Ej: 220V monofásico)" class="border border-gray-300 rounded-lg p-2" required>
        </div>
    </div>
    <button type="button" onclick="agregarEspecificacion()" class="text-white bg-blue-600 px-3 py-1 rounded hover:bg-blue-700 mt-2">
        <i class="fas fa-plus"></i> Agregar Especificación
    </button>
    <small class="block text-gray-500 mt-1">Agregue nombre y descripción para cada especificación técnica.</small>
</div>

<!-- Scripts para agregar dinámicamente -->
<script>

function agregarEspecificacion() {
    const container = document.getElementById('especificaciones-container');
    const div = document.createElement('div');
    div.className = "mb-2 grid grid-cols-1 md:grid-cols-2 gap-2 items-center";
    div.innerHTML = `
        <select name="nombres_especificacion[]" class="border border-gray-300 rounded-lg p-2" required>
     <option value="" disabled selected>Seleccione una especificación</option>
<option value="Accesorios incluidos">Accesorios incluidos</option>
<option value="Altura máxima">Altura máxima</option>
<option value="Amperaje">Amperaje</option>
<option value="Ancho brazo">Ancho brazo</option>
<option value="Ancho de trabajo">Ancho de trabajo</option>
<option value="Área de trabajo">Área de trabajo</option>
<option value="Autonomía">Autonomía</option>
<option value="Capacidad">Capacidad</option>
<option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
<option value="Capacidad de análisis de datos">Capacidad de análisis de datos</option>
<option value="Capacidad de auto-limpieza">Capacidad de auto-limpieza</option>
<option value="Capacidad de calibración automática">Capacidad de calibración automática</option>
<option value="Capacidad de carga">Capacidad de carga</option>
<option value="Capacidad de carga dinámica">Capacidad de carga dinámica</option>
<option value="Capacidad de diagnóstico">Capacidad de diagnóstico</option>
<option value="Capacidad de detección de fallas">Capacidad de detección de fallas</option>
<option value="Capacidad de expansión">Capacidad de expansión</option>
<option value="Capacidad de integración">Capacidad de integración</option>
<option value="Capacidad de memoria">Capacidad de memoria</option>
<option value="Capacidad de monitoreo remoto">Capacidad de monitoreo remoto</option>
<option value="Capacidad de operación continua">Capacidad de operación continua</option>
<option value="Capacidad de operación en altitudes elevadas">Capacidad de operación en altitudes elevadas</option>
<option value="Capacidad de operación en ambientes con alta densidad de aire">Capacidad de operación en ambientes con alta densidad de aire</option>
<option value="Capacidad de operación en ambientes con alta densidad de animales">Capacidad de operación en ambientes con alta densidad de animales</option>
<option value="Capacidad de operación en ambientes con alta densidad de energía">Capacidad de operación en ambientes con alta densidad de energía</option>
<option value="Capacidad de operación en ambientes con alta densidad de luz">Capacidad de operación en ambientes con alta densidad de luz</option>
<option value="Capacidad de operación en ambientes con alta densidad de maquinaria">Capacidad de operación en ambientes con alta densidad de maquinaria</option>
<option value="Capacidad de operación en ambientes con alta densidad de personal">Capacidad de operación en ambientes con alta densidad de personal</option>
<option value="Capacidad de operación en ambientes con alta densidad de sonido">Capacidad de operación en ambientes con alta densidad de sonido</option>
<option value="Capacidad de operación en ambientes con alta densidad de tráfico">Capacidad de operación en ambientes con alta densidad de tráfico</option>
<option value="Capacidad de operación en ambientes con alta densidad de vegetación">Capacidad de operación en ambientes con alta densidad de vegetación</option>
<option value="Capacidad de operación en ambientes con alta densidad de agua">Capacidad de operación en ambientes con alta densidad de agua</option>
<option value="Capacidad de operación en ambientes con cambios bruscos de temperatura">Capacidad de operación en ambientes con cambios bruscos de temperatura</option>
<option value="Capacidad de operación en ambientes con gases tóxicos">Capacidad de operación en ambientes con gases tóxicos</option>
<option value="Capacidad de operación en ambientes con humedad alta">Capacidad de operación en ambientes con humedad alta</option>
<option value="Capacidad de operación en ambientes con niebla salina">Capacidad de operación en ambientes con niebla salina</option>
<option value="Capacidad de operación en ambientes con partículas abrasivas">Capacidad de operación en ambientes con partículas abrasivas</option>
<option value="Capacidad de operación en ambientes con polvo fino">Capacidad de operación en ambientes con polvo fino</option>
<option value="Capacidad de operación en ambientes con radiación solar directa">Capacidad de operación en ambientes con radiación solar directa</option>
<option value="Capacidad de operación en ambientes corrosivos">Capacidad de operación en ambientes corrosivos</option>
<option value="Capacidad de operación en ambientes explosivos">Capacidad de operación en ambientes explosivos</option>
<option value="Capacidad de operación en ambientes húmedos">Capacidad de operación en ambientes húmedos</option>
<option value="Capacidad de operación en ambientes polvorientos">Capacidad de operación en ambientes polvorientos</option>
<option value="Capacidad de operación en ambientes con vibraciones">Capacidad de operación en ambientes con vibraciones</option>
<option value="Capacidad de operación en bajas temperaturas">Capacidad de operación en bajas temperaturas</option>
<option value="Capacidad de operación en altas temperaturas">Capacidad de operación en altas temperaturas</option>
<option value="Capacidad de reciclaje">Capacidad de reciclaje</option>
<option value="Capacidad de respuesta">Capacidad de respuesta</option>
<option value="Capacidad de succión">Capacidad de succión</option>
<option value="Capacidad térmica">Capacidad térmica</option>
<option value="Capacidad de actualización">Capacidad de actualización</option>
<option value="Capacidad de aislamiento acústico">Capacidad de aislamiento acústico</option>
<option value="Capacidad de aislamiento térmico">Capacidad de aislamiento térmico</option>
<option value="Capacidad de programación">Capacidad de programación</option>
<option value="Capacidad de procesamiento">Capacidad de procesamiento</option>
<option value="Certificado">Certificado</option>
<option value="Certificaciones">Certificaciones</option>
<option value="Color">Color</option>
<option value="Compatibilidad con accesorios">Compatibilidad con accesorios</option>
<option value="Compatibilidad con entornos industriales">Compatibilidad con entornos industriales</option>
<option value="Compatibilidad con protocolos de comunicación">Compatibilidad con protocolos de comunicación</option>
<option value="Compatibilidad con redes">Compatibilidad con redes</option>
<option value="Compatibilidad con software">Compatibilidad con software</option>
<option value="Compatibilidad con estándares">Compatibilidad con estándares</option>
<option value="Compatibilidad con sistemas de gestión">Compatibilidad con sistemas de gestión</option>
<option value="Compatibilidad con sistemas de seguridad">Compatibilidad con sistemas de seguridad</option>
<option value="Compatibilidad eléctrica">Compatibilidad eléctrica</option>
<option value="Conectividad">Conectividad</option>
<option value="Consumo energético">Consumo energético</option>
<option value="Dimensiones">Dimensiones</option>
<option value="Eficiencia energética">Eficiencia energética</option>
<option value="Facilidad de mantenimiento">Facilidad de mantenimiento</option>
<option value="Flujo de volumen">Flujo de volumen</option>
<option value="Frecuencia">Frecuencia</option>
<option value="Garantía">Garantía</option>
<option value="Interfaz de usuario">Interfaz de usuario</option>
<option value="Material">Material</option>
<option value="Método de refrigeración">Método de refrigeración</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Nivel de ruido">Nivel de ruido</option>
<option value="Opciones de personalización">Opciones de personalización</option>
<option value="Peso">Peso</option>
<option value="Potencia">Potencia</option>
<option value="Precisión">Precisión</option>
<option value="Presión de trabajo">Presión de trabajo</option>
<option value="Presión máxima">Presión máxima</option>
<option value="Profundidad de corte">Profundidad de corte</option>
<option value="Punto de rocío">Punto de rocío</option>
<option value="RAM">RAM</option>
<option value="Rango de operación">Rango de operación</option>
<option value="Rango de presión">Rango de presión</option>
<option value="Rango de temperatura">Rango de temperatura</option>
<option value="Refrigerante">Refrigerante</option>
<option value="Resolución">Resolución</option>
<option value="Resistencia a impactos">Resistencia a impactos</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Resistencia a rayos UV">Resistencia a rayos UV</option>
<option value="Resistencia a temperaturas extremas">Resistencia a temperaturas extremas</option>
<option value="Resistencia a vibraciones">Resistencia a vibraciones</option>
<option value="Resistencia al agua">Resistencia al agua</option>
<option value="RPM">RPM</option>
<option value="Temperatura ambiente">Temperatura ambiente</option>
<option value="Temperatura de entrada">Temperatura de entrada</option>
<option value="Tiempo de carga">Tiempo de carga</option>
<option value="Tiempo de respuesta">Tiempo de respuesta</option>
<option value="Tiempo de vida del filtro">Tiempo de vida del filtro</option>
<option value="Tipo de batería">Tipo de batería</option>
<option value="Tipo de combustible">Tipo de combustible</option>
<option value="Tipo de conexión">Tipo de conexión</option>
<option value="Tipo de control">Tipo de control</option>
<option value="Tipo de iluminación">Tipo de iluminación</option>
<option value="Tipo de material de construcción">Tipo de material de construcción</option>
<option value="Tipo de montaje">Tipo de montaje</option>
<option value="Tipo de motor">Tipo de motor</option>
<option value="Tipo de pantalla">Tipo de pantalla</option>
<option value="Tipo de sensor">Tipo de sensor</option>
<option value="Tipo de transmisión">Tipo de transmisión</option>
<option value="Uso recomendado">Uso recomendado</option>
<option value="Velocidad">Velocidad</option>
<option value="Vida útil">Vida útil</option>
<option value="Voltaje">Voltaje</option>
<option value="Nivel de vibración">Nivel de vibración</option>
<option value="Clase de aislamiento">Clase de aislamiento</option>
<option value="Índice de protección IP">Índice de protección IP</option>
<option value="Clase de eficiencia IE">Clase de eficiencia IE</option>
<option value="Tipo de rodamientos">Tipo de rodamientos</option>
<option value="Tipo de eje">Tipo de eje</option>
<option value="Material del eje">Material del eje</option>
<option value="Material de la carcasa">Material de la carcasa</option>
<option value="Tipo de refrigeración">Tipo de refrigeración</option>
<option value="Nivel de precisión">Nivel de precisión</option>
<option value="Capacidad de aislamiento">Capacidad de aislamiento</option>
<option value="Nivel de protección térmica">Nivel de protección térmica</option>
<option value="Consumo en reposo">Consumo en reposo</option>
<option value="Tiempo de arranque">Tiempo de arranque</option>
<option value="Tipo de arranque">Tipo de arranque</option>
<option value="Método de control">Método de control</option>
<option value="Voltaje de control">Voltaje de control</option>
<option value="Corriente de control">Corriente de control</option>
<option value="Tiempo entre mantenimientos">Tiempo entre mantenimientos</option>
<option value="Tipo de lubricación">Tipo de lubricación</option>
<option value="Frecuencia de lubricación">Frecuencia de lubricación</option>
<option value="Diámetro de entrada/salida">Diámetro de entrada/salida</option>
<option value="Número de fases">Número de fases</option>
<option value="Número de polos">Número de polos</option>
<option value="Clase de precisión">Clase de precisión</option>
<option value="Tolerancia">Tolerancia</option>
<option value="Índice de eficiencia">Índice de eficiencia</option>
<option value="Margen de error">Margen de error</option>
<option value="Tasa de compresión">Tasa de compresión</option>
<option value="Diámetro de cilindro">Diámetro de cilindro</option>
<option value="Carrera del pistón">Carrera del pistón</option>
<option value="Par máximo">Par máximo</option>
<option value="Torque nominal">Torque nominal</option>
<option value="Frecuencia máxima de trabajo">Frecuencia máxima de trabajo</option>
<option value="Capacidad del compresor">Capacidad del compresor</option>
<option value="Capacidad de mezcla">Capacidad de mezcla</option>
<option value="Capacidad de producción">Capacidad de producción</option>
<option value="Capacidad de enfriamiento">Capacidad de enfriamiento</option>
<option value="Capacidad de calentamiento">Capacidad de calentamiento</option>
<option value="Tiempo de retención">Tiempo de retención</option>
<option value="Número de ciclos">Número de ciclos</option>
<option value="Rango de presión diferencial">Rango de presión diferencial</option>
<option value="Nivel de exactitud">Nivel de exactitud</option>
<option value="Capacidad de adaptación">Capacidad de adaptación</option>
<option value="Requerimiento de alimentación">Requerimiento de alimentación</option>
<option value="Tamaño de partícula">Tamaño de partícula</option>
<option value="Tipo de filtro">Tipo de filtro</option>
<option value="Método de carga">Método de carga</option>
<option value="Tiempo de inactividad">Tiempo de inactividad</option>
<option value="Tiempo de cambio de herramienta">Tiempo de cambio de herramienta</option>
<option value="Velocidad de proceso">Velocidad de proceso</option>
<option value="Velocidad de corte">Velocidad de corte</option>
<option value="Velocidad de desplazamiento">Velocidad de desplazamiento</option>
<option value="Resistencia a la tracción">Resistencia a la tracción</option>
<option value="Resistencia a la flexión">Resistencia a la flexión</option>
<option value="Índice de dureza">Índice de dureza</option>
<option value="Tiempo de recuperación">Tiempo de recuperación</option>
<option value="Índice de deformación">Índice de deformación</option>
<option value="Índice de elasticidad">Índice de elasticidad</option>
<option value="Densidad">Densidad</option>
<option value="Capacidad de amortiguamiento">Capacidad de amortiguamiento</option>
<option value="Factor de potencia">Factor de potencia</option>
<option value="Resistencia térmica">Resistencia térmica</option>
<option value="Tiempo de reacción">Tiempo de reacción</option>
<option value="Capacidad de ajuste automático">Capacidad de ajuste automático</option>
<option value="Capacidad de aprendizaje automático">Capacidad de aprendizaje automático</option>
<option value="Capacidad de respuesta adaptativa">Capacidad de respuesta adaptativa</option>
<option value="Integración con IoT">Integración con IoT</option>
<option value="Requiere mantenimiento especializado">Requiere mantenimiento especializado</option>
<option value="Interfaz hombre-máquina">Interfaz hombre-máquina</option>
<option value="Capacidad de autoevaluación">Capacidad de autoevaluación</option>
<option value="Tipo de interfaz de comunicación">Tipo de interfaz de comunicación</option>
<option value="Tipo de codificador">Tipo de codificador</option>
<option value="Longitud del cable">Longitud del cable</option>
<option value="Tipo de señal">Tipo de señal</option>
<option value="Nivel de interferencia electromagnética">Nivel de interferencia electromagnética</option>
<option value="Rango de detección">Rango de detección</option>
<option value="Tipo de sensor">Tipo de sensor</option>
<option value="Tipo de actuador">Tipo de actuador</option>
<option value="Tiempo de ciclo">Tiempo de ciclo</option>
<option value="Precisión de posicionamiento">Precisión de posicionamiento</option>
<option value="Compatibilidad de voltaje">Compatibilidad de voltaje</option>
<option value="Compatibilidad de red">Compatibilidad de red</option>
<option value="Protocolo de comunicación">Protocolo de comunicación</option>
<option value="Longitud máxima de carrera">Longitud máxima de carrera</option>
<option value="Capacidad de carga axial">Capacidad de carga axial</option>
<option value="Capacidad de carga radial">Capacidad de carga radial</option>
<option value="Velocidad angular">Velocidad angular</option>
<option value="Frecuencia de oscilación">Frecuencia de oscilación</option>
<option value="Nivel de ruido acústico">Nivel de ruido acústico</option>
<option value="Ruido de operación">Ruido de operación</option>
<option value="Nivel de emisión de calor">Nivel de emisión de calor</option>
<option value="Autonomía en batería">Autonomía en batería</option>
<option value="Tipo de batería">Tipo de batería</option>
<option value="Velocidad de carga de batería">Velocidad de carga de batería</option>
<option value="Tipo de conector eléctrico">Tipo de conector eléctrico</option>
<option value="Requiere UPS">Requiere UPS</option>
<option value="Consumo pico">Consumo pico</option>
<option value="Protección contra sobrecarga">Protección contra sobrecarga</option>
<option value="Protección contra cortocircuito">Protección contra cortocircuito</option>
<option value="Protección contra sobretensión">Protección contra sobretensión</option>
<option value="Protección contra inversión de polaridad">Protección contra inversión de polaridad</option>
<option value="Nivel de resistencia química">Nivel de resistencia química</option>
<option value="Compatibilidad con fluidos">Compatibilidad con fluidos</option>
<option value="Capacidad de presurización">Capacidad de presurización</option>
<option value="Medición de caudal">Medición de caudal</option>
<option value="Presión de trabajo nominal">Presión de trabajo nominal</option>
<option value="Material del sello">Material del sello</option>
<option value="Tipo de sellado">Tipo de sellado</option>
<option value="Rango de temperatura ambiente">Rango de temperatura ambiente</option>
<option value="Rango de humedad operativa">Rango de humedad operativa</option>
<option value="Rango de altitud">Rango de altitud</option>
<option value="Resistencia al polvo">Resistencia al polvo</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Revestimiento anticorrosivo">Revestimiento anticorrosivo</option>
<option value="Protección UV">Protección UV</option>
<option value="Protección contra impactos">Protección contra impactos</option>
<option value="Protección contra fuego">Protección contra fuego</option>
<option value="Sistema de ventilación">Sistema de ventilación</option>
<option value="Sistema de climatización">Sistema de climatización</option>
<option value="Capacidad de autodiagnóstico">Capacidad de autodiagnóstico</option>
<option value="Capacidad de actualización de firmware">Capacidad de actualización de firmware</option>
<option value="Capacidad de conectividad remota">Capacidad de conectividad remota</option>
<option value="Soporte para mantenimiento remoto">Soporte para mantenimiento remoto</option>
<option value="Compatibilidad con PLC">Compatibilidad con PLC</option>
<option value="Tipo de entrada digital">Tipo de entrada digital</option>
<option value="Tipo de salida analógica">Tipo de salida analógica</option>
<option value="Tipo de protocolo industrial">Tipo de protocolo industrial</option>
<option value="Nivel de seguridad operacional">Nivel de seguridad operacional</option>
<option value="Certificación de seguridad">Certificación de seguridad</option>
<option value="Normativa de cumplimiento">Normativa de cumplimiento</option>
<option value="Certificado CE">Certificado CE</option>
<option value="Certificado ISO">Certificado ISO</option>
<option value="Capacidad de conexión en red">Capacidad de conexión en red</option>
<option value="Puerto de comunicación disponible">Puerto de comunicación disponible</option>
<option value="Puerto Ethernet">Puerto Ethernet</option>
<option value="Puerto RS-232">Puerto RS-232</option>
<option value="Puerto RS-485">Puerto RS-485</option>
<option value="Puerto CAN">Puerto CAN</option>
<option value="Puerto USB">Puerto USB</option>
<option value="Puerto HDMI">Puerto HDMI</option>
<option value="Sistema operativo embebido">Sistema operativo embebido</option>
<option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
<option value="Memoria RAM">Memoria RAM</option>
<option value="Unidad de procesamiento">Unidad de procesamiento</option>
<option value="Frecuencia del procesador">Frecuencia del procesador</option>
<option value="Soporte para pantallas táctiles">Soporte para pantallas táctiles</option>
<option value="Capacidad de programación">Capacidad de programación</option>
<option value="Lenguaje de programación compatible">Lenguaje de programación compatible</option>
<option value="Actualización vía OTA">Actualización vía OTA</option>
<option value="Modo de calibración">Modo de calibración</option>
<option value="Indicador de mantenimiento">Indicador de mantenimiento</option>
<option value="Requiere calibración periódica">Requiere calibración periódica</option>
<option value="Tiempo promedio entre fallos (MTBF)">Tiempo promedio entre fallos (MTBF)</option>
<option value="Tiempo promedio de reparación (MTTR)">Tiempo promedio de reparación (MTTR)</option>
<option value="Tiempo estimado de vida útil">Tiempo estimado de vida útil</option>
<option value="Frecuencia de verificación">Frecuencia de verificación</option>
<option value="Indicador de error">Indicador de error</option>
<option value="Tipo de interfaz de usuario">Tipo de interfaz de usuario</option>
<option value="Idiomas soportados">Idiomas soportados</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Modo de operación (manual/automático)">Modo de operación (manual/automático)</option>
<option value="Soporte para Industria 4.0">Soporte para Industria 4.0</option>
<option value="Soporte para IoT">Soporte para IoT</option>
<option value="Integración con SCADA">Integración con SCADA</option>
<option value="Integración con ERP">Integración con ERP</option>
<option value="Compatibilidad con sensores inteligentes">Compatibilidad con sensores inteligentes</option>
<option value="Capacidad de integración modular">Capacidad de integración modular</option>
<option value="Soporte para mantenimiento predictivo">Soporte para mantenimiento predictivo</option>
<option value="Requiere mantenimiento especializado">Requiere mantenimiento especializado</option>
<option value="Tiempo estimado de instalación">Tiempo estimado de instalación</option>
<option value="Nivel de entrenamiento requerido">Nivel de entrenamiento requerido</option>
<option value="Curva de aprendizaje">Curva de aprendizaje</option>
<option value="Sustentabilidad ambiental">Sustentabilidad ambiental</option>
<option value="Consumo energético anual">Consumo energético anual</option>
<option value="Modo de ahorro de energía">Modo de ahorro de energía</option>
<option value="Emisión de carbono">Emisión de carbono</option>
<option value="Materiales reciclables">Materiales reciclables</option>
<option value="Compatibilidad con energías renovables">Compatibilidad con energías renovables</option>
<option value="Nivel de vibración">Nivel de vibración</option>
<option value="Requiere cimentación especial">Requiere cimentación especial</option>
<option value="Requiere aislamiento acústico">Requiere aislamiento acústico</option>
<option value="Altura máxima de operación">Altura máxima de operación</option>
<option value="Accesibilidad para mantenimiento">Accesibilidad para mantenimiento</option>
<option value="Diseño ergonómico">Diseño ergonómico</option>
<option value="Diseño compacto">Diseño compacto</option>
<option value="Capacidad de personalización">Capacidad de personalización</option>
<option value="Interfaz de usuario personalizable">Interfaz de usuario personalizable</option>
<option value="Requiere licencia de software">Requiere licencia de software</option>
<option value="Número de ciclos por minuto">Número de ciclos por minuto</option>
<option value="Velocidad máxima de producción">Velocidad máxima de producción</option>
<option value="Exactitud de corte">Exactitud de corte</option>
<option value="Rendimiento en condiciones extremas">Rendimiento en condiciones extremas</option>
<option value="Tipo de señal de control">Tipo de señal de control</option>
<option value="Tipo de filtro incorporado">Tipo de filtro incorporado</option>
<option value="Capacidad de monitoreo remoto">Capacidad de monitoreo remoto</option>
<option value="Sistema de alarma integrado">Sistema de alarma integrado</option>
<option value="Sistema de frenado de emergencia">Sistema de frenado de emergencia</option>
<option value="Sistema de respaldo energético">Sistema de respaldo energético</option>
<option value="Sistema de supresión de incendios">Sistema de supresión de incendios</option>
<option value="Tipo de iluminación">Tipo de iluminación</option>
<option value="Tipo de motor">Tipo de motor</option>
<option value="Tipo de transmisión">Tipo de transmisión</option>
<option value="Sistema de lubricación">Sistema de lubricación</option>
<option value="Sistema neumático">Sistema neumático</option>
<option value="Sistema hidráulico">Sistema hidráulico</option>
<option value="Sistema de control de presión">Sistema de control de presión</option>
<option value="Presión de entrada mínima">Presión de entrada mínima</option>
<option value="Presión de entrada máxima">Presión de entrada máxima</option>
<option value="Caída de presión permitida">Caída de presión permitida</option>
<option value="Tiempo de respuesta del sistema">Tiempo de respuesta del sistema</option>
<option value="Latencia del sistema">Latencia del sistema</option>
<option value="Respaldo de configuraciones">Respaldo de configuraciones</option>
<option value="Modo de diagnóstico">Modo de diagnóstico</option>
<option value="Compatibilidad con software CAD/CAM">Compatibilidad con software CAD/CAM</option>
<option value="Soporte para realidad aumentada">Soporte para realidad aumentada</option>
<option value="Soporte para visión artificial">Soporte para visión artificial</option>
<option value="Compatibilidad con impresoras industriales">Compatibilidad con impresoras industriales</option>
<option value="Sistema de impresión integrado">Sistema de impresión integrado</option>
<option value="Interfaz multilenguaje">Interfaz multilenguaje</option>
<option value="Accesibilidad para personas con discapacidad">Accesibilidad para personas con discapacidad</option>
<option value="Tiempo de enfriamiento">Tiempo de enfriamiento</option>
<option value="Tiempo de calentamiento">Tiempo de calentamiento</option>
<option value="Ciclo térmico">Ciclo térmico</option>
<option value="Compatibilidad electromagnética">Compatibilidad electromagnética</option>
<option value="Nivel de interferencia eléctrica">Nivel de interferencia eléctrica</option>
<option value="Nivel de interferencia magnética">Nivel de interferencia magnética</option>
<option value="Protección contra sobrecarga">Protección contra sobrecarga</option>
<option value="Protección contra sobrecalentamiento">Protección contra sobrecalentamiento</option>
<option value="Sistema de enclavamiento de seguridad">Sistema de enclavamiento de seguridad</option>
<option value="Sistema de parada de emergencia">Sistema de parada de emergencia</option>
<option value="Detección de fallos automática">Detección de fallos automática</option>
<option value="Monitoreo en tiempo real">Monitoreo en tiempo real</option>
<option value="Sistema de autodiagnóstico">Sistema de autodiagnóstico</option>
<option value="Soporte para actualizaciones OTA (Over the Air)">Soporte para actualizaciones OTA (Over the Air)</option>
<option value="Compatibilidad con protocolos OPC UA/MQTT">Compatibilidad con protocolos OPC UA/MQTT</option>
<option value="Soporte para Edge Computing">Soporte para Edge Computing</option>
<option value="Certificación ISO 9001">Certificación ISO 9001</option>
<option value="Certificación ISO 14001">Certificación ISO 14001</option>
<option value="Certificación CE">Certificación CE</option>
<option value="Certificación RoHS">Certificación RoHS</option>
<option value="Certificación UL">Certificación UL</option>
<option value="Cumplimiento con normativas OSHA">Cumplimiento con normativas OSHA</option>
<option value="Cumplimiento con normativas IEC">Cumplimiento con normativas IEC</option>
<option value="Cumplimiento con normativas ANSI">Cumplimiento con normativas ANSI</option>
<option value="Tipo de embalaje para transporte">Tipo de embalaje para transporte</option>
<option value="Método de transporte recomendado">Método de transporte recomendado</option>
<option value="Documentación técnica incluida">Documentación técnica incluida</option>
<option value="Idiomas disponibles de la documentación">Idiomas disponibles de la documentación</option>
<option value="Requiere manual de usuario físico">Requiere manual de usuario físico</option>
<option value="Tipo de garantía ofrecida">Tipo de garantía ofrecida</option>
<option value="Duración de la garantía">Duración de la garantía</option>
<option value="Vida útil estimada">Vida útil estimada</option>
<option value="Número de ciclos garantizados">Número de ciclos garantizados</option>
<option value="Sistema de sensores integrados">Sistema de sensores integrados</option>
<option value="Tipo de sensores incluidos">Tipo de sensores incluidos</option>
<option value="Precisión de sensores">Precisión de sensores</option>
<option value="Frecuencia de actualización de datos">Frecuencia de actualización de datos</option>
<option value="Consumo energético por hora de operación">Consumo energético por hora de operación</option>
<option value="Coeficiente de eficiencia energética (CEE)">Coeficiente de eficiencia energética (CEE)</option>
<option value="Modo de espera automático">Modo de espera automático</option>
<option value="Capacidad de regeneración de energía">Capacidad de regeneración de energía</option>
<option value="Soporte para baterías de respaldo">Soporte para baterías de respaldo</option>
<option value="Tipo de batería interna">Tipo de batería interna</option>
<option value="Duración de batería en respaldo">Duración de batería en respaldo</option>
<option value="Compatibilidad con sistemas BMS (Battery Management System)">Compatibilidad con sistemas BMS (Battery Management System)</option>
<option value="Sistema de monitoreo de temperatura">Sistema de monitoreo de temperatura</option>
<option value="Tipo de interfaz de operación">Tipo de interfaz de operación</option>
<option value="Pantalla táctil incorporada">Pantalla táctil incorporada</option>
<option value="Resolución de la interfaz">Resolución de la interfaz</option>
<option value="Interfaz por voz">Interfaz por voz</option>
<option value="Puerto USB para actualizaciones">Puerto USB para actualizaciones</option>
<option value="Puerto Ethernet">Puerto Ethernet</option>
<option value="Puerto serial (RS232/RS485)">Puerto serial (RS232/RS485)</option>
<option value="Compatibilidad con PLC">Compatibilidad con PLC</option>
<option value="Control por aplicación móvil">Control por aplicación móvil</option>
<option value="Soporte para conexión Bluetooth">Soporte para conexión Bluetooth</option>
<option value="Soporte para conexión Wi-Fi">Soporte para conexión Wi-Fi</option>
<option value="Requiere calibración periódica">Requiere calibración periódica</option>
<option value="Frecuencia de mantenimiento preventivo">Frecuencia de mantenimiento preventivo</option>
<option value="Frecuencia de mantenimiento correctivo">Frecuencia de mantenimiento correctivo</option>
<option value="Disponibilidad de repuestos">Disponibilidad de repuestos</option>
<option value="Tiempo promedio de reparación">Tiempo promedio de reparación</option>
<option value="Tiempo promedio de mantenimiento">Tiempo promedio de mantenimiento</option>
<option value="Nivel de automatización">Nivel de automatización</option>
<option value="Capacidad de programación personalizada">Capacidad de programación personalizada</option>
<option value="Lenguajes de programación compatibles">Lenguajes de programación compatibles</option>
<option value="Compatibilidad con SCADA">Compatibilidad con SCADA</option>
<option value="Compatibilidad con ERP">Compatibilidad con ERP</option>
<option value="Compatibilidad con MES">Compatibilidad con MES</option>
<option value="Interfaz web integrada">Interfaz web integrada</option>
<option value="Permite configuración remota">Permite configuración remota</option>
<option value="Soporte técnico remoto">Soporte técnico remoto</option>
<option value="Historial de eventos/logs">Historial de eventos/logs</option>
<option value="Exportación de datos en CSV/JSON/XML">Exportación de datos en CSV/JSON/XML</option>
<option value="Condiciones ideales de almacenamiento">Condiciones ideales de almacenamiento</option>
<option value="Rango de humedad permitida">Rango de humedad permitida</option>
<option value="Resistencia a la corrosión">Resistencia a la corrosión</option>
<option value="Tipo de pintura o recubrimiento">Tipo de pintura o recubrimiento</option>
<option value="Grado de protección IP">Grado de protección IP</option>
<option value="Protección contra interferencias electromagnéticas (EMI)">Protección contra interferencias electromagnéticas (EMI)</option>
<option value="Protección contra vibraciones">Protección contra vibraciones</option>
<option value="Nivel de aislamiento acústico">Nivel de aislamiento acústico</option>
<option value="Estructura modular">Estructura modular</option>
<option value="Material del bastidor">Material del bastidor</option>
<option value="Tipo de fijación al suelo">Tipo de fijación al suelo</option>
<option value="Sistema de amortiguación">Sistema de amortiguación</option>
<option value="Indicadores LED de estado">Indicadores LED de estado</option>
<option value="Pantalla de diagnóstico">Pantalla de diagnóstico</option>
<option value="Alertas visuales y auditivas">Alertas visuales y auditivas</option>
<option value="Modo de operación silenciosa">Modo de operación silenciosa</option>
<option value="Diseño ergonómico">Diseño ergonómico</option>
<option value="Accesibilidad para personas con discapacidad">Accesibilidad para personas con discapacidad</option>
<option value="Botones de emergencia accesibles">Botones de emergencia accesibles</option>
<option value="Manejo sencillo sin herramientas especiales">Manejo sencillo sin herramientas especiales</option>
<option value="Sistema de identificación por usuario">Sistema de identificación por usuario</option>
<option value="Niveles de acceso configurables">Niveles de acceso configurables</option>
<option value="Registro de actividad por usuario">Registro de actividad por usuario</option>
<option value="Requiere permisos de administrador">Requiere permisos de administrador</option>
<option value="Sistema de licencias de software">Sistema de licencias de software</option>
<option value="Modo de operación segura">Modo de operación segura</option>
<option value="Consumo de recursos optimizado">Consumo de recursos optimizado</option>
<option value="Impacto ambiental del ciclo de vida">Impacto ambiental del ciclo de vida</option>
<option value="Nivel de reciclabilidad de componentes">Nivel de reciclabilidad de componentes</option>
<option value="Uso de materiales sostenibles">Uso de materiales sostenibles</option>
<option value="Compatibilidad con energías renovables">Compatibilidad con energías renovables</option>
<option value="Soporte para paneles solares">Soporte para paneles solares</option>
<option value="Capacidad de integración con sistema domótico">Capacidad de integración con sistema domótico</option>
<option value="Control por comandos API">Control por comandos API</option>
<option value="Tipo de conectores estándar">Tipo de conectores estándar</option>
<option value="Longitud máxima de cableado permitido">Longitud máxima de cableado permitido</option>
<option value="Requiere conexión trifásica">Requiere conexión trifásica</option>
<option value="Tipo de refrigeración">Tipo de refrigeración</option>
<option value="Refrigeración líquida">Refrigeración líquida</option>
<option value="Refrigeración por aire forzado">Refrigeración por aire forzado</option>
<option value="Soporte para mantenimiento predictivo">Soporte para mantenimiento predictivo</option>
<option value="Soporte para mantenimiento proactivo">Soporte para mantenimiento proactivo</option>
<option value="Diagnóstico de fallas con IA">Diagnóstico de fallas con IA</option>
<option value="Optimización de rendimiento con IA">Optimización de rendimiento con IA</option>
<option value="Reconocimiento de patrones de uso">Reconocimiento de patrones de uso</option>
<option value="Adaptación automática a condiciones externas">Adaptación automática a condiciones externas</option>
<option value="Monitoreo en tiempo real 24/7">Monitoreo en tiempo real 24/7</option>
<option value="Sistema de autoaprendizaje">Sistema de autoaprendizaje</option>
<option value="Detección automática de anomalías">Detección automática de anomalías</option>
<option value="Recomendaciones de mantenimiento automatizadas">Recomendaciones de mantenimiento automatizadas</option>
<option value="Cumplimiento con normas ISO 9001">Cumplimiento con normas ISO 9001</option>
<option value="Cumplimiento con normas ISO 14001">Cumplimiento con normas ISO 14001</option>
<option value="Cumplimiento con normas OSHA">Cumplimiento con normas OSHA</option>
<option value="Cumplimiento con normas CE">Cumplimiento con normas CE</option>
<option value="Cumplimiento con normas UL">Cumplimiento con normas UL</option>
<option value="Compatibilidad con Industria 4.0">Compatibilidad con Industria 4.0</option>
<option value="Integración con blockchain para trazabilidad">Integración con blockchain para trazabilidad</option>
<option value="Simulación digital gemela (Digital Twin)">Simulación digital gemela (Digital Twin)</option>
<option value="Entrenamiento en entorno virtual (VR/AR)">Entrenamiento en entorno virtual (VR/AR)</option>
<option value="Control por voz">Control por voz</option>
<option value="Asistente virtual integrado">Asistente virtual integrado</option>
<option value="Gestión de consumo energético en tiempo real">Gestión de consumo energético en tiempo real</option>
<option value="Modo eco inteligente">Modo eco inteligente</option>
<option value="Control de temperatura ambiental automático">Control de temperatura ambiental automático</option>
<option value="Registro climático ambiental de la zona">Registro climático ambiental de la zona</option>
<option value="Capacidad de replicación remota">Capacidad de replicación remota</option>
<option value="Configuración de parámetros por código QR">Configuración de parámetros por código QR</option>
<option value="Personalización de panel HMI">Personalización de panel HMI</option>
<option value="Distribución inteligente de tareas">Distribución inteligente de tareas</option>
<option value="Sincronización con múltiples unidades">Sincronización con múltiples unidades</option>
<option value="Capacidad de producción adaptativa">Capacidad de producción adaptativa</option>
<option value="Monitoreo de eficiencia OEE">Monitoreo de eficiencia OEE</option>
<option value="Tasa de utilización real">Tasa de utilización real</option>
<option value="Medición de tiempo de ciclo en vivo">Medición de tiempo de ciclo en vivo</option>
<option value="Paradas técnicas programadas automáticamente">Paradas técnicas programadas automáticamente</option>
<option value="Compatibilidad con mantenimiento TPM">Compatibilidad con mantenimiento TPM</option>
<option value="Indicadores KPI integrados">Indicadores KPI integrados</option>
<option value="Visualización dinámica de datos históricos">Visualización dinámica de datos históricos</option>
<option value="Curva de aprendizaje incorporada">Curva de aprendizaje incorporada</option>
<option value="Simulación de escenarios de fallo">Simulación de escenarios de fallo</option>
<option value="Capacidad de control de calidad integrada">Capacidad de control de calidad integrada</option>
<option value="Registros de inspección automatizados">Registros de inspección automatizados</option>
<option value="Sistema de alertas inteligentes">Sistema de alertas inteligentes</option>
<option value="Conexión a plataformas en la nube">Conexión a plataformas en la nube</option>
<option value="Actualización de firmware automatizada">Actualización de firmware automatizada</option>
<option value="Auditoría técnica automática">Auditoría técnica automática</option>
<option value="Operación en temperaturas extremas (-40 °C a +85 °C)">Operación en temperaturas extremas (-40 °C a +85 °C)</option>
<option value="Resistencia a ambientes salinos o corrosivos">Resistencia a ambientes salinos o corrosivos</option>
<option value="Aislamiento sísmico de alta precisión">Aislamiento sísmico de alta precisión</option>
<option value="Resistencia a explosiones controladas">Resistencia a explosiones controladas</option>
<option value="Sistema de respaldo ante cortes de energía">Sistema de respaldo ante cortes de energía</option>
<option value="Aislamiento electromagnético">Aislamiento electromagnético</option>
<option value="Modo operación silenciosa (reducción de dB)">Modo operación silenciosa (reducción de dB)</option>
<option value="Resistencia al polvo y a la arena">Resistencia al polvo y a la arena</option>
<option value="Capacidad de funcionar en altura (> 3500 msnm)">Capacidad de funcionar en altura (> 3500 msnm)</option>
<option value="Protección contra sobrecarga atmosférica">Protección contra sobrecarga atmosférica</option>
<option value="Sensores ambientales integrados">Sensores ambientales integrados</option>
<option value="Sistema de autolimpieza">Sistema de autolimpieza</option>
<option value="Cumplimiento con regulaciones medioambientales internacionales">Cumplimiento con regulaciones medioambientales internacionales</option>
<option value="Medición de huella de carbono en tiempo real">Medición de huella de carbono en tiempo real</option>
<option value="Uso de materiales reciclables">Uso de materiales reciclables</option>
<option value="Sistema de reciclaje interno de fluidos">Sistema de reciclaje interno de fluidos</option>
<option value="Cero emisiones contaminantes">Cero emisiones contaminantes</option>
<option value="Modo ultra-eficiente">Modo ultra-eficiente</option>
<option value="Capacidad de operar con energía solar">Capacidad de operar con energía solar</option>
<option value="Interfaz de usuario multilingüe">Interfaz de usuario multilingüe</option>
<option value="Panel de control personalizable por usuario">Panel de control personalizable por usuario</option>
<option value="Iluminación LED integrada">Iluminación LED integrada</option>
<option value="Cubierta con acabado antimanchas">Cubierta con acabado antimanchas</option>
<option value="Diseño estético adaptativo">Diseño estético adaptativo</option>
<option value="Modo nocturno visual">Modo nocturno visual</option>
<option value="Sistema de bloqueo por reconocimiento facial">Sistema de bloqueo por reconocimiento facial</option>
<option value="Control de acceso por huella digital">Control de acceso por huella digital</option>
<option value="Cerradura biométrica">Cerradura biométrica</option>
<option value="Alarmas físicas y digitales">Alarmas físicas y digitales</option>
<option value="Registro automático de incidentes">Registro automático de incidentes</option>
<option value="Geolocalización en tiempo real">Geolocalización en tiempo real</option>
<option value="Conexión satelital de emergencia">Conexión satelital de emergencia</option>
<option value="Sistema de apagado remoto">Sistema de apagado remoto</option>
<option value="Capacidad de ser transportada modularmente">Capacidad de ser transportada modularmente</option>
<option value="Sistema de anclaje rápido">Sistema de anclaje rápido</option>
<option value="Autonivelación automática en superficies irregulares">Autonivelación automática en superficies irregulares</option>
<option value="Indicadores de desgaste predictivo">Indicadores de desgaste predictivo</option>
<option value="Sistema de registro de trazabilidad logística">Sistema de registro de trazabilidad logística</option>
<option value="Compatibilidad con AGVs (vehículos autónomos)">Compatibilidad con AGVs (vehículos autónomos)</option>
<option value="Empaquetado automatizado para traslado">Empaquetado automatizado para traslado</option>
<option value="Soporte para transporte en drones industriales">Soporte para transporte en drones industriales</option>
<option value="Certificación para transporte marítimo">Certificación para transporte marítimo</option>
<option value="Soporte para operación en plataformas offshore">Soporte para operación en plataformas offshore</option>
<option value="Capacidad de operar en ambientes polvorientos">Capacidad de operar en ambientes polvorientos</option>
<option value="Capacidad de operar en ambientes húmedos">Capacidad de operar en ambientes húmedos</option>
<option value="Protección IP67 contra polvo y agua">Protección IP67 contra polvo y agua</option>
<option value="Compatibilidad con norma ISO 13849 (seguridad funcional)">Compatibilidad con norma ISO 13849 (seguridad funcional)</option>
<option value="Eficiencia energética clase IE3">Eficiencia energética clase IE3</option>
<option value="Sistema de detección de fugas">Sistema de detección de fugas</option>
<option value="Sistema de monitoreo de vibraciones">Sistema de monitoreo de vibraciones</option>
<option value="Conexión a red SCADA">Conexión a red SCADA</option>
<option value="Sistema de frenado regenerativo">Sistema de frenado regenerativo</option>
<option value="Protección contra sobrecargas térmicas">Protección contra sobrecargas térmicas</option>
<option value="Motor con arranque suave (soft starter)">Motor con arranque suave (soft starter)</option>
<option value="Variador de frecuencia incorporado">Variador de frecuencia incorporado</option>
<option value="Cumplimiento con normativa RoHS">Cumplimiento con normativa RoHS</option>
<option value="Lubricación automática programable">Lubricación automática programable</option>
<option value="Compatibilidad con mantenimiento predictivo">Compatibilidad con mantenimiento predictivo</option>
<option value="Sensor de proximidad inductivo">Sensor de proximidad inductivo</option>
<option value="Sistema neumático con regulación de presión">Sistema neumático con regulación de presión</option>
<option value="Controlador PLC integrado">Controlador PLC integrado</option>
<option value="Sistema de respaldo UPS">Sistema de respaldo UPS</option>
<option value="Resistencia a productos químicos agresivos">Resistencia a productos químicos agresivos</option>
<option value="Temperatura de operación entre -20°C y 70°C">Temperatura de operación entre -20°C y 70°C</option>
<option value="Material inoxidable grado 316L">Material inoxidable grado 316L</option>
<option value="Nivel de ruido inferior a 60 dB">Nivel de ruido inferior a 60 dB</option>
<option value="Sistema de puesta a tierra certificado">Sistema de puesta a tierra certificado</option>
<option value="Detección de fallas en tiempo real">Detección de fallas en tiempo real</option>
<option value="Ejes con rodamientos sellados">Ejes con rodamientos sellados</option>
<option value="Conectores industriales M12">Conectores industriales M12</option>
<option value="Sistema de corte por emergencia (E-Stop)">Sistema de corte por emergencia (E-Stop)</option>
<option value="Pantalla HMI a color táctil">Pantalla HMI a color táctil</option>
<option value="Capacidad de monitoreo remoto por IoT">Capacidad de monitoreo remoto por IoT</option>
<option value="Soporte de protocolo Modbus/TCP">Soporte de protocolo Modbus/TCP</option>
<option value="Certificación CE de seguridad">Certificación CE de seguridad</option>
<option value="Sistema de enclavamiento de seguridad">Sistema de enclavamiento de seguridad</option>
<option value="Transmisión mecánica con acoplamiento flexible">Transmisión mecánica con acoplamiento flexible</option>
<option value="Sistema de ventilación forzada">Sistema de ventilación forzada</option>
<option value="Compatible con Industria 4.0">Compatible con Industria 4.0</option>
<option value="Estructura modular para fácil mantenimiento">Estructura modular para fácil mantenimiento</option>
<option value="Sistema de alarma por sobrecalentamiento">Sistema de alarma por sobrecalentamiento</option>
<option value="Pintura electrostática resistente a la corrosión">Pintura electrostática resistente a la corrosión</option>
<option value="Filtro de aire HEPA industrial">Filtro de aire HEPA industrial</option>
<option value="Sensor de nivel ultrasónico">Sensor de nivel ultrasónico</option>
<option value="Certificación ATEX para atmósferas explosivas">Certificación ATEX para atmósferas explosivas</option>
<option value="Integración con software de mantenimiento CMMS">Integración con software de mantenimiento CMMS</option>

</select>
        <div class="flex gap-2">
            <input type="text" name="descripciones_especificacion[]" placeholder="Descripción (Ej: 50 litros)" class="border border-gray-300 rounded-lg p-2 w-full" required>
            <button type="button" onclick="this.closest('.grid').remove()" class="text-white bg-red-600 px-2 py-1 rounded hover:bg-red-700">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
}
</script>



<!-- Iro.js -->
<script src="js/iro.min.js"></script>

<!-- Contenedor principal -->
<div class="flex flex-col items-center justify-center px-4">
  <div class="bg-white shadow-lg rounded-xl p-6 w-full max-w-sm flex flex-col items-center">
    
    <!-- Título -->
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Selecciona un color para la máquina</h2>
    
    <!-- Selector de color -->
    <div id="colorPickerContainer" class="mb-6"></div>

    <!-- Vista previa del color seleccionado -->
    <div class="flex flex-col items-center">
      <div id="colorPreview" class="w-12 h-12 rounded-full border border-gray-300 shadow-inner mb-2"></div>
      <p class="text-sm text-gray-600">Color seleccionado: 
        <span id="colorHex" class="font-mono text-gray-800"></span>
      </p>
    </div>
    <!-- Input oculto para enviar el valor -->
    <input type="hidden" id="selected-color" name="color">
  </div>
</div>

<!-- Script -->
<script>
  // Obtener el color desde PHP
  const colorInicial = "<?= htmlspecialchars($maquina['color']) ?>";

  const colorPicker = new iro.ColorPicker('#colorPickerContainer', {
    width: 200,
    color: colorInicial, // Inicializar con el color de la máquina
    borderWidth: 1,
    borderColor: "#ccc"
  });

  const selectedInput = document.getElementById('selected-color');
  const colorPreview = document.getElementById('colorPreview');
  const colorHexText = document.getElementById('colorHex');

  // Asignar el color inicial a los elementos visuales
  selectedInput.value = colorInicial;
  colorPreview.style.backgroundColor = colorInicial;
  colorHexText.textContent = colorInicial;

  // Actualizar cuando se cambia el color en el selector
  colorPicker.on('color:change', function(color) {
    const hex = color.hexString;
    selectedInput.value = hex;
    colorPreview.style.backgroundColor = hex;
    colorHexText.textContent = hex;
  });
</script>



    <!-- Botones -->
    <div class="flex justify-between mt-4 space-x-4">
        <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-green-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-300">
            <i class="fas fa-save mr-2"></i>
            Guardar Maquina
        </button>

        <button type="button" onclick="location.href='maquina.php';" class="bg-blue-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-blue-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i class="fas fa-arrow-left mr-2"></i>
            Regresar
        </button>
    </div>
</form>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let select = document.getElementById("miSelect");
    let opciones = Array.from(select.options);

    // Ordena las opciones alfabéticamente
    opciones.sort((a, b) => a.text.localeCompare(b.text));

    // Vacía el select y añade las opciones ordenadas
    select.innerHTML = "";
    opciones.forEach(opcion => select.appendChild(opcion));
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

