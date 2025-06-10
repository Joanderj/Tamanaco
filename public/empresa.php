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

$sql = "SELECT * FROM empresa WHERE id_empresa=1";
$result = $conexion->query($sql);
$empresa = $result->fetch_assoc();

// Consulta para contar el número de empleados
$sql_contar_empleados = "SELECT COUNT(*) AS total_empleados FROM personas";
$result_contar_empleados = $conexion->query($sql_contar_empleados);
$total_empleados = $result_contar_empleados->fetch_assoc()['total_empleados'];

// Consulta para contar el número de compras y calcular el valor total del mes actual
$sql_historial_compra = "
    SELECT COUNT(*) AS total_compras, SUM(total) AS total_valor 
    FROM historial_compra 
    WHERE MONTH(fecha_creacion) = MONTH(CURRENT_DATE()) 
      AND YEAR(fecha_creacion) = YEAR(CURRENT_DATE())
";
$result_historial_compra = $conexion->query($sql_historial_compra);
$datos_historial_compra = $result_historial_compra->fetch_assoc();

$total_compras = $datos_historial_compra['total_compras'];
$total_valor = $datos_historial_compra['total_valor'];



// Menú actual (empresa.php -> id_menu = 9)
$menu_actual = 9;

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
    <!-- Título con foto de perfil -->
    <div class="flex items-center justify-center mb-4">
        <img src="../public/img/about-1.jpg" alt="Foto de Perfil" class="w-16 h-16 rounded-full border-4 border-gray-300 shadow-lg">
    </div>
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
    <i class="fa fa-building mr-2"></i> Empresa:
    <!-- Botón de cierre como icono en la esquina superior derecha -->
    <button class="text-white text-xl ml-auto cursor-pointer hover:text-red-300" onclick="toggleSidebar()">
      <i class="fa fa-times"></i>
    </button>
  </h2>
  
  <!-- Submenús generados dinámicamente -->
 <?php foreach ($submenus_tipo_2 as $submenu): ?>
    <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
      <i class="<?php 
          echo $submenu['nombre_submenu'] === 'Sede' ? 'fa fa-map-marker-alt' :
               ($submenu['nombre_submenu'] === 'Sucursal' ? 'fa fa-store' :
               ($submenu['nombre_submenu'] === 'Almacén' ? 'fa fa-warehouse' :
               ($submenu['nombre_submenu'] === 'Planta' ? 'fa fa-industry' :
               ($submenu['nombre_submenu'] === 'Artículo' ? 'fa fa-box-open' : 'fa fa-question-circle')))); 
      ?> mr-2"></i> 
      <?php echo htmlspecialchars($submenu['nombre_submenu']); ?>
    </a>
<?php endforeach; ?>
</nav>
</div>
<hr>

<!-- Contenedor principal -->


<div class="p-6 bg-gray-50 rounded shadow-md">
<!-- Contenedor para los botones superiores -->
<div class="flex justify-start items-center space-x-4 mb-4">
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
    <?php foreach ($submenus_tipo_1 as $submenu): ?>
        <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
            <div class="flex flex-col items-center">
                <button class="<?php 
                    echo $submenu['nombre_submenu'] === 'Datos' ? 'bg-red-500 hover:bg-red-600' :
                         ($submenu['nombre_submenu'] === 'Contacto' ? 'bg-purple-500 hover:bg-purple-600' :
                         ($submenu['nombre_submenu'] === 'Redes Sociales' ? 'bg-green-500 hover:bg-green-600' :
                         ($submenu['nombre_submenu'] === 'Sobre Nosotros' ? 'bg-yellow-500 hover:bg-yellow-600' :
                         ($submenu['nombre_submenu'] === 'Blog' ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-500 hover:bg-gray-600')))); 
                ?> text-white w-16 h-16 rounded-full flex items-center justify-center">
                    <i class="<?php 
                        echo $submenu['nombre_submenu'] === 'Datos' ? 'fas fa-database' :
                             ($submenu['nombre_submenu'] === 'Contacto' ? 'fas fa-envelope' :
                             ($submenu['nombre_submenu'] === 'Redes Sociales' ? 'fas fa-share-alt' :
                             ($submenu['nombre_submenu'] === 'Sobre Nosotros' ? 'fas fa-info-circle' :
                             ($submenu['nombre_submenu'] === 'Blog' ? 'fas fa-blog' : 'fas fa-tasks')))); 
                    ?> text-xl"></i>
                </button>
                <span class="text-gray-700 text-sm mt-2"><?php echo htmlspecialchars($submenu['nombre_submenu']); ?></span>
            </div>
        </a>
    <?php endforeach; ?>
</div>


</div>
 <!-- Contenedor Principal -->
 <div class="container mx-auto px-4 py-6">
        <!-- Tarjetas de Información -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Tarjeta de Información -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-4">Total Empleado</h2>
                <div class="text-4xl font-semibold text-blue-500">
                    <?php echo htmlspecialchars($total_empleados); ?>
                </div>
                <p class="text-gray-500 mt-2">Empleados estimados</p>
            </div>
            <!-- Segunda Tarjeta -->
            <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-lg font-bold mb-4">Gastos Mensuales</h2>
    <div class="text-4xl font-semibold text-green-500">
        $<?php echo htmlspecialchars(number_format($total_valor, 2)); ?>
    </div>
    <p class="text-gray-500 mt-2">Datos estimados</p>
</div>
            <!-- Tercera Tarjeta -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-lg font-bold mb-4">Mantenimientos Activos</h2>
                <div class="text-4xl font-semibold text-yellow-500">
                    12
                </div>
                <p class="text-gray-500 mt-2">Proyectos en curso</p>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-4">Análisis de Desempeño</h2>
            <canvas id="desempenoChart" class="w-full h-64"></canvas>
        </div>

        <!-- Tabla -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-lg font-bold mb-4">Listado de Empresas Asociadas</h2>
            <table class="table-auto w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b px-4 py-2">ID</th>
                        <th class="border-b px-4 py-2">Nombre</th>
                        <th class="border-b px-4 py-2">País</th>
                        <th class="border-b px-4 py-2">Estado</th>
                        <th class="border-b px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border-b px-4 py-2">1</td>
                        <td class="border-b px-4 py-2">Tamanaco Corp</td>
                        <td class="border-b px-4 py-2">Venezuela</td>
                        <td class="border-b px-4 py-2">Portuguesa</td>
                        <td class="border-b px-4 py-2">
                            <button class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Editar</button>
                            <button class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Eliminar</button>
                        </td>
                    </tr>
                    <!-- Agregar más filas si es necesario -->
                </tbody>
            </table>
        </div>
    </div>
    
    <?php
    // Verificar si el RIF está completo (letra + número)
$rif_completo = !empty($empresa['rif']) && !empty($empresa['numero_rif']);

// Si no está completo, mostrar modal y bloquear la interfaz
if (!$rif_completo) {
    echo '
    <div id="rif-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96">
            <h3 class="text-xl font-bold mb-4">Registro de RIF</h3>
            <form id="rif-form" action="guardar_rif.php" method="POST">
                <div class="mb-4">
                    Ingrese los Datos del RIF de La Empresa para continuar (OJO no es Editable)
                    <label class="block text-gray-700 mb-2">Letra del RIF</label>
                    <select 
                        name="rif_letra" 
                        class="w-full p-2 border border-gray-300 rounded" 
                        required
                        title="Seleccione una letra">
                        <option value="">Seleccione una letra</option>
                        <option value="J">J</option>
                        <option value="G">G</option>
                        <option value="V">V</option>
                        <option value="E">E</option>
                        <option value="P">P</option>
                        <option value="C">C</option>
                        <option value="R">R</option>
                        <option value="S">S</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Número del RIF</label>
                    <input 
                        type="text" 
                        name="rif_numero" 
                        class="w-full p-2 border border-gray-300 rounded" 
                        required 
                        pattern="\d{8,10}" 
                        title="Debe contener entre 8 y 10 dígitos numéricos"
                    >
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="submit" 
                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Reemplazar caracteres no numéricos en el campo del número del RIF
        document.querySelector("input[name=\'rif_numero\']").addEventListener("input", function (e) {
            this.value = this.value.replace(/[^0-9]/g, ""); // Reemplazar caracteres no numéricos
        });

        // Bloquear el resto de la interfaz
        document.body.style.overflow = "hidden";
    </script>
    ';
    // Opcional: Detener la carga del resto del HTML si no hay RIF
    exit();
}
?>



    <!-- Librerías JS -->
    <script src="../public/js/chart.js"></script>
    <script>
        // Configurar Gráfico
        const ctx = document.getElementById('desempenoChart').getContext('2d');
        const desempenoChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
                datasets: [{
                    label: 'Desempeño',
                    data: [20, 40, 50, 70, 100],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Meses'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Progreso (%)'
                        }
                    }
                }
            }
        });
    </script>
</body>

  
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



