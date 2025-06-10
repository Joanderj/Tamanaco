<?php
session_start();
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['id_perfil'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Encabezado HTML -->
<head>
    <meta charset="UTF-8">
    <title>Tamanaco - <?php echo $pagina ?? 'mantenimiento'; ?></title>
    <link rel="icon" type="image/png" href="img/logo2.png">
    <!-- Asegúrate de tener el ícono en la ruta especificada -->
</head>


    <!-- Tailwind y FontAwesome -->
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">

    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <style>
        body { overflow: hidden; }
    </style>
</head>
<body class="relative min-h-screen flex items-center justify-center text-gray-800 dark:text-gray-100">

    <!-- Fondo animado y decorativo -->
    <div class="absolute inset-0 -z-10 overflow-hidden">
        <!-- Degradado radial difuso -->
        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-[120vw] h-[120vh] bg-gradient-radial from-blue-200 via-purple-100 to-pink-200 opacity-30 dark:from-blue-900 dark:via-purple-800 dark:to-pink-900 blur-3xl animate-pulse-slow"></div>

        <!-- Sutiles ondas animadas al fondo -->
        <svg class="absolute bottom-0 w-full h-48 opacity-30 dark:opacity-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#60a5fa" fill-opacity="0.4" d="M0,64L40,90.7C80,117,160,171,240,176C320,181,400,139,480,117.3C560,96,640,96,720,122.7C800,149,880,203,960,192C1040,181,1120,107,1200,69.3C1280,32,1360,32,1400,32L1440,32L1440,320L1400,320C1360,320,1280,320,1200,320C1120,320,1040,320,960,320C880,320,800,320,720,320C640,320,560,320,480,320C400,320,320,320,240,320C160,320,80,320,40,320L0,320Z"></path>
        </svg>
    </div>

    <!-- Contenido principal (se mantiene igual) -->
    <div class="w-full max-w-xl bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-10 relative transition duration-500 z-10">
     
        <!-- Botón de regresar -->
        <a href="../index.php" class="absolute top-5 left-5 flex items-center gap-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-full text-sm transition">
            <i class="fas fa-arrow-left"></i><span>Regresar</span>
        </a>

        <!-- Encabezado -->
        <div class="text-center mb-10">
            <div class="flex justify-center mb-4">
                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-full">
                    <i class="fas fa-user-cog text-5xl text-blue-500 dark:text-blue-300"></i>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold">Seleccionar Perfil</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Inicia sesión con uno de los perfiles disponibles</p>
            <?php if ($error_message): ?>
                <p class="text-red-500 mt-4 font-semibold text-sm animate-pulse"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        </div>

        <!-- Formulario -->
        <form id="perfilForm" action="iniciar_sesion.php" method="POST" class="space-y-6" onsubmit="return handleSubmit()">
            <div>
                <label for="id_perfil" class="block text-sm font-medium mb-2">Perfil:</label>
                <select id="id_perfil" name="perfil" placeholder="Selecciona un perfil..." required class="tom-select w-full">
                    <?php
                    $conn = new mysqli("localhost", "root", "", "bd_tamanaco");
                    if ($conn->connect_error) {
                        die("Error de conexión: " . $conn->connect_error);
                    }

                    $result = $conn->query("SELECT id_perfil, nombre_perfil FROM perfiles WHERE id_status = 1");
                    if ($result->num_rows > 0) {
                        while ($perfil = $result->fetch_assoc()) {
                            $id = $perfil['id_perfil'];
                            $nombre = htmlspecialchars($perfil['nombre_perfil']);
                            echo "<option value='$id'>$nombre</option>";
                        }
                    } else {
                        echo "<option disabled>No hay perfiles activos</option>";
                    }
                    $conn->close();
                    ?>
                </select>
            </div>

            <!-- Botón con spinner -->
            <div class="text-center">
                <button type="submit" id="submitBtn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg flex justify-center items-center gap-2 transition">
                    <span id="btnText"><i class="fas fa-arrow-right"></i> Continuar</span>
                    <span id="spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                </button>
            </div>
        </form>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect("#id_perfil");

        function handleSubmit() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            btn.disabled = true;
            text.classList.add('hidden');
            spinner.classList.remove('hidden');
            return true;
        }
    </script>
</body>
</html>
