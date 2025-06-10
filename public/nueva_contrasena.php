<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link href="../public/css/tailwind.min.css" rel="stylesheet">
    <link href="../public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 min-h-screen flex items-center justify-center">

<div class="bg-white shadow-2xl rounded-2xl w-full max-w-xl p-8 space-y-6 animate-fade-in">

    <!-- PASOS -->
    <div class="flex justify-between items-center text-sm font-semibold text-gray-500 uppercase tracking-wide border-b pb-3">
        <div class="flex-1 text-center">
            <div class="text-gray-400">Paso 1</div>
            <div>Verificación</div>
        </div>
        <div class="flex-1 text-center">
            <div class="text-gray-400">Paso 2</div>
            <div>Validación</div>
        </div>
        <div class="flex-1 text-center relative">
            <div class="text-indigo-600">Paso 3</div>
            <div class="font-bold text-indigo-700">Acceso</div>
            <div class="absolute top-5 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-indigo-500 rounded-full"></div>
        </div>
    </div>

    <!-- TÍTULO -->
    <div class="text-center">
        <h2 class="text-2xl font-extrabold text-gray-800">
            <i class="fas fa-lock mr-2 text-indigo-600"></i> Cambiar Contraseña
        </h2>
        <p class="text-sm text-gray-500 mt-1">Establece una nueva contraseña segura para tu cuenta</p>
    </div>

    <!-- FORMULARIO -->
    <form method="POST" action="guardar_contrasena.php" id="formNuevaContrasena" class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
            <div class="relative">
                <input type="password" id="password" name="password" required oninput="verificarSeguridad(); validarCoincidencia();" 
                    class="w-full rounded-lg border-gray-300 shadow-sm pr-10 focus:ring-indigo-500 focus:border-indigo-500">
                <i class="fas fa-eye absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-400 cursor-pointer"
                   onclick="togglePassword('password', this)"></i>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
            <div class="relative">
                <input type="password" id="confirm_password" name="confirm_password" required oninput="validarCoincidencia();" 
                    class="w-full rounded-lg border-gray-300 shadow-sm pr-10 focus:ring-indigo-500 focus:border-indigo-500">
                <i class="fas fa-eye absolute top-1/2 right-3 transform -translate-y-1/2 text-gray-400 cursor-pointer"
                   onclick="togglePassword('confirm_password', this)"></i>
            </div>
        </div>

        <div>
            <p id="mensaje-error-contraseña" class="text-sm text-red-600 hidden mt-1">⚠ Las contraseñas no coinciden.</p>
            <p id="mensaje-exito-contraseña" class="text-sm text-green-600 hidden mt-1">✔ Las contraseñas coinciden.</p>
        </div>

        <!-- SEGURIDAD -->
        <div class="my-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nivel de Seguridad:</label>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div id="barraSeguridad" class="h-2 w-1/5 bg-red-500 transition-all duration-300 ease-in-out"></div>
            </div>
            <p id="nivelSeguridadTexto" class="text-sm text-center mt-2 text-gray-700 font-medium">Bajo</p>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition-all duration-300">
            <i class="fas fa-save mr-2"></i> Guardar Contraseña
        </button>
    </form>
</div>

<!-- ANIMACIÓN -->
<style>
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    function validarCoincidencia() {
        const pass = document.getElementById("password").value;
        const confirm = document.getElementById("confirm_password").value;
        const error = document.getElementById("mensaje-error-contraseña");
        const exito = document.getElementById("mensaje-exito-contraseña");

        if (pass === confirm && pass.length > 0) {
            exito.classList.remove("hidden");
            error.classList.add("hidden");
        } else {
            error.classList.remove("hidden");
            exito.classList.add("hidden");
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

        barra.style.width = ancho;
        barra.className = `h-2 ${color} transition-all duration-300 ease-in-out`;
        texto.textContent = nivel;
    }

    function togglePassword(id, icon) {
        const input = document.getElementById(id);
        const isVisible = input.type === "text";
        input.type = isVisible ? "password" : "text";
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    }
</script>

</body>
</html>
