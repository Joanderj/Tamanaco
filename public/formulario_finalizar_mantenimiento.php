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
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

$id_tarea = $_GET['id_tarea'] ?? null;

if (!$id_tarea) {
    $_SESSION['mensaje_error'] = "ID de tarea no válido.";
    header("Location: tareas.php");
    exit;
}

// Obtener datos de la tarea
$sql = "SELECT t.id_tarea, t.titulo_tarea, t.descripcion_tarea, tm.nombre_tipo AS tipo_mantenimiento, 
        t.fecha_inicio, t.hora_inicio, t.fecha_fin, t.hora_fin, s.nombre_status AS status
        FROM tareas t
        JOIN tipo_mantenimiento tm ON t.tipo_mantenimiento_id  = tm.id_tipo
        JOIN status s ON t.status_id = s.id_status
        WHERE t.id_tarea = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_tarea]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    $_SESSION['mensaje_error'] = "Tarea no encontrada.";
    header("Location: tareas.php");
    exit;
}

// Leer y limpiar mensajes de éxito o error
$mensaje_exito = $_SESSION['mensaje_exito'] ?? "";
$mensaje_error = $_SESSION['mensaje_error'] ?? "";
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>

<!-- Mensaje de éxito -->
<?php if (!empty($mensaje_exito)): ?>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-green-100 p-4 rounded-full shadow-lg animate-pulse">
                    <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-bold text-green-600 mb-2">¡Éxito!</h2>
                <p class="text-gray-700"><?= htmlspecialchars($mensaje_exito, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" 
                    class="absolute top-2 right-2 bg-green-500 hover:bg-green-600 text-white rounded-full p-2 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Mensaje de error -->
<?php if (!empty($mensaje_error)): ?>
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 p-4 rounded-full shadow-lg animate-pulse">
                    <i class="fas fa-exclamation-triangle text-red-500 text-4xl"></i>
                </div>
            </div>
            <div class="text-center">
                <h2 class="text-xl font-bold text-red-600 mb-2">¡Error!</h2>
                <p class="text-gray-700"><?= htmlspecialchars($mensaje_error, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <button onclick="this.parentElement.parentElement.style.display='none'" 
                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Contenedor principal -->
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-tools text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800">Finalizar Mantenimiento</h2>
            <p class="text-gray-600 mt-2 text-center">Verifica los datos y añade la observación antes de finalizar.</p>
        </div>
        <!-- Formulario para finalizar -->
        <?php

include 'db_connection.php';
date_default_timezone_set('America/Caracas');

// Obtener datos iniciales de la tarea
$id_tarea = $_GET['id_tarea'] ?? null;

if (!$id_tarea) {
    die("ID de tarea no especificado.");
}

$stmt = $conn->prepare("SELECT * FROM tareas WHERE id_tarea = ?");
$stmt->execute([$id_tarea]);
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    die("Tarea no encontrada.");
}
?>
    <h2 class="text-xl font-bold text-gray-800 mb-4">Finalizar Mantenimiento</h2>
    <form id="finalizarForm" action="finalizar_mantenimiento_procesar.php" method="POST" class="space-y-4">
        <input type="hidden" name="id_tarea" value="<?= htmlspecialchars($id_tarea) ?>">
        <input type="hidden" id="fecha_fin_programada" value="<?= htmlspecialchars($tarea['fecha_fin']) ?>">

        <div>
            <label for="observacion" class="block font-semibold flex items-center">
                <i class="fas fa-comment-alt text-yellow-500 mr-2"></i>
                Observación:
            </label>
            <textarea id="observacion" name="observacion" rows="3" class="w-full border border-gray-300 rounded-lg p-2" required></textarea>
        </div>

        <div>
            <label for="fecha_hora_finalizacion" class="block font-semibold flex items-center">
                <i class="fas fa-clock text-yellow-500 mr-2"></i>
                Fecha y Hora de Finalización:
            </label>
            <input type="datetime-local" id="fecha_hora_finalizacion" name="fecha_hora_finalizacion" class="w-full border border-gray-300 rounded-lg p-2" value="<?= (new DateTime('now', new DateTimeZone('America/Caracas')))->format('Y-m-d\TH:i'); ?>" required>
        </div>

        <div>
            <label class="block font-semibold flex items-center">
                <i class="fas fa-calendar-check text-yellow-500 mr-2"></i>
                Fecha de Finalización (programada):
            </label>
            <input type="text" class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100" value="<?= htmlspecialchars($tarea['fecha_fin']) ?>" readonly>
        </div>

        <div class="flex justify-between mt-4 space-x-4">
            <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-lg flex items-center hover:bg-green-700 transition-all duration-300">
                <i class="fas fa-check mr-2"></i> Finalizar Mantenimiento
            </button>
            <button type="button" onclick="location.href='formulario_guardar_actividad.php?id=<?= htmlspecialchars($id_tarea) ?>';" class="bg-gray-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-gray-600 transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Cancelar
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('finalizarForm2').addEventListener('submit', function(e) {
        const programada = new Date(document.getElementById('fecha_fin_programada').value);
        const actual = new Date(document.getElementById('fecha_hora_finalizacion').value);

        if (actual < programada) {
            e.preventDefault();
            Swal.fire({
                title: '¿Finalizar antes de la fecha programada?',
                text: 'La fecha actual es anterior a la programada. ¿Deseas continuar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, finalizar ahora',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('finalizarForm').submit();
                }
            });
        }
    });
</script>


    </div>
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
</body>
</html>

