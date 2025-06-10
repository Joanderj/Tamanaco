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

    
        // Añadir especificaciones dinámicamente
        function agregarEspecificacion() {
            const container = document.getElementById('contenedor-especificaciones');
            const nuevaEspecificacion = document.createElement('div');
            nuevaEspecificacion.classList.add('especificacion', 'bg-gray-100', 'p-4', 'rounded-lg', 'mb-4', 'shadow-md');

            nuevaEspecificacion.innerHTML = `
                <div class="mb-2">
                    <label for="detalle_especificacion" class="block font-semibold mb-1">Detalle de la Especificación:</label>
                    <select name="detalle_especificacion[]" class="w-full border border-gray-300 rounded-lg p-2" required>
                        <option value="" disabled selected>Seleccione un detalle</option>
                        <optgroup label="General">
                            <option value="nombre_comercial">Nombre Comercial</option>
                            <option value="codigo_producto">Código del Producto</option>
                            <option value="descripcion_general">Descripción General</option>
                        </optgroup>
                        <optgroup label="Características Técnicas">
                            <option value="material">Material</option>
                            <option value="dimensiones">Dimensiones</option>
                            <option value="peso">Peso</option>
                            <option value="capacidad_maxima">Capacidad Máxima</option>
                            <option value="voltaje">Voltaje</option>
                            <option value="temperatura_operativa">Temperatura Operativa</option>
                            <option value="presion_maxima">Presión Máxima</option>
                            <option value="nivel_filtracion">Nivel de Filtración</option>
                        </optgroup>
                        <optgroup label="Compatibilidad">
                            <option value="modelos_compatibles">Modelos Compatibles</option>
                            <option value="marcas_compatibles">Marcas Compatibles</option>
                            <option value="sistemas_compatibles">Sistemas Compatibles</option>
                        </optgroup>
                        <optgroup label="Uso Industrial">
                            <option value="certificacion">Certificación</option>
                            <option value="normativas_aplicables">Normativas Aplicables</option>
                            <option value="ambiente_uso">Ambiente de Uso (Interior/Exterior)</option>
                        </optgroup>
                        <optgroup label="Detalles Estéticos">
                            <option value="color">Color</option>
                            <option value="acabado">Acabado</option>
                            <option value="marca_visible">Marca Visible</option>
                        </optgroup>
                        <optgroup label="Funcionalidad">
                            <option value="tipo_funcion">Tipo de Función</option>
                            <option value="modo_operacion">Modo de Operación</option>
                            <option value="durabilidad">Durabilidad</option>
                        </optgroup>
                    </select>
                </div>
                <div class="mb-2">
                    <label for="valor_especificacion" class="block font-semibold mb-1">Valor de la Especificación:</label>
                    <input type="text" name="valor_especificacion[]" placeholder="Ejemplo: Acero inoxidable, 250 bar" class="w-full border border-gray-300 rounded-lg p-2" required>
                </div>
                <button type="button" onclick="eliminarEspecificacion(this)" class="bg-red-500 text-white py-1 px-3 rounded-lg hover:bg-red-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Eliminar
                </button>
            `;

            container.appendChild(nuevaEspecificacion);
        }
function eliminarEspecificacion(boton) {
    console.log(boton.parentElement); // Check what this logs
    const especificacion = boton.parentElement;
    especificacion.remove();
}

    </script>
    <style>
        .especificacion {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
   <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
 <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
 <div class="flex flex-col items-center mb-6">
    <!-- Ícono de repuestos -->
    <div class="bg-green-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
        <i class="fas fa-cogs text-3xl"></i> <!-- Ícono de engranajes -->
    </div>
    <!-- Título del formulario -->
    <h2 class="text-3xl font-extrabold text-gray-800">Formulario de Repuestos</h2>
    <!-- Descripción del formulario -->
    <p class="text-gray-600 mt-2 text-center">Registra los repuestos y sus especificaciones relacionadas de forma sencilla y dinámica.</p>
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
$id = $_GET['id_herramienta'] ?? null;
$conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
$herramienta = $conexion->query("SELECT * FROM herramientas WHERE id_herramienta = $id")->fetch_assoc();
?>

<form action="modificar_herramienta.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id_herramienta" value="<?= $herramienta['id_herramienta'] ?>">
<!-- Contenedor de imagen clickeable -->
<div class="text-center mb-6">
    <label for="imagen" class="relative w-64 h-64 mx-auto border-2 border-dashed border-blue-500 rounded-lg flex justify-center items-center cursor-pointer group overflow-hidden">
        
        <!-- Input oculto -->
        <input type="file" id="imagen" name="nombre_imagen" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="mostrarPreview()" />

        <!-- Previsualización -->
        <img id="imagen-preview"
            src="<?= !empty($herramienta['url']) ? $herramienta['url'] : '' ?>"
            alt="Imagen actual"
            class="absolute inset-0 w-full h-full object-cover rounded-lg <?= !empty($herramienta['url']) ? '' : 'hidden' ?>" />

        <!-- Placeholder -->
        <div id="imagen-placeholder"
            class="text-center absolute inset-0 flex flex-col items-center justify-center text-blue-500 bg-white/60 group-hover:bg-white/70 transition <?= empty($herramienta['url']) ? '' : 'hidden' ?>">
            <i class="fas fa-tools text-3xl"></i>
            <p class="font-medium">Haga clic para subir una foto</p>
            <p class="text-gray-400 text-sm">PNG, JPG, máximo 5MB</p>
        </div>
    </label>
</div>

<script>
    function mostrarPreview() {
        const input = document.getElementById('imagen');
        const preview = document.getElementById('imagen-preview');
        const placeholder = document.getElementById('imagen-placeholder');
        const file = input.files[0];

        if (file) {
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
    }
</script>

    <!-- Nombre -->
    <div class="mb-4">
        <label for="nombre_herramienta" class="block font-semibold flex items-center">
            <i class="fas fa-tag text-blue-500 mr-2"></i> Nombre de la Herramienta: <span class="text-red-600">*</span>
        </label>
        <input oninput="this.value = this.value.toUpperCase();"
 type="text" id="nombre_herramienta" name="nombre_herramienta" value="<?= htmlspecialchars($herramienta['nombre_herramienta']) ?>" class="w-full border border-gray-300 rounded-lg p-2" required>
    </div>

    <!-- Descripción -->
    <div class="mb-4">
        <label for="descripcion" class="block font-semibold flex items-center">
            <i class="fas fa-file-alt text-blue-500 mr-2"></i> Descripción:
        </label>
        <textarea oninput="this.value = this.value.toUpperCase();"
 id="descripcion" name="descripcion" class="w-full border border-gray-300 rounded-lg p-2"><?= htmlspecialchars($herramienta['descripcion']) ?></textarea>
    </div>

    <!-- Marca -->
    <div class="mb-4">
        <label for="marca" class="block font-semibold flex items-center">
            <i class="fas fa-industry text-blue-500 mr-2"></i> Marca: <span class="text-red-600">*</span>
        </label>
        <select id="marca" name="id_marca" onchange="cargarModelos()" class="w-full border border-gray-300 rounded-lg p-2" required>
            <option disabled>Seleccione una marca</option>
            <?php
            $marcas = $conexion->query("SELECT id_marca, nombre_marca FROM marca");
            while ($fila = $marcas->fetch_assoc()) {
                $selected = $herramienta['id_marca'] == $fila['id_marca'] ? 'selected' : '';
                echo "<option value='{$fila['id_marca']}' $selected>{$fila['nombre_marca']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Modelo -->
    <div class="mb-4">
        <label for="modelo" class="block font-semibold flex items-center">
            <i class="fas fa-cube text-blue-500 mr-2"></i> Modelo:
        </label>
        <select id="modelo" name="id_modelo" class="w-full border border-gray-300 rounded-lg p-2">
            <option disabled>Seleccione un modelo</option>
            <?php
            $modelos = $conexion->query("SELECT id_modelo, nombre_modelo FROM modelo");
            while ($fila = $modelos->fetch_assoc()) {
                $selected = $herramienta['id_modelo'] == $fila['id_modelo'] ? 'selected' : '';
                echo "<option value='{$fila['id_modelo']}' $selected>{$fila['nombre_modelo']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Tipo -->
    <div class="mb-4">
        <label for="tipo" class="block font-semibold flex items-center">
            <i class="fas fa-tools text-blue-500 mr-2"></i> Tipo:
        </label>
        <select id="tipo" name="id_tipo" class="w-full border border-gray-300 rounded-lg p-2">
            <option disabled>Seleccione un tipo</option>
            <?php
            $tipos = $conexion->query("SELECT id_tipo, nombre_tipo FROM tipo");
            while ($fila = $tipos->fetch_assoc()) {
                $selected = $herramienta['id_tipo'] == $fila['id_tipo'] ? 'selected' : '';
                echo "<option value='{$fila['id_tipo']}' $selected>{$fila['nombre_tipo']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- Botones -->
    <div class="flex justify-between mt-4 space-x-4">
        <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-green-600 transition">
            <i class="fas fa-save mr-2"></i> Guardar Cambios
        </button>
        <button type="button" onclick="location.href='herramienta.php';" class="bg-blue-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-blue-600 transition">
            <i class="fas fa-arrow-left mr-2"></i> Regresar
        </button>
    </div>
</form>

<?php $conexion->close(); ?>

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
