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
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-4xl">
            <!-- Encabezado -->
            <div class="text-center mb-8">
                <i class="fas fa-user-shield text-5xl text-blue-500"></i>
                <h1 class="text-3xl font-bold text-gray-700 mt-2">Gestión de Permisos</h1>
                <p class="text-gray-500">Seleccione un perfil y configure sus permisos</p>
            </div>
            
            <!-- Formulario -->
            <form method="POST" action="guardar_permisos.php">
                <div class="mb-6">
                    <label for="perfil" class="block text-gray-700 font-medium mb-2">Seleccionar Perfil:</label>
                    <select name="id_perfil" id="id_perfil" class="w-full border rounded-lg p-2">
                        <option value="" disabled selected>Seleccione un perfil</option>
                        <?php
                        try {
                            $pdo = new PDO('mysql:host=localhost;dbname=bd_tamanaco', 'root', '');
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $query = "SELECT id_perfil, nombre_perfil FROM perfiles WHERE id_status = 1 AND id_perfil != 1";
                            $stmt = $pdo->query($query);

                            while ($perfil = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$perfil['id_perfil']}'>{$perfil['nombre_perfil']}</option>";
                            }
                        } catch (PDOException $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                    </select>
                </div>

                <!-- Contenedor dinámico de permisos -->
                <div id="permisos-container" class="bg-gray-50 border rounded-lg p-4">
                    <p class="text-gray-600 text-center">Seleccione un perfil para gestionar sus permisos.</p>
                </div>

                <!-- Botón para guardar -->
                <div class="mt-6 text-center">
                    <button type="submit" id="guardar-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hidden">
                        Guardar Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Script JavaScript -->
    <script>
        document.getElementById("id_perfil").addEventListener("change", function () {
            const idPerfil = this.value;
            const permisosContainer = document.getElementById("permisos-container");
            const guardarBtn = document.getElementById("guardar-btn");

            if (idPerfil) {
                fetch(`cargar_permisos.php?id_perfil=${idPerfil}`)
                    .then(response => response.text())
                    .then(html => {
                        permisosContainer.innerHTML = html;
                        guardarBtn.style.display = "block"; // Mostrar botón "Guardar Permisos"
                    })
                    .catch(error => console.error("Error al cargar permisos:", error));
            } else {
                permisosContainer.innerHTML = "<p>Seleccione un perfil para gestionar sus permisos.</p>";
                guardarBtn.style.display = "none"; // Ocultar botón "Guardar Permisos"
            }
        });
    </script>
</body>
</html>