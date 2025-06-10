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
    <!-- Formulario Actualizar Inventario de Herramientas -->
<div class="container mx-auto px-4 py-6">
    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col items-center mb-6">
            <div class="bg-blue-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
                <i class="fas fa-box text-3xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-800">Actualizar Inventario de Producto</h2>
            <p class="text-gray-600 mt-2 text-center">Realiza movimientos de entrada, salida, traslado o retiro.</p>
        </div>

        <script>
        function mostrarCamposEspeciales() {
            const tipoMovimiento = document.getElementById('tipo_movimiento').value;
            const camposEntrada = document.getElementById('campos_entrada');
            const camposRetirado = document.getElementById('campos_retirado');
            const campoTraslado = document.getElementById('campo_hora_traslado');
            const camposSalida = document.getElementById('campos_salida');

            // Ocultar todos los campos
            camposEntrada.classList.add('hidden');
            camposRetirado.classList.add('hidden');
            campoTraslado.classList.add('hidden');
            camposSalida.classList.add('hidden');

            // Mostrar campos según tipo de movimiento
            if (tipoMovimiento == 1) {
                camposEntrada.classList.remove('hidden');
            } else if (tipoMovimiento == 2) {
                camposRetirado.classList.remove('hidden');
            } else if (tipoMovimiento == 3) {
                campoTraslado.classList.remove('hidden');
            } else if (tipoMovimiento == 'retirado') {
                camposSalida.classList.remove('hidden');
            }
        }

        function validarFormulario(event) {
            const tipoMovimiento = document.getElementById('tipo_movimiento').value;
            let valid = true;

            // Validar campos según el tipo de movimiento
            if (tipoMovimiento == 1) { // Entrada
                const cantidadEntrada = document.getElementById('cantidad_entrada').value;
                const almacenEntrada = document.getElementById('almacen_entrada').value;

                if (!cantidadEntrada || !almacenEntrada) {
                    valid = false;
                    alert('Por favor, complete todos los campos requeridos para la entrada.');
                }
            } else if (tipoMovimiento == 2) { // Salida
                const cantidadSalida = document.getElementById('cantidad_salida').value;
                const almacenOrigen = document.getElementById('almacen_origen').value;

                if (!cantidadSalida || !almacenOrigen) {
                    valid = false;
                    alert('Por favor, complete todos los campos requeridos para la salida.');
                }
            } else if (tipoMovimiento == 3) { // Traslado
                const cantidadTraslado = document.getElementById('cantidad_traslado').value;
                const almacenOrigenTraslado = document.getElementById('almacen_origen_traslado').value;
                const almacenDestinoTraslado = document.getElementById('almacen_destino_traslado').value;

                if (!cantidadTraslado || !almacenOrigenTraslado || !almacenDestinoTraslado) {
                    valid = false;
                    alert('Por favor, complete todos los campos requeridos para el traslado.');
                }
            } else if (tipoMovimiento == 'retirado') { // Retirado
                const cantidadRetirada = document.getElementById('cantidad_retirada').value;
                const almacenOrigenSalida = document.getElementById('almacen_origen_salida').value;

                if (!cantidadRetirada || !almacenOrigenSalida) {
                    valid = false;
                    alert('Por favor, complete todos los campos requeridos para el retiro.');
                }
            }

            if (!valid) {
                event.preventDefault(); // Evitar el envío del formulario si no es válido
            }
        }
    </script>
