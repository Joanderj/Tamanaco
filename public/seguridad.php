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
$usuario = $_SESSION['username'];

// Consulta para obtener el perfil del usuario
$sql = "SELECT * FROM usuarios u JOIN personas p ON u.id_persona = p.id_persona WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();


// Menú actual (empresa.php -> id_menu = 9)
$menu_actual = 8;

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
    WHERE s.id_status = 1 AND ps.id_status = 1 AND ps.id_perfil = ? AND s.tipo_submenu = 1 and s.id_menu = 8
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
        <img src="perfil.jpg" alt="Foto de Perfil" class="w-16 h-16 rounded-full border-4 border-gray-300 shadow-lg">
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
          <i class="fa fa-home mr-2"></i> Inicio:
          <!-- Botón de cierre como icono en la esquina superior derecha -->
          <button class="text-white text-xl ml-auto cursor-pointer hover:text-red-300" onclick="toggleSidebar()">
            <i class="fa fa-times"></i>
          </button>
        </h2>
      
        <!-- Submenús con iconos y diseños hover -->
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-user mr-2"></i> Empleado
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-box mr-2"></i> Inventario
        </a>
        <hr class="border-gray-300 my-2">

        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-tools mr-2"></i> Mantenimiento
        </a>
        <a href="#" class="flex items-center py-2 px-2 text-gray-700 hover:text-blue-500 hover:bg-blue-100 rounded transition duration-200">
          <i class="fa fa-chart-bar mr-2"></i> Reportes
        </a>
      </nav>
</div>
<hr>

<div class="p-6 bg-gray-50 rounded">
<div class="flex flex-wrap justify-around space-y-4 md:space-y-0 md:space-x-6">
    <?php foreach ($submenus_tipo_1 as $submenu): ?>
      <a href="<?php echo htmlspecialchars($submenu['url_submenu']); ?>">
        <div class="flex flex-col items-center">
          <!-- Botón dinámico -->
          <button class="
            <?php 
              echo $submenu['nombre_submenu'] === 'Datos Personales' ? 'bg-red-500 hover:bg-red-600' : 
                   ($submenu['nombre_submenu'] === 'Dirección de Habitación' ? 'bg-blue-500 hover:bg-blue-600' : 
                   ($submenu['nombre_submenu'] === 'Seguridad' ? 'bg-yellow-500 hover:bg-yellow-600' : 
                   ($submenu['nombre_submenu'] === 'Correo' ? 'bg-purple-500 hover:bg-purple-600' : 
                   ($submenu['nombre_submenu'] === 'Teléfono' ? 'bg-green-500 hover:bg-green-600' : 
                   ($submenu['nombre_submenu'] === 'Actividad' ? 'bg-indigo-500 hover:bg-indigo-600' : 
                   'bg-gray-500 hover:bg-gray-600')))));
            ?> 
            text-white w-16 h-16 rounded-full flex items-center justify-center">
            <i class="
              <?php 
                echo $submenu['nombre_submenu'] === 'Datos Personales' ? 'fas fa-user' : 
                     ($submenu['nombre_submenu'] === 'Dirección de Habitación' ? 'fas fa-home' : 
                     ($submenu['nombre_submenu'] === 'Seguridad' ? 'fas fa-lock' : 
                     ($submenu['nombre_submenu'] === 'Correo' ? 'fas fa-envelope' : 
                     ($submenu['nombre_submenu'] === 'Teléfono' ? 'fas fa-phone' : 
                     ($submenu['nombre_submenu'] === 'Actividad' ? 'fas fa-chart-line' : 
                     'fas fa-tasks')))));
              ?>
              text-xl"></i>
          </button>
          <!-- Etiqueta del botón -->
          <span class="text-gray-700 text-sm mt-2"><?php echo htmlspecialchars($submenu['nombre_submenu']); ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
</div>
<div class="container mx-auto px-4 py-6">
<div class="container mx-auto max-w-4xl p-8 bg-white rounded-lg shadow-md">
<!-- Título con icono de seguridad -->
<div class="flex flex-col items-center mb-6">
    <div class="bg-yellow-500 text-white w-16 h-16 rounded-full flex items-center justify-center shadow-lg mb-4">
        <i class="fas fa-shield-alt text-3xl"></i>
    </div>
    <h2 class="text-3xl font-extrabold text-gray-800">Seguridad</h2>
    <p class="text-gray-600 mt-2 text-center">Protege tu cuenta y mantén tus datos seguros.</p>
</div>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Agrega tus estilos aquí */
    </style>
    <script>
        function validatePassword() {
            const usuarioInput = document.getElementById('usuario').value;
            const claveInput = document.getElementById('clave').value;
            const mensaje = document.getElementById('mensaje');

            // Realizar una solicitud AJAX para verificar la contraseña
            fetch('verificar_contraseña.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ usuario: usuarioInput, clave: claveInput })
            })
            .then(response => response.json())
            .then(data => {
                const btnAccion = document.getElementById('btnAccion');
                if (data.valid) {
                    btnAccion.disabled = false;
                    btnAccion.onclick = function() {
                        window.location.href = 'seguridad_cambio.php';
                    };
                    mensaje.textContent = "La clave es correcta.";
                    mensaje.style.color = "green";
                } else {
                    btnAccion.disabled = true;
                    mensaje.textContent = "La clave es errónea.";
                    mensaje.style.color = "red";
                }
            });
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</head>
<body>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Leer el mensaje de éxito desde la sesión
$mensaje_exito = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : "";

// Limpiar el mensaje de éxito después de mostrarlo
unset($_SESSION['mensaje']);
?>

<!-- Mostrar mensaje de éxito si existe -->
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

<form method="POST" id="formularioLogin">   
    <!-- Contraseña actual -->
    <div class="relative mb-6">
        <label for="clave" class="block font-medium text-gray-600">Contraseña actual:</label>
        <div class="flex items-center">
            <input type="password" id="clave" name="clave" class="w-full border border-gray-300 rounded-lg p-2 pr-10" required>
            <button type="button" onclick="togglePassword('clave', 'togglePasswordIcon2')" class="absolute right-2 p-2 text-gray-600">
                <i id="togglePasswordIcon2" class="fas fa-eye"></i>
            </button>
        </div>
        <button type="button" onclick="verificarClaveActual()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-1 px-4 rounded">
            Verificar
        </button>
        <small id="mensaje-validacion" class="text-sm mt-1 block text-gray-600"></small>
    </div>

    <!-- Contenedor oculto: solo se muestra si la clave es válida -->
    <div id="contenedorNuevaClave" class="hidden">

        <!-- Nueva contraseña -->
        <div class="relative mb-6">
            <label for="password" class="block font-medium text-gray-600">Nueva Contraseña:</label>
            <div class="flex items-center relative">
                <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-lg p-3 pr-10"
                    oninput="verificarSeguridad(); validarCoincidencia(); toggleBoton();" required>
                <button type="button" onclick="togglePassword('password', 'iconPassword')" class="absolute right-2 p-2 text-gray-600">
                    <i id="iconPassword" class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <!-- Confirmar contraseña -->
        <div class="relative mb-2">
            <label for="confirm_password" class="block font-medium text-gray-600">Confirmar Contraseña:</label>
            <div class="flex items-center relative">
                <input type="password" id="confirm_password" name="confirm_password" class="w-full border border-gray-300 rounded-lg p-3 pr-10"
                    oninput="validarCoincidencia(); toggleBoton();" required>
                <button type="button" onclick="togglePassword('confirm_password', 'iconConfirm')" class="absolute right-2 p-2 text-gray-600">
                    <i id="iconConfirm" class="fas fa-eye"></i>
                </button>
            </div>
            <small id="mensaje-error-contraseña" class="text-red-500 hidden">Las contraseñas no coinciden.</small>
            <small id="mensaje-exito-contraseña" class="text-green-600 hidden">Las contraseñas coinciden.</small>
        </div>

        <!-- Nivel de Seguridad -->
        <div class="mb-6">
            <label class="block font-semibold text-lg flex items-center">
                <i class="fas fa-shield-alt text-red-500 mr-2"></i> Nivel de Seguridad:
            </label>
            <div class="w-full bg-gray-300 rounded-lg overflow-hidden">
                <div id="barraSeguridad" class="h-3 w-1/5 bg-red-500 transition-all duration-300"></div>
            </div>
            <p id="nivelSeguridadTexto" class="text-center text-gray-700 font-semibold mt-2">Bajo</p>
        </div>

        <!-- Botón Guardar -->
        <div class="flex justify-center mt-6">
            <button type="submit" id="btnAccion"
                class="bg-green-500 text-white py-2 px-6 rounded-lg flex items-center hover:bg-green-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-300 disabled:bg-gray-400 disabled:text-gray-200 disabled:cursor-not-allowed"
                disabled>
                <i class="fas fa-save mr-2"></i>
                <span id="btnTexto">Guardar Nueva Contraseña</span>
            </button>
        </div>
    </div>

    <input type="hidden" name="id" id="id" value="<?php echo $perfil['id_usuario']; ?>">
</form>
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

function validarCoincidencia() {
    const pass = document.getElementById("password").value.trim();
    const confirm = document.getElementById("confirm_password").value.trim();
    const error = document.getElementById("mensaje-error-contraseña");
    const success = document.getElementById("mensaje-exito-contraseña");

    if (pass === "" || confirm === "") {
        error.classList.add("hidden");
        success.classList.add("hidden");
        return false;
    }

    if (pass === confirm) {
        error.classList.add("hidden");
        success.classList.remove("hidden");
        return true;
    } else {
        success.classList.add("hidden");
        error.classList.remove("hidden");
        return false;
    }
}

function verificarSeguridad() {
    const password = document.getElementById("password").value;
    const barra = document.getElementById("barraSeguridad");
    const texto = document.getElementById("nivelSeguridadTexto");

    let nivel = "Bajo";
    let ancho = "20%";
    let color = "bg-red-500";

    if (password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password)) {
        nivel = "Intermedio";
        ancho = "60%";
        color = "bg-yellow-500";
    }

    if (password.length >= 12 && /[!@#$%^&*]/.test(password)) {
        nivel = "Alto";
        ancho = "100%";
        color = "bg-green-500";
    }

    barra.className = `h-3 transition-all duration-300 ${color}`;
    barra.style.width = ancho;
    texto.textContent = nivel;

    return nivel;
}

function toggleBoton() {
    const coinciden = validarCoincidencia();
    const nivel = verificarSeguridad();
    const boton = document.getElementById("btnAccion");
    boton.disabled = !(coinciden && (nivel === "Intermedio" || nivel === "Alto"));
}

function verificarClaveActual() {
    const clave = document.getElementById("clave").value;
    const id = document.getElementById("id").value;
    const mensaje = document.getElementById("mensaje-validacion");

    if (clave === "") {
        mensaje.textContent = "Ingresa tu contraseña actual.";
        mensaje.classList.replace("text-green-600", "text-red-500");
        return;
    }

    // Petición al servidor
    fetch("verificar_clave.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `clave=${encodeURIComponent(clave)}&id=${id}`
    })
    .then(response => response.text())
    .then(data => {
        if (data === "ok") {
            mensaje.textContent = "Contraseña verificada.";
            mensaje.classList.replace("text-red-500", "text-green-600");
            document.getElementById("contenedorNuevaClave").classList.remove("hidden");
        } else {
            mensaje.textContent = "Contraseña incorrecta.";
            mensaje.classList.replace("text-green-600", "text-red-500");
        }
    });
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







