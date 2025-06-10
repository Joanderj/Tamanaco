<?php
session_start();

// Verificar si hay un mensaje de error o si se envió un perfil
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['perfil'])) {
        $error_message = "Debe seleccionar un perfil antes de continuar.";
    } else {
        $id_perfil = $_POST['perfil'];
        $_SESSION['id_perfil'] = $id_perfil;

        // Conexión a la base de datos
        $conn = new mysqli("localhost", "root", "", "bd_tamanaco");
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        // Obtener el nombre del perfil
        $query = "SELECT nombre_perfil FROM perfiles WHERE id_perfil = ? AND id_status = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_perfil);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre_perfil = $row['nombre_perfil'];
            $_SESSION['perfil'] = $nombre_perfil; // Guardar perfil en sesión
        } else {
            $error_message = "El perfil seleccionado no es válido o está en mantenimiento.";
        }

        // Cerrar conexión
        $stmt->close();
        $conn->close();
    }
} else {
    // Si es la primera visita o si ya se seleccionó un perfil
    $id_perfil = isset($_SESSION['id_perfil']) ? $_SESSION['id_perfil'] : null;
    $nombre_perfil = isset($_SESSION['perfil']) ? $_SESSION['perfil'] : "Seleccione un perfil para continuar";
    unset($_SESSION['error_message']); // Limpiar mensaje de error
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamanaco - <?php echo $pagina ?? 'mantenimiento'; ?></title>
    <link rel="icon" type="image/png" href="img/logo2.png">
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
        }

        /* Animación para mensajes de error */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            animation: fadeIn 0.5s ease-in-out;
        }

        .bg-decorative {
            background-image: url('../public/img/1.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="flex items-center justify-center min-h-screen bg-gradient-to-b from-blue-800 to-blue-900">
        <div class="bg-white shadow-2xl rounded-xl overflow-hidden flex flex-col md:flex-row max-w-4xl w-full relative">
            <!-- Ícono de regresar -->
            <a href="seleccionar_perfil.php" 
               class="absolute top-4 left-4 bg-blue-700 hover:bg-blue-800 text-white p-3 rounded-full shadow-lg transition duration-300 flex items-center justify-center w-10 h-10 z-10">
                <i class="fas fa-arrow-left"></i>
            </a>

            <!-- Imagen decorativa con filtro oscuro -->
            <div class="w-full md:w-1/2 relative hidden md:block">
                <div class="absolute inset-0 bg-black bg-opacity-40"></div>
                <div class="bg-cover bg-center h-full" style="background-image: url('../public/img/1.jpg');"></div>
            </div>
            
            <!-- Formulario de inicio de sesión -->
            <div class="w-full md:w-1/2 p-10 bg-gray-50">
                <!-- Título -->
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-extrabold text-black"><?= htmlspecialchars($nombre_perfil); ?></h1>
                    <p class="text-gray-400 text-sm mt-2">Perfil seleccionado</p>
                </div>
                
                <!-- Icono destacado -->
                <div class="flex justify-center items-center mb-8">
                    <div class="rounded-full bg-blue-200 p-4 shadow-lg">
                        <i class="fas fa-cogs text-4xl text-blue-500"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-center text-black">Inicio de Sesión</h1>
                <p class="text-gray-400 text-center mb-6">Bienvenido al sistema de mantenimiento preventivo y correctivo.</p>

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
                                <p class="text-gray-700"><?= htmlspecialchars($error_message); ?></p>
                            </div>
                            <button onclick="this.parentElement.parentElement.style.display='none'" 
                                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 focus:outline-none">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="validar_usuario.php" method="POST" class="mt-6">
                    <!-- Campo oculto para el perfil -->
                    <input type="hidden" name="id_perfil" value="<?= htmlspecialchars($id_perfil); ?>">

                    <!-- Usuario -->
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-medium mb-1">Usuario</label>
                        <div class="flex items-center bg-white border border-gray-300 rounded-lg shadow-md p-3">
                            <i class="fas fa-user text-gray-800 mr-3"></i>
                            <input type="text" id="username" name="username" class="w-full text-gray-800 focus:outline-none bg-white placeholder-gray-500" placeholder="Ingresa tu usuario" required>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-1">Contraseña</label>
                        <div class="flex items-center bg-white border border-gray-300 rounded-lg shadow-md p-3">
                            <i class="fas fa-lock text-gray-800 mr-3"></i>
                            <input type="password" id="password" name="password" class="w-full text-gray-800 focus:outline-none bg-white placeholder-gray-500" placeholder="Ingresa tu contraseña" required>
                        </div>
                    </div>

                    <!-- Botón -->
                    <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 rounded-lg shadow-lg transition duration-300">
                        Iniciar Sesión
                    </button>
                </form>

                <!-- Enlaces de opciones adicionales -->
                <div class="mt-6 text-center">
                    <a href="recuperar_usuario.php" class="hidden text-sm text-blue-600 hover:text-blue-800">¿Olvidaste tu usuario?</a>
                    <span class="text-gray-400 mx-2">|</span>
                    <a href="recuperar_contraseña.php" class="text-sm text-blue-600 hover:text-blue-800">¿Olvidaste tu contraseña?</a>
                </div>
            </div>
        </div>
    </div>
</body>
</body>
</html>