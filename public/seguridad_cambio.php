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

</head>
<body>

<!-- Formulario -->
<form method="POST" id="formularioLogin" action="guardar_seguridad.php">
    
    <!-- Contraseña y Confirmación -->
<div class="grid grid-cols-2 gap-6 mb-6">
    <div class="relative">
        <label for="password" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-key text-orange-500 mr-2"></i> Contraseña: <span class="text-red-600">*</span>
        </label>
        <input type="password" id="password" name="clave" class="w-full border border-gray-300 rounded-lg p-3 pr-10" required onkeyup="verificarSeguridad()">
        <button type="button" onclick="togglePassword('password', 'togglePasswordIcon1')" class="absolute top-10 right-3 text-gray-500">
            <i id="togglePasswordIcon1" class="fas fa-eye"></i>
        </button>
        <small class="text-gray-500 block mt-1">La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.</small>
    </div>

    <div class="relative">
        <label for="confirm_password" class="block font-semibold text-lg flex items-center">
            <i class="fas fa-lock text-purple-500 mr-2"></i> Confirmar Contraseña: <span class="text-red-600">*</span>
        </label>
        <input type="password" id="confirm_password" name="clave2" class="w-full border border-gray-300 rounded-lg p-3 pr-10" required>
        <button type="button" onclick="togglePassword('confirm_password', 'togglePasswordIcon2')" class="absolute top-10 right-3 text-gray-500">
            <i id="togglePasswordIcon2" class="fas fa-eye"></i>
        </button>
        <small id="mensaje-error-contraseña" class="text-red-500 hidden">Las contraseñas no coinciden</small>
<small id="mensaje-exito-contraseña" class="text-green-500 hidden">¡Las contraseñas coinciden!</small>
    </div>
</div>

<script>
function validarCoincidencia() {
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm_password").value;
    const mensajeError = document.getElementById("mensaje-error-contraseña");
    const mensajeExito = document.getElementById("mensaje-exito-contraseña");

    if (password === confirmPassword && password !== "") {
        mensajeExito.classList.remove("hidden");
        mensajeError.classList.add("hidden");
    } else {
        mensajeError.classList.remove("hidden");
        mensajeExito.classList.add("hidden");
    }
}

// Validación en tiempo real en ambos campos
document.getElementById("password").addEventListener("keyup", validarCoincidencia);
document.getElementById("confirm_password").addEventListener("keyup", validarCoincidencia);



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

<!-- Nivel de seguridad de contraseña -->
<div class="mb-6">
    <label class="block font-semibold text-lg flex items-center">
        <i class="fas fa-shield-alt text-red-500 mr-2"></i> Nivel de Seguridad:
    </label>
    <div class="w-full bg-gray-300 rounded-lg overflow-hidden">
        <div id="barraSeguridad" class="h-3 w-1/5 bg-red-500 transition-all duration-300"></div>
    </div>
    <p id="nivelSeguridadTexto" class="text-center text-gray-700 font-semibold mt-2">Bajo</p>
</div>

<script>
    function verificarSeguridad() {
        const password = document.getElementById("password").value;
        const barraSeguridad = document.getElementById("barraSeguridad");
        const nivelSeguridadTexto = document.getElementById("nivelSeguridadTexto");

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

        nivelSeguridadTexto.textContent = nivel;
        barraSeguridad.className = `h-3 ${color} transition-all duration-300`;
        barraSeguridad.style.width = ancho;
    }
</script>
 <!-- Nota sobre campos obligatorios --> 
 <p class="text-gray-500 text-sm mt-4">Todos los campos marcados con <span class="text-red-600">*</span> son obligatorios.</p>

    <input type="hidden" id="id" name='id' value='<?php echo $perfil['id_usuario'];?>'/>

    <!-- Botón Guardar -->
    <div class="flex justify-center mt-6">
 <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
    Guardar Cambios
    </button>
    </div>
</form>

<script>
    function validatePasswords() {
        const claveInput = document.getElementById('clave').value;
        const clave2Input = document.getElementById('clave2').value;
        const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');
        const mensajeFuerza = document.getElementById('mensajeFuerza');
        const btnAccion = document.getElementById('btnAccion');

        // Validación de coincidencia de contraseñas
        if (claveInput === clave2Input) {
            mensajeConfirmacion.textContent = "Las contraseñas coinciden.";
            mensajeConfirmacion.style.color = "green";
        } else {
            mensajeConfirmacion.textContent = "Las contraseñas no coinciden.";
            mensajeConfirmacion.style.color = "red";
            btnAccion.disabled = true; // Deshabilitar botón si no coinciden
            return; // Salir de la función
        }

        // Validación de fuerza de contraseña
        const fuerza = validarFuerzaContraseña(claveInput);
        mensajeFuerza.textContent = fuerza.mensaje;
        mensajeFuerza.style.color = fuerza.color;

        // Habilitar o deshabilitar el botón según la fuerza de la contraseña
        btnAccion.disabled = !fuerza.fuerte;
    }

    function validarFuerzaContraseña(clave) {
        let mensaje = "";
        let color = "red"; // Color por defecto
        let fuerte = false;

        const tieneLetrasMayusculas = /[A-Z]/.test(clave);
        const tieneLetrasMinusculas = /[a-z]/.test(clave);
        const tieneNumeros = /[0-9]/.test(clave);
        const tieneSimbolo = /[!@#$%^&*(),.?":{}|<>]/.test(clave);
        
        if (clave.length >= 12 && tieneLetrasMayusculas && tieneLetrasMinusculas && tieneNumeros && tieneSimbolo) {
            mensaje = "Fuerza de contraseña: Alta";
            color = "green";
            fuerte = true;
        } else if (clave.length >= 8 && (tieneLetrasMayusculas || tieneLetrasMinusculas || tieneNumeros || tieneSimbolo)) {
            mensaje = "Fuerza de contraseña: Media";
            color = "orange";
            fuerte = false;
        } else {
            mensaje = "Fuerza de contraseña: Baja";
            color = "red";
            fuerte = false;
        }

        return { mensaje, color, fuerte };
    }
</script>



</body>
</html>



