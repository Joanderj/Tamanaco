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

// Consulta para obtener los datos de la empresa con id = 1
$id_empresa = 1;
$query = "SELECT * FROM empresa WHERE id_empresa = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$result = $stmt->get_result();

$empresa = $result->fetch_assoc(); // Obtener los datos de la empresa, si existen
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
  <!-- Encabezado -->
  <div class="text-center mb-6">
    <h2 class="text-xl font-bold text-purple-600">
      <i class="fas fa-file-signature mr-2"></i> Solicitudes
    </h2>
    <p class="text-gray-600 text-sm">Revisa y gestiona las solicitudes de mantenimiento.</p>
  </div>
  <div class="p-6 bg-gray-50 rounded shadow-md">
  <!-- Botones superiores -->
  

  <!-- Filtros -->
  <div class="flex items-center space-x-4 mb-4 border border-gray-300 p-2 rounded-lg">
    <!-- Input estilizado -->
    <input
      type="text"
      id="searchInput"
      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
      placeholder="Buscar en la tabla..."
      onkeyup="filterTable()"
    />
    <!-- Botón Filtrar -->
    <button
      id="filterButton"
      class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 flex items-center"
      onclick="toggleFilterForm()">
      <i class="fas fa-filter mr-2"></i> Filtrar
    </button>
  </div>
<?php
include 'db_connection.php';

$id_perfil = $_SESSION['id_perfil'] ?? null;
if (!$id_perfil) {
  echo "Acceso no autorizado.";
  exit;
}

try {
  $stmt = $conn->prepare("
    SELECT 
      s.id_solicitud,
      ts.nombre_tipo AS tipo_solicitud,
      s.fecha_solicitud,
      st.nombre_status AS estatus,
      u.usuario
    FROM solicitudes s
    JOIN tipos_solicitudes ts ON s.id_tipo_solicitud = ts.id_tipo_solicitud
    JOIN status st ON s.id_status = st.id_status
    JOIN usuarios u ON s.id_usuario = u.id_usuario
    WHERE s.id_perfil = :id_perfil
    ORDER BY s.fecha_solicitud DESC
  ");
  $stmt->execute([':id_perfil' => $id_perfil]);
  $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  echo "Error al cargar solicitudes: " . $e->getMessage();
  exit;
}
?>

<!-- Contenedor principal -->
<div class="border border-gray-300 rounded-lg shadow-md p-4">
  <div class="flex justify-between items-center mb-4 border-b border-gray-300 pb-2">
    <h1 class="text-xl font-semibold text-gray-700">Solicitudes</h1>
    <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center">
      <i class="fas fa-sort mr-2"></i> Clasificar
    </button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-indigo-500 text-white">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Solicitante</th>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Tipo</th>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha</th>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estatus</th>
          <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acciones</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($solicitudes as $sol): ?>
        <tr class="hover:bg-gray-100">
          <td class="px-6 py-4">#<?= $sol['id_solicitud'] ?></td>
          <td class="px-6 py-4"><?= htmlspecialchars($sol['usuario']) ?></td>
          <td class="px-6 py-4 capitalize"><?= htmlspecialchars($sol['tipo_solicitud']) ?></td>
          <td class="px-6 py-4"><?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></td>
          <td class="px-6 py-4">
            <span class="px-3 py-1 rounded-full text-sm font-medium 
              <?= $sol['estatus'] === 'Pendiente' ? 'bg-yellow-100 text-yellow-700' :
                   ($sol['estatus'] === 'Aprobado' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700') ?>">
              <?= htmlspecialchars($sol['estatus']) ?>
            </span>
          </td>
          <td class="px-6 py-4 space-x-2">
            <button onclick="verSolicitud(<?= $sol['id_solicitud'] ?>, '<?= strtolower($sol['tipo_solicitud']) ?>')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow">
              Ver
            </button>
            <?php if ($sol['tipo_solicitud'] === 'Mantenimiento'): ?>
              <button onclick="imprimir_solicitud_mantenimiento(<?= $sol['id_solicitud'] ?>)" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded shadow">
                <i class="fa fa-print"></i> Imprimir
              </button>
            <?php elseif ($sol['tipo_solicitud'] === 'Compra'): ?>
              <button onclick="imprimir_solicitud_compra(<?= $sol['id_solicitud'] ?>)" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded shadow">
                <i class="fa fa-print"></i> Imprimir
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-4 flex items-center justify-between">
    <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Anterior</button>
    <div class="flex space-x-2">
      <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">1</button>
      <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">2</button>
      <span class="text-gray-500">...</span>
      <button class="border border-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Siguiente</button>
    </div>
    <button class="border border-blue-500 text-blue-500 px-4 py-2 rounded hover:bg-blue-50">Elementos por página</button>
  </div>
</div>

<!-- MODALES PROFESIONALES -->
<div id="modalMantenimiento" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg w-[90%] max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-700">Detalle de Mantenimiento</h2>
      <button onclick="cerrarModal()" class="text-gray-600 hover:text-red-600 text-xl">&times;</button>
    </div>
    <div id="contenidoMantenimiento" class="text-gray-700 space-y-2"></div>
    <div class="mt-4 text-right space-x-2">
      <button onclick="actualizarEstado('aceptar')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Aceptar</button>
      <button onclick="actualizarEstado('denegar')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Denegar</button>
    </div>
  </div>
</div>

<div id="modalCompra" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg w-[90%] max-w-md">
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-gray-700">Detalle de Compra</h2>
      <button onclick="cerrarModal()" class="text-gray-600 hover:text-red-600 text-xl">&times;</button>
    </div>
    <div id="contenidoCompra" class="text-gray-700 space-y-2"></div>
    <div class="mt-4 text-right space-x-2">
      <button onclick="actualizarEstado('aceptar')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Aceptar</button>
      <button onclick="actualizarEstado('denegar')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Denegar</button>
    </div>
  </div>
</div>

<script>
let solicitudActualId = null;
let tipoActual = null;

function verSolicitud(id, tipo) {
  solicitudActualId = id;
  tipoActual = tipo;
  const endpoint = tipo === 'mantenimiento' ? 'detalle_mantenimiento_ajax.php' : 'detalle_compra_ajax.php';

  fetch(`${endpoint}?id_solicitud=${id}`)
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }

      if (tipo === 'mantenimiento') {
        document.getElementById('contenidoMantenimiento').innerHTML = `
          <p><strong>Título:</strong> ${data.titulo_tarea}</p>
          <p><strong>Descripción:</strong> ${data.descripcion_tarea}</p>
          <p><strong>Fecha inicio:</strong> ${data.fecha_inicio} ${data.hora_inicio}</p>
          <p><strong>Fecha fin:</strong> ${data.fecha_fin} ${data.hora_fin}</p>
          <p><strong>Tipo:</strong> ${data.tipo_mantenimiento_id}</p>
        `;
        document.getElementById('modalMantenimiento').classList.remove('hidden');
      } else {
        document.getElementById('contenidoCompra').innerHTML = `
          <p><strong>Código:</strong> ${data.codigo_compra}</p>
          <p><strong>Total productos:</strong> ${data.total_productos}</p>
          <p><strong>Total precio:</strong> $${data.total_precio}</p>
          <p><strong>Fecha:</strong> ${data.fecha_compra}</p>
        `;
        document.getElementById('modalCompra').classList.remove('hidden');
      }
    })
    .catch(err => {
      console.error(err);
      alert("Error al cargar los datos.");
    });
}

function actualizarEstado(accion) {
  fetch('actualizar_estado_solicitud.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id_solicitud=${solicitudActualId}&accion=${accion}&tipo=${tipoActual}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Solicitud actualizada correctamente.");
      cerrarModal();
      location.reload();
    } else {
      alert(data.error || "Error al actualizar.");
    }
  })
  .catch(err => {
    console.error(err);
    alert("Error al procesar la solicitud.");
  });
}

function cerrarModal() {
  document.getElementById('modalMantenimiento').classList.add('hidden');
  document.getElementById('modalCompra').classList.add('hidden');
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