</head>
<body>
    <div class="container mx-auto p-4">
        <form action="actualizar_inventario_producto.php" method="POST" id="form-actualizar-herramienta" class="space-y-6" onsubmit="validarFormulario(event)">
            <!-- Seleccionar Herramienta -->
            <div>
                <label for="id_producto" class="block font-semibold">
                    Producto <span class="text-red-600">*</span>
                </label>
                <select name="producto" id="id_producto" required class="w-full border border-gray-300 rounded-lg p-2">
                    <?php
                    $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                    $resultado = $conexion->query("SELECT id_producto, nombre_producto FROM producto ORDER BY nombre_producto ASC");
                    while ($fila = $resultado->fetch_assoc()) {
                        echo "<option value='{$fila['id_producto']}'>{$fila['nombre_producto']}</option>";
                    }
                    $conexion->close();
                    ?>
                </select>
            </div>

            <!-- Tipo de Movimiento -->
            <div>
                <label for="tipo_movimiento" class="block font-semibold">
                    Tipo de Movimiento <span class="text-red-600">*</span>
                </label>
                <select id="tipo_movimiento" name="tipo_movimiento" required class="w-full border border-gray-300 rounded-lg p-2" onchange="mostrarCamposEspeciales()">
                    <option value="" selected>Seleccione</option>
                    <option value="1">Entrada</option>
                    <option value="2">Salida</option>
                    <option value="3">Traslado</option>
                    <option value="retirado">Retirado</option>
                </select>
            </div>

            <!-- Campos Entrada -->
            <div id="campos_entrada" class="hidden space-y-4">
                <div>
                    <label for="almacen_entrada" class="block font-semibold">Almacén <span class="text-red-600">*</span></label>
                    <select name="almacen_entrada" id="almacen_entrada" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="cantidad_entrada" class="font-semibold">Cantidad <span class="text-red-600">*</span></label>
                        <input type="number" name="cantidad_entrada" id="cantidad_entrada" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label for="precio_unitario" class="font-semibold">Precio Unitario (opcional)</label>
                        <input type="number" step="0.01" name="precio_unitario" id="precio_unitario" class="w-full border border-gray-300 rounded-lg p-2">
                    </div>
                </div>
            </div>

            <!-- Campos Retirada -->
            <div id="campos_retirado" class="hidden space-y-4">
                <div>
                    <label for="almacen_origen" class="block font-semibold">Almacén de Origen <span class="text-red-600">*</span></label>
                    <select name="almacen_origen" id="almacen_origen" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div>
                    <label for="destino_salida" class="block font-semibold">Destino (Almacén) <span class="text-red-600">*</span></label>
                    <select name="almacen_destino" id="destino_salida" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div>
                    <label for="cantidad_salida" class="block font-semibold">Cantidad a Salir <span class="text-red-600">*</span></label>
                    <input type="number" name="cantidad_salida" id="cantidad_salida" class="w-full border border-gray-300 rounded-lg p-2">
                </div>
                <div>
                    <label for="status_salida" class="block font-semibold">Estado de la Herramienta <span class="text-red-600">*</span></label>
                    <select name="status_salida" id="status_salida" class="w-full border border-gray-300 rounded-lg p-2">
                        <option value="Disponible">Disponible - La herramienta está lista para su uso</option>
                        <option value="En uso">En uso - La herramienta está siendo utilizada actualmente</option>
                        <option value="Dañada">Dañada - La herramienta presenta daños y requiere reparación</option>
                        <option value="En mantenimiento">En mantenimiento - La herramienta está en proceso de mantenimiento</option>
                        <option value="Pendiente de revisión">Pendiente de revisión - La herramienta necesita ser inspeccionada antes de su uso</option>
                        <option value="Reparación completada">Reparación completada - La herramienta ha sido reparada y está disponible</option>
                        <option value="Extraviada">Extraviada - La herramienta ha sido reportada como perdida</option>
                        <option value="Retirada">Retirada - La herramienta ha sido retirada del inventario</option>
                        <option value="Obsoleta">Obsoleta - La herramienta ya no cumple con los estándares y se ha descartado</option>
                        <option value="Reservada">Reservada - La herramienta ha sido apartada para un uso futuro</option>
                        <option value="Alquilada">Alquilada - La herramienta ha sido prestada temporalmente</option>
                        <option value="En garantía">En garantía - La herramienta está cubierta por una garantía activa</option>
                        <option value="Desactivada">Desactivada - La herramienta ha sido marcada como inactiva en el sistema</option>
                        <option value="En camino">En camino - La herramienta está en proceso de entrega a su destino</option>
                        <option value="Entregada">Entregada - La herramienta ha llegado a su destino y está disponible</option>
                    </select>
                </div>
                <div>
                    <label for="motivo_salida" class="block font-semibold">Motivo de la Salida <span class="text-red-600">*</span></label>
                    <textarea name="motivo_salida" id="motivo_salida" rows="3" class="w-full border border-gray-300 rounded-lg p-2" placeholder="Describa el motivo de la salida"></textarea>
                </div>
            </div>

            <!-- Campos Traslado -->
            <div id="campo_hora_traslado" class="hidden">
                <div>
                    <label for="cantidad_traslado" class="block font-semibold">Cantidad a Trasladar <span class="text-red-600">*</span></label>
                    <input type="number" name="cantidad_traslado" id="cantidad_traslado" class="w-full border border-gray-300 rounded-lg p-2" min="1">
                </div>
                <div>
                    <label for="almacen_origen_traslado" class="block font-semibold">Almacén de Origen <span class="text-red-600">*</span></label>
                    <select name="almacen_origen_traslado" id="almacen_origen_traslado" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div>
                    <label for="almacen_destino_traslado" class="block font-semibold">Almacén de Destino <span class="text-red-600">*</span></label>
                    <select name="almacen_destino_traslado" id="almacen_destino_traslado" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div>
                    <label for="hora_traslado" class="block font-semibold">Horas Estimadas del Traslado</label>
                    <input type="number" name="hora_traslado" id="hora_traslado" class="w-full border border-gray-300 rounded-lg p-2" min="1" step="1" placeholder="Ingrese horas">
                </div>
                <div>
                    <label for="motivo_traslado" class="block font-semibold">Motivo del Traslado</label>
                    <textarea name="motivo_traslado" id="motivo_traslado" rows="3" class="w-full border border-gray-300 rounded-lg p-2"></textarea>
                </div>
            </div>

            <!-- Campos Salida -->
            <div id="campos_salida" class="hidden space-y-4">
                <div>
                    <label for="almacen_origen_salida" class="block font-semibold">Almacén de Origen <span class="text-red-600">*</span></label>
                    <select name="almacen_origen_salida" id="almacen_origen_salida" class="w-full border border-gray-300 rounded-lg p-2">
                        <?php
                        $conexion = new mysqli("localhost", "root", "", "bd_tamanaco");
                        $resultado = $conexion->query("SELECT id_almacen, nombre FROM almacen WHERE id_status = 1");
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<option value='{$fila['id_almacen']}'>{$fila['nombre']}</option>";
                        }
                        $conexion->close();
                        ?>
                    </select>
                </div>
                <div>
                    <label for="cantidad_retirada" class="block font-semibold">Cantidad a Retirar <span class="text-red-600">*</span></label>
                    <input type="number" name="cantidad_retirada" id="cantidad_retirada" class="w-full border border-gray-300 rounded-lg p-2" min="1">
                </div>
                <small id="error-cantidad_retirada" class="text-red-500 hidden">Cantidad excede el inventario disponible</small>
                <div>
                    <label for="motivo_retiro" class="block font-semibold">Motivo del Retiro</label>
                    <select name="motivo_retiro" id="motivo_retiro" class="w-full border border-gray-300 rounded-lg p-2">
                        <option value="">Seleccione un motivo</option>
                        <option value="Obsoleta">Obsoleta</option>
                        <option value="En mantenimiento">En mantenimiento</option>
                        <option value="Extraviada">Extraviada</option>
                        <option value="Entregada">Entregada</option>
                    </select>
                </div>
                <div>
                    <label for="descripcion" class="block font-semibold">Descripción del motivo</label>
                    <textarea name="descripcion" id="descripcion" rows="3" class="w-full border border-gray-300 rounded-lg p-2"></textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between mt-6">
                <button type="submit" class="bg-green-500 text-white py-2 px-6 rounded-lg hover:bg-green-600">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
                <button type="button" onclick="location.href='inventario.php';" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600">
                    <i class="fas fa-arrow-left mr-2"></i>Regresar
                </button>
            </div>
        </form>
    </div>

<script>
    document.getElementById('cantidad_retirada').addEventListener('input', function () {
        const cantidadRetirada = parseInt(this.value) || 0; // Obtener la cantidad ingresada
        const idProducto = document.getElementById('id_producto').value; // Obtener el ID del producto
        const idAlmacen = document.getElementById('almacen_origen_salida').value; // Obtener el ID del almacén
        
        // Realizar la consulta AJAX para verificar la cantidad disponible
        fetch(`verificar_inventario.php?id_producto=${idProducto}&id_almacen=${idAlmacen}`)
            .then(response => response.json())
            .then(data => {
                const cantidadDisponible = data.cantidad_disponible || 0;
                const error = document.getElementById('error-cantidad_retirada');
                
                if (cantidadRetirada > cantidadDisponible) {
                    error.classList.remove('hidden');
                } else {
                    error.classList.add('hidden');
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>



<script>
    function mostrarCamposEspeciales() {
    const tipoMovimiento = document.getElementById("tipo_movimiento").value;

    // Ocultar todos los campos especiales
    document.getElementById("campos_entrada").classList.add("hidden");
    document.getElementById("campos_salida").classList.add("hidden");
    document.getElementById("campo_hora_traslado").classList.add("hidden");
    document.getElementById("campos_retirado").classList.add("hidden");

    // Mostrar solo los campos relevantes
    if (tipoMovimiento === "1") {
        document.getElementById("campos_entrada").classList.remove("hidden");
    } else if (tipoMovimiento === "2") {
        document.getElementById("campos_salida").classList.remove("hidden");
    } else if (tipoMovimiento === "3") {
        document.getElementById("campo_hora_traslado").classList.remove("hidden");
    } else if (tipoMovimiento === "retirado") {
        document.getElementById("campos_retirado").classList.remove("hidden");
    }
}



    // Validación básica en tiempo real
    document.getElementById('cantidad_entrada').addEventListener('input', function () {
        const error = document.getElementById('error-cantidad_entrada');
        error.classList.toggle('hidden', this.value > 0);
    });

    document.getElementById('cantidad_salida').addEventListener('input', function () {
        const error = document.getElementById('error-cantidad_salida');
        error.classList.toggle('hidden', this.value > 0);
    });
</script>

</body>
</html>