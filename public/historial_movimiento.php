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
<?php
include 'db_connection.php'; // Asegúrate que aquí se defina $pdo y no $conn

$sql = "SELECT mr.*, r.nombre_repuesto, 
        ao.nombre AS origen, 
        ad.nombre AS destino, 
        tm.nombre_tipo_movimiento 
        FROM movimiento_repuesto mr
        LEFT JOIN repuesto r ON mr.id_repuesto = r.id_repuesto
        LEFT JOIN almacen ao ON mr.id_almacen_origen = ao.id_almacen
        LEFT JOIN almacen ad ON mr.id_almacen_destino = ad.id_almacen
        LEFT JOIN tipo_movimiento tm ON mr.id_tipo_movimiento = tm.id_tipo_movimiento
        ORDER BY mr.fecha_movimiento DESC";

try {
    $stmt = $conn->query($sql);

    $movimientos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>

<div class="p-6 bg-white rounded-xl shadow-md border border-gray-200 overflow-x-auto">
  <h2 class="text-xl font-semibold text-gray-700 mb-4 flex items-center gap-2">
    <i data-lucide="history" class="w-5 h-5 text-blue-500"></i> Historial de Movimiento de Repuestos
  </h2>
  <table class="min-w-full table-auto text-sm text-left text-gray-700">
    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
      <tr>
        <th class="px-4 py-2">#</th>
        <th class="px-4 py-2">Repuesto</th>
        <th class="px-4 py-2">Origen</th>
        <th class="px-4 py-2">Destino</th>
        <th class="px-4 py-2">Cantidad</th>
        <th class="px-4 py-2">Fecha</th>
        <th class="px-4 py-2">Tipo</th>
        <th class="px-4 py-2">Descripción</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <?php
      $contador = 1;
      foreach ($movimientos as $row):
      ?>
        <tr>
          <td class="px-4 py-2 font-medium text-gray-800"><?= $contador++ ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['nombre_repuesto']) ?></td>
          <td class="px-4 py-2">
            <?= $row['origen'] ? htmlspecialchars($row['origen']) : '<span class="text-gray-400 italic">—</span>' ?>
          </td>
          <td class="px-4 py-2">
            <?= $row['destino'] ? htmlspecialchars($row['destino']) : '<span class="text-gray-400 italic">—</span>' ?>
          </td>
          <td class="px-4 py-2"><?= $row['cantidad'] ?></td>
          <td class="px-4 py-2"><?= date('Y-m-d', strtotime($row['fecha_movimiento'])) ?></td>
          <td class="px-4 py-2 capitalize"><?= htmlspecialchars($row['nombre_tipo_movimiento']) ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['descripcion']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>






