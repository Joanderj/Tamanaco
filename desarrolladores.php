<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Desarrolladores</title>
    <link rel="icon" type="image/png" href="https://i.pinimg.com/736x/22/9a/05/229a05f9751700bd4445a6f90477dc03.jpg">
  <link href="public/css/tailwind.min.css" rel="stylesheet">
  <link href="public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Sección de Bienvenida Animada -->
  <section id="bienvenidaDesarrolladores" class="w-full min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-indigo-200 via-white to-blue-100 relative overflow-hidden">
    <!-- Fondo decorativo animado -->
    <div class="absolute top-0 left-0 w-96 h-96 bg-indigo-400 opacity-20 rounded-full blur-3xl animate-pulse -z-10"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-300 opacity-20 rounded-full blur-3xl animate-pulse -z-10"></div>
    <div class="absolute inset-0 bg-grid-indigo-400/[0.04] pointer-events-none z-0"></div>

    <!-- Contenido central -->
    <div class="relative z-10 flex flex-col items-center text-center px-6 py-20 max-w-2xl">
      <h1 id="bienvenidaTitulo" class="text-6xl md:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-blue-500 to-teal-400 drop-shadow-lg mb-6 opacity-0">
        ¡Bienvenidos!
      </h1>
      <p id="bienvenidaDescripcion" class="text-xl md:text-2xl text-gray-700 font-medium leading-relaxed mb-8 opacity-0">
        Descubre el universo creativo de nuestro equipo de desarrolladores.<br>
        Aquí la innovación, la pasión y la tecnología se unen para construir el futuro.<br>
        Explora, inspírate y conoce a quienes hacen posible cada línea de código en Tamanaco.
      </p>
      <div id="bienvenidaIconos" class="flex justify-center gap-8 text-5xl text-indigo-400 opacity-0">
        <i class="fas fa-laptop-code"></i>
        <i class="fas fa-lightbulb"></i>
        <i class="fas fa-users"></i>
        <i class="fas fa-rocket"></i>
      </div>
    </div>
  </section>

  <!-- Animación de Bienvenida con Anime.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <script>
    anime.timeline({ easing: 'easeOutExpo', duration: 1200 })
      .add({
        targets: '#bienvenidaTitulo',
        opacity: [0, 1],
        scale: [0.7, 1],
        translateY: [-80, 0],
        delay: 200
      })
      .add({
        targets: '#bienvenidaDescripcion',
        opacity: [0, 1],
        translateY: [60, 0],
        duration: 1000,
        offset: '-=800'
      })
      .add({
        targets: '#bienvenidaIconos i',
        opacity: [0, 1],
        scale: [0, 1.2],
        rotate: ['-360deg', '0deg'],
        delay: anime.stagger(200),
        duration: 900,
        offset: '-=700'
      });

    // Efecto de resplandor suave en el título
    anime({
      targets: '#bienvenidaTitulo',
      textShadow: [
        '0 0 40px #818cf8, 0 0 80px #38bdf8',
        '0 0 10px #818cf8, 0 0 20px #38bdf8'
      ],
      direction: 'alternate',
      loop: true,
      duration: 2000,
      easing: 'easeInOutSine'
    });
  </script>
<!-- Sección de título -->
<section class="relative bg-gradient-to-br from-indigo-100 via-white to-blue-100 py-20 px-6 overflow-hidden">
  <!-- Burbuja decorativa animada -->
  <div class="absolute top-0 left-0 w-96 h-96 bg-indigo-300 opacity-20 rounded-full blur-3xl animate-pulse -z-10"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-300 opacity-20 rounded-full blur-3xl animate-pulse -z-10"></div>

  <!-- Contenedor centrado -->
  <div class="max-w-4xl mx-auto text-center" id="tituloAnimado">
    <h2 class="text-5xl md:text-6xl font-extrabold text-gray-800 tracking-tight mb-4">
      <i class="fas fa-code text-indigo-500 mr-3"></i>Analistas de Software
    </h2>
    <p class="text-lg text-gray-600 font-medium">
      Conoce a las mentes creativas que construyen el futuro de Tamanaco.
    </p>
  </div>
</section>

<!-- Anime.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
  anime({
    targets: '#tituloAnimado h2',
    translateY: [-100, 0],
    opacity: [0, 1],
    duration: 1200,
    easing: 'easeOutExpo'
  });

  anime({
    targets: '#tituloAnimado p',
    translateY: [50, 0],
    opacity: [0, 1],
    delay: 300,
    duration: 1000,
    easing: 'easeOutExpo'
  });
</script>

  <section class="w-full min-h-screen flex flex-col md:flex-row">

    <!-- Apartado 1: Imagen -->
    <div class="md:w-1/3 w-full h-64 md:h-auto">
      <img src="public/img/luciano.jpg" alt="Equipo Tamanaco" class="w-full h-full object-cover">
    </div>

    <!-- Apartado 2: Porcentaje -->
    <!-- Segunda Columna: Porcentaje en dos bloques 50/50 -->
<div class="md:w-1/3 w-full flex flex-col min-h-screen">

  <!-- Parte superior (gris) -->
  <div class="bg-gray-400 flex flex-col justify-center items-center text-center flex-1 p-6">
    <p class="text-sm text-gray-600 uppercase tracking-wide mb-2">Satisfacción general</p>
    <h2 class="text-7xl font-extrabold text-white mb-2  px-8 py-4 " id="porcentaje">92%</h2>
  </div>

  <!-- Parte inferior (azul claro) -->
  <div class="bg-blue-100 flex justify-center items-center text-center flex-1 p-6">
    <p class="text-gray-800 text-lg font-medium leading-relaxed">
      Hola, Soy Luciano
    </p>
  </div>

</div>


    <!-- Apartado 3: Título y descripción -->
    <div class="md:w-1/3 w-full bg-white flex flex-col justify-center p-8">
      <h3 class="text-3xl font-bold text-black mb-4">Software que impulsa el futuro</h3>
      <p class="text-black text-base leading-relaxed">
        En Tamanaco no solo fabricamos productos, también desarrollamos tecnología. Nuestro sistema digital ayuda a mantener todo organizado, eficiente y adaptado al crecimiento de la empresa. Es una herramienta pensada para los retos del deporte venezolano y del mañana.
      </p>
    </div>

  </section>

  <!-- Anime.js para animar el porcentaje -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
  <script>
    anime({
      targets: '#porcentaje',
      innerHTML: [0, 10],
      round: 1,
      easing: 'easeOutExpo',
      duration: 2000
    });

     anime({
      targets: '#porcentajes',
      innerHTML: [0, 3],
      round: 1,
      easing: 'easeOutExpo',
      duration: 2000
    });
  </script>
<div class="flex flex-col md:flex-row w-full min-h-screen">
<div class="md:w-1/3 w-full h-64 md:h-auto">
      <img src="public/img/daniel.jpg" alt="Equipo Tamanaco" class="w-full h-full object-cover">
    </div>
  <!-- Carta 2: Porcentaje dividido 50/50 -->
  <div class="md:w-1/3 w-full flex flex-col min-h-screen">

    <!-- Parte superior (gris) -->
    <div class="bg-gray-400 flex flex-col justify-center items-center text-center flex-1 p-6">
      <p class="text-sm text-gray-600 uppercase tracking-wide mb-2">Satisfacción general</p>
      <h2 class="text-7xl font-extrabold text-white mb-2  px-8 py-4 " id="porcentajes">3%</h2>
    </div>

    <!-- Parte inferior (azul claro) -->
    <div class="bg-blue-200 flex justify-center items-center text-center flex-1 p-6">
      <p class="text-gray-800 text-lg font-medium leading-relaxed">
        Hola, Soy Daniel
      </p>
    </div>
  </div>

  <!-- Carta 3: Texto largo -->
  <div class="md:w-1/3 w-full bg-gray-100 flex flex-col justify-center items-start p-10 text-left">
    <h3 class="text-4xl font-extrabold text-gray-900 mb-4">Desarrollo con propósito</h3>
    <p class="text-gray-700 text-base leading-loose">
      Nuestro sistema fue creado pensando en la organización, eficiencia y crecimiento. Detrás de cada módulo hay horas de diseño, pruebas y mejora constante. No solo desarrollamos software, construimos soluciones reales para la industria venezolana.
    </p>
  </div>

</div>
<!-- Sección de Backend Developers -->
<section class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 py-20 px-6 overflow-hidden text-white">
  <!-- Decoración de fondo animada -->
  <div class="absolute -top-20 -left-20 w-96 h-96 bg-indigo-700 opacity-10 rounded-full blur-3xl animate-pulse -z-10"></div>
  <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-blue-600 opacity-10 rounded-full blur-3xl animate-pulse -z-10"></div>

  <!-- Contenido -->
  <div class="max-w-4xl mx-auto text-center" id="backendAnimado">
    <h2 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-4 text-indigo-400">
      <i class="fas fa-server mr-3"></i>Backend Developers
    </h2>
    <p class="text-lg text-blue-200 font-medium">
      Los arquitectos invisibles que hacen que todo funcione perfecto detrás del sistema.
    </p>
  </div>
</section>

<!-- Anime.js -->
<script>
  anime({
    targets: '#backendAnimado h2',
    translateX: [-100, 0],
    opacity: [0, 1],
    duration: 1200,
    easing: 'easeOutExpo'
  });

  anime({
    targets: '#backendAnimado p',
    translateX: [100, 0],
    opacity: [0, 1],
    delay: 300,
    duration: 1000,
    easing: 'easeOutExpo'
  });
</script>
<!-- SECCIÓN BRIAN CON ANIMACIONES POR VISTA -->
<section id="seccion-brian" class="bg-gray-900 text-white py-16 px-6">
  <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">

    <!-- Columna 1: Contenido -->
    <div class="space-y-10">

      <!-- Título -->
      <h2 id="titulo-brian" class="text-4xl font-bold text-indigo-400 opacity-0">
        Hola, Soy Brian
      </h2>

      <!-- Íconos en círculos -->
      <div class="flex justify-between items-center gap-4" id="iconos-brian">
        <div class="flex flex-col items-center circulo opacity-0">
          <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-database"></i>
          </div>
          <h4 class="mt-3 font-semibold">Base de Datos</h4>
          <p class="text-sm text-gray-400 text-center">Gestión eficiente y segura de los datos.</p>
        </div>

        <div class="flex flex-col items-center circulo opacity-0">
          <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-cogs"></i>
          </div>
          <h4 class="mt-3 font-semibold">Lógica</h4>
          <p class="text-sm text-gray-400 text-center">Procesos internos optimizados.</p>
        </div>

        <div class="flex flex-col items-center circulo opacity-0">
          <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4 class="mt-3 font-semibold">Seguridad</h4>
          <p class="text-sm text-gray-400 text-center">Protección integral del sistema.</p>
        </div>
      </div>

      <!-- Descripción -->
      <p class="text-blue-200 leading-relaxed mt-6 opacity-0" id="descripcion-brian">
        Nuestro equipo backend en Tamanaco se encarga de que todo funcione de forma fluida detrás del escenario. Desde la arquitectura del sistema hasta la seguridad de la información, ellos crean soluciones robustas y eficientes que permiten el funcionamiento continuo de todas nuestras plataformas.
      </p>

      <!-- Pudín Épico -->
      <div id="pudin-epico" class="opacity-0 mt-10 mx-auto w-60 h-60 rounded-full overflow-hidden shadow-2xl ring-4 ring-yellow-400 ring-offset-4 ring-offset-gray-800 transform bg-yellow-100 relative">
        <img src="https://i.blogs.es/0f35c7/puding-roscon/450_1000.jpg"
             alt="Pudín Épico"
             class="w-full h-full object-cover rounded-full saturate-150 brightness-105 shadow-inner">
        <div class="absolute inset-0 rounded-full bg-yellow-200 opacity-30 blur-2xl animate-pulse"></div>
      </div>
    </div>

    <!-- Columna 2: Imagen -->
    <div class="w-full">
      <img id="imagen-brian" src="public/img/brian.jpg" alt="Equipo Backend Tamanaco" class="rounded-2xl shadow-2xl w-full object-cover opacity-0">
    </div>
  </div>
</section>

<!-- ANIME.JS + INTERSECTION OBSERVER -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Título animación
          anime({
            targets: '#titulo-brian',
            translateY: [-100, 0],
            scale: [0.8, 1],
            opacity: [0, 1],
            duration: 1200,
            easing: 'easeOutElastic(1, .8)'
          });

          // Círculos animación
          anime({
            targets: '.circulo',
            translateY: [80, 0],
            rotate: [20, 0],
            opacity: [0, 1],
            delay: anime.stagger(200, { start: 500 }),
            duration: 1400,
            easing: 'easeOutElastic(1, .6)'
          });

          // Descripción
          anime({
            targets: '#descripcion-brian',
            opacity: [0, 1],
            translateX: [-30, 0],
            delay: 1200,
            duration: 1000,
            easing: 'easeOutQuad'
          });

          // Imagen
          anime({
            targets: '#imagen-brian',
            scale: [0.9, 1],
            opacity: [0, 1],
            duration: 1500,
            delay: 800,
            easing: 'easeOutElastic(1, .8)'
          });

          // Pudín épico
          anime({
            targets: '#pudin-epico',
            scale: [0, 1.2],
            rotate: ['-720deg', '0deg'],
            opacity: [0, 1],
            duration: 2000,
            delay: 1800,
            easing: 'easeOutElastic(1, .6)',
            complete: () => {
              anime({
                targets: '#pudin-epico',
                scale: [1.2, 1],
                duration: 800,
                easing: 'easeOutElastic(1, .8)'
              });
            }
          });

          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.4 // se activa cuando 40% del bloque está visible
    });

    const seccionBrian = document.querySelector('#seccion-brian');
    observer.observe(seccionBrian);
  });
</script>


<section class="bg-gray-800 text-gray-100 py-16 px-6">
  <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center">
    
    <!-- Columna 1: Contenido -->
    <div class="space-y-10">
      <h2 class="text-4xl font-bold text-green-400">
        Hola, Soy Rafa
      </h2>

      <!-- 3 Círculos con íconos -->
      <div class="flex justify-between items-center gap-4">
        <!-- Círculo 1 -->
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-database"></i>
          </div>
          <h4 class="mt-3 font-semibold text-gray-200">Base de Datos</h4>
          <p class="text-sm text-gray-400 text-center">Gestión eficiente y segura de los datos.</p>
        </div>

        <!-- Círculo 2 -->
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-cogs"></i>
          </div>
          <h4 class="mt-3 font-semibold text-gray-200">Lógica</h4>
          <p class="text-sm text-gray-400 text-center">Procesos internos optimizados.</p>
        </div>

        <!-- Círculo 3 -->
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-2xl shadow-lg">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4 class="mt-3 font-semibold text-gray-200">Seguridad</h4>
          <p class="text-sm text-gray-400 text-center">Protección integral del sistema.</p>
        </div>
      </div>

      <!-- Descripción larga -->
      <p class="text-green-300 leading-relaxed mt-6">
        Nuestro equipo backend en Tamanaco se encarga de que todo funcione de forma fluida detrás del escenario. Desde la arquitectura del sistema hasta la seguridad de la información, ellos crean soluciones robustas y eficientes que permiten el funcionamiento continuo de todas nuestras plataformas.
      </p>
    </div>

    <!-- Columna 2: Imagen -->
    <div class="w-full">
      <img src="public/img/rafa.jpg" alt="Equipo Backend Tamanaco" class="rounded-2xl shadow-2xl w-full object-cover">
    </div>
  </div>
</section>
<!-- Sección de Font Developers -->
<section class="relative bg-gradient-to-br from-green-900 via-green-800 to-green-900 py-20 px-6 overflow-hidden text-white">
  <!-- Decoración de fondo animada -->
  <div class="absolute -top-20 -left-20 w-96 h-96 bg-green-700 opacity-10 rounded-full blur-3xl animate-pulse -z-10"></div>
  <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-teal-600 opacity-10 rounded-full blur-3xl animate-pulse -z-10"></div>

  <!-- Contenido -->
  <div class="max-w-4xl mx-auto text-center" id="fontDevAnimado">
    <h2 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-4 text-green-400">
      <i class="fas fa-font mr-3"></i>Font Developer
    </h2>
    <p class="text-lg text-teal-200 font-medium">
      Los creadores del estilo y la identidad visual que hacen única cada línea de código.
    </p>
  </div>
</section>

<!-- Anime.js -->
<script>
  anime({
    targets: '#fontDevAnimado h2',
    translateX: [-100, 0],
    opacity: [0, 1],
    duration: 1200,
    easing: 'easeOutExpo'
  });

  anime({
    targets: '#fontDevAnimado p',
    translateX: [100, 0],
    opacity: [0, 1],
    delay: 300,
    duration: 1000,
    easing: 'easeOutExpo'
  });
</script>
<!-- Sección Frontend Developer épica -->
<section class="min-h-screen bg-gray-900 text-white flex items-center justify-center px-4 py-24">
  <div class="text-center max-w-md" id="cardAnimada">
    <!-- Imagen circular -->
    <div class="relative mb-6">
      <img src="public/img/sebas.jpeg" alt="Frontend Dev"
        class="w-52 h-52 rounded-full border-[10px] border-white shadow-[0_0_60px_rgba(99,102,241,0.7)] mx-auto z-10 relative">
    </div>

    <!-- Iconos -->
    <div class="flex justify-center space-x-6 text-3xl text-indigo-400 mb-4">
      <i class="fas fa-paint-brush"></i>
      <i class="fas fa-laptop-code"></i>
      <i class="fas fa-bolt"></i>
    </div>

    <!-- Título -->
    <h2 class="text-4xl font-bold text-indigo-300 mb-2 drop-shadow-md">
      Frontend Developer
    </h2>

    <!-- Descripción -->
    <p class="text-blue-200 text-md leading-relaxed">
      El arte de transformar ideas en interfaces. Creamos experiencias visuales rápidas, modernas y cautivadoras.
    </p>
  </div>
</section>

<!-- Anime.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<!-- Animación ÉPICA -->
<script>
  anime.timeline({
    easing: 'easeOutExpo',
    duration: 1200
  })
  .add({
    targets: '#cardAnimada img',
    scale: [0, 1],
    rotate: ['-360deg', '0deg'],
    opacity: [0, 1],
    delay: 300
  })
  .add({
    targets: '#cardAnimada i',
    translateY: [50, 0],
    opacity: [0, 1],
    delay: anime.stagger(150),
    offset: '-=1000'
  })
  .add({
    targets: '#cardAnimada h2',
    translateX: [-100, 0],
    opacity: [0, 1],
    offset: '-=800'
  })
  .add({
    targets: '#cardAnimada p',
    translateY: [40, 0],
    opacity: [0, 1],
    offset: '-=1000'
  });
</script>
<!-- Sección del Líder del Proyecto -->
<section id="seccionLider" class="relative bg-gradient-to-tr from-black via-gray-900 to-gray-800 min-h-screen flex items-center justify-center px-6 py-24 text-white overflow-hidden opacity-0 translate-y-20 transition-all duration-700">
  <!-- Decoración Épica de Fondo -->
  <div class="absolute top-0 left-0 w-96 h-96 bg-purple-700 opacity-10 rounded-full blur-3xl animate-ping -z-10"></div>
  <div class="absolute bottom-0 right-0 w-[32rem] h-[32rem] bg-indigo-500 opacity-20 rounded-full blur-[200px] -z-10"></div>
  <div class="absolute inset-0 bg-grid-white/[0.03] pointer-events-none z-0"></div>

  <!-- Contenido principal -->
  <div class="max-w-3xl text-center z-10" id="liderAnimado">
    <h2 class="text-5xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-indigo-500 to-teal-300 drop-shadow-lg mb-6">
      ⚡ Project Leader & System Architect
    </h2>
    <p class="text-lg text-gray-300 font-medium leading-relaxed">
      Especialista en análisis, automatización y desarrollo integral.<br>
      Conecta la lógica con la experiencia visual para llevar el proyecto al siguiente nivel.
    </p>
    <div class="flex justify-center gap-10 mt-10 text-4xl text-indigo-400">
      <i class="fas fa-cogs"></i>
      <i class="fas fa-network-wired"></i>
      <i class="fas fa-brain"></i>
    </div>
  </div>
</section>

<!-- Anime.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
  const seccion = document.getElementById('seccionLider');

  const observer2 = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Quita opacidad para mostrar suavemente la sección
        seccion.classList.remove('opacity-0', 'translate-y-20');

        // Ejecutar la animación principal
        anime.timeline({
          easing: 'easeOutExpo',
          duration: 1000
        })
        .add({
          targets: '#liderAnimado h2',
          translateY: [-100, 0],
          scale: [0.8, 1],
          opacity: [0, 1]
        })
        .add({
          targets: '#liderAnimado p',
          translateY: [50, 0],
          opacity: [0, 1],
          delay: 300
        })
        .add({
          targets: '#liderAnimado i',
          scale: [0, 1],
          rotate: ['-360deg', '0deg'],
          opacity: [0, 1],
          delay: anime.stagger(200),
          duration: 800
        });

        // Desactivar observer para que solo pase una vez
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });

  observer2.observe(seccion);
</script>

  <style>

    .observado {
      opacity: 0;
      transform: translateY(100px);
    }

    #presentacion h1, h2, p {
      transform-origin: center;
    }

    #iconos i {
      transition: transform 0.3s ease, color 0.3s ease;
    }

    #iconos i:hover {
      transform: scale(1.3) rotate(5deg);
      color: #facc15;
    }
  </style>

  <section id="seccionJoander" class="flex h-screen w-screen overflow-hidden observado">
    
    <!-- Imagen izquierda -->
    <div id="imgIzq" class="w-1/3 h-full observado">
      <img src="public/img/joander5.png" alt="Imagen Izquierda" class="w-full h-full object-cover" />
    </div>

    <!-- Centro: Presentación -->
    <div class="w-1/3 h-full flex items-center justify-center bg-gray-900 text-white text-center px-8 observado">
      <div id="presentacion">
        <h1 class="text-3xl font-extrabold mb-6">Hola, soy Joander</h1>
        <h2 class="text-5xl font-bold text-yellow-400 mb-6">Project Leader & System Architect</h2>
        <p class="text-xl leading-relaxed tracking-wide mb-8">
          Especialista en análisis profundo, automatización de procesos y desarrollo integral a medida.<br>
          Conecto la lógica con la experiencia visual para convertir ideas en sistemas funcionales y escalables.<br>
          Mi enfoque une precisión técnica con diseño centrado en el usuario para llevar tu proyecto al siguiente nivel.
        </p>
        <div id="iconos" class="flex justify-center space-x-6 text-3xl text-yellow-400 opacity-0">
          <i class="fas fa-code"></i>
          <i class="fas fa-cogs"></i>
          <i class="fas fa-brain"></i>
        </div>
      </div>
    </div>

    <!-- Imagen derecha -->
    <div id="imgDer" class="w-1/3 h-full observado">
      <img src="public/img/espacio.jpeg" alt="Imagen Derecha" class="w-full h-full object-cover" />
    </div>

  </section>

  <script>
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // Animar imágenes
          anime({
            targets: ['#imgIzq', '#imgDer'],
            opacity: [0, 1],
            scale: [0.9, 1],
            translateY: [100, 0],
            duration: 1200,
            easing: 'easeOutExpo',
            delay: anime.stagger(100)
          });

          // Animar presentación
          anime.timeline({
            easing: 'easeOutExpo',
            duration: 1000
          })
          .add({
            targets: '#presentacion h1',
            translateY: [-100, 0],
            opacity: [0, 1],
            rotateZ: [-10, 0],
            duration: 1000
          })
          .add({
            targets: '#presentacion h2',
            translateY: [100, 0],
            opacity: [0, 1],
            rotateZ: [10, 0],
            duration: 1000,
            offset: '-=800'
          })
          .add({
            targets: '#presentacion p',
            translateY: [50, 0],
            opacity: [0, 1],
            scale: [0.9, 1],
            duration: 1200,
            offset: '-=800'
          })
          .add({
            targets: '#iconos',
            opacity: [0, 1],
            duration: 600,
            offset: '-=600'
          })
          .add({
            targets: '#iconos i',
            scale: [0, 1.3],
            rotate: ['-30deg', '0deg'],
            delay: anime.stagger(200),
            easing: 'easeOutBack',
            duration: 1000
          });

          // Efecto pulsante
          anime({
            targets: '#iconos i',
            scale: [
              { value: 1.2, duration: 800 },
              { value: 1, duration: 800 }
            ],
            easing: 'easeInOutSine',
            loop: true,
            delay: anime.stagger(300, { start: 3500 })
          });

          // Marcar como animado para no volver a repetir
          entry.target.classList.remove('observado');
          obs.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.5
    });

    document.querySelectorAll('.observado').forEach(el => observer.observe(el));
  </script>
<!-- Sección de Invitados del Proyecto -->
<section id="seccionInvitados" class="relative min-h-screen bg-yellow-100 flex items-center justify-center px-6 py-24 text-black overflow-hidden opacity-0 translate-y-20 transition-all duration-700">

  <!-- Fondo loco y colorido -->
  <div class="absolute top-10 left-10 w-32 h-32 bg-pink-400 rounded-full blur-2xl opacity-30 animate-bounce -z-10"></div>
  <div class="absolute bottom-20 right-10 w-40 h-40 bg-green-300 rounded-full blur-xl opacity-40 animate-pulse -z-10"></div>
  <div class="absolute inset-0 bg-grid-black/[0.03] pointer-events-none z-0"></div>

  <!-- Contenido gracioso -->
  <div class="max-w-3xl text-center z-10" id="invitadosAnimados">
    <h2 class="text-4xl md:text-5xl font-extrabold text-pink-600 mb-4">
      🎉 Invitados del Proyecto 🎈
    </h2>
    <p class="text-lg text-gray-700 font-semibold leading-relaxed mb-6">
      Aquí llegan los más locos, los más cracks, los más creativos del team. <br>
      🧠💡💻 Listos para romperla con ideas y memes.
    </p>
    <div class="flex justify-center gap-8 text-5xl text-indigo-600">
      <i class="fas fa-user-ninja"></i>
      <i class="fas fa-robot"></i>
      <i class="fas fa-ghost"></i>
      <i class="fas fa-dog"></i>
    </div>
  </div>
</section>

<!-- Anime.js Animación para sección graciosa -->
<script>
  const seccionInv = document.getElementById('seccionInvitados');

  const observer3 = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Mostrar la sección con efecto
        seccionInv.classList.remove('opacity-0', 'translate-y-20');

        // Animación loca y divertida
        anime.timeline({
          easing: 'easeOutElastic(1, .8)',
          duration: 1000
        })
        .add({
          targets: '#invitadosAnimados h2',
          rotate: [-20, 0],
          scale: [0.5, 1],
          opacity: [0, 1]
        })
        .add({
          targets: '#invitadosAnimados p',
          translateX: [-100, 0],
          opacity: [0, 1],
          delay: 300
        })
        .add({
          targets: '#invitadosAnimados i',
          translateY: [-200, 0],
          scale: [0, 1.2],
          rotate: ['720deg', '0deg'],
          opacity: [0, 1],
          delay: anime.stagger(200),
          duration: 1200
        });

        // Solo una vez
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });

  observer3.observe(seccionInv);
</script>
<section class="bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 py-24 px-6 lg:px-32 min-h-screen flex items-center justify-center">
  <div class="max-w-7xl mx-auto flex flex-col-reverse md:flex-row items-center gap-16">

    <!-- Texto Alfonso -->
    <div class="w-full md:w-2/3 space-y-8 opacity-0" id="textoAnimado">
      <h2 class="text-5xl font-extrabold text-gray-800 tracking-tight leading-snug">
        Hola, soy <span class="text-purple-600">Alfonso</span>
      </h2>
      <p class="text-gray-700 text-lg leading-relaxed">
        🐑 El pastor del grupo. Mi función es guiar a las ovejas descarriadas (¡sí, tú también, Rafa!).<br>
        Me especializo en recordar reuniones, enviar memes educativos y asegurarme de que todos prendan la cámara en las clases de matemática. (¡Aunque ni yo copie a veces!)
      </p>

      <div class="text-gray-800 text-md bg-white p-6 rounded-xl shadow-lg border-l-4 border-purple-500">
        <p><strong>🧠 Chiste del día:</strong> ¿Por qué mi ex no usa reloj? Porque el tiempo con ella nunca fue bueno. ⏱️💔</p>
      </div>

      <p class="mt-6 text-gray-700 text-lg leading-relaxed italic font-medium">
        “Donde va el conocimiento, voy yo... después de un cafecito ☕”
      </p>
    </div>

    <!-- Imagen -->
    <div class="w-full md:w-1/3 flex justify-center opacity-0" id="imagenAnimada">
      <img src="public/img/alfonso2.jpg" alt="Alfonso" class="w-80 h-96 object-cover rounded-3xl shadow-2xl border-4 border-white">
    </div>
  </div>
</section>

<!-- Anime.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>

<script>
  const observerAlfonso = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // Animación del texto con rebote y stagger
        anime.timeline({
          easing: 'easeOutElastic(1, .8)',
          duration: 1000
        })
        .add({
          targets: '#textoAnimado',
          opacity: [0, 1],
          translateY: [100, 0],
          scale: [0.8, 1]
        })
        .add({
          targets: '#textoAnimado h2',
          opacity: [0, 1],
          translateX: [-50, 0],
          duration: 800
        }, '-=800')
        .add({
          targets: '#textoAnimado p, #textoAnimado div, #textoAnimado span',
          opacity: [0, 1],
          translateX: [50, 0],
          delay: anime.stagger(150),
          duration: 700
        }, '-=600');

      anime({
  targets: '#imagenAnimada',
  opacity: [
    { value: 0, duration: 0 },
    { value: 1, duration: 400 },
    { value: 0.5, duration: 200 },
    { value: 1, duration: 200 }
  ],
  scale: [
    { value: 4, duration: 500, easing: 'easeOutElastic(1.2, .5)' },
    { value: 0.2, duration: 300, easing: 'easeInBack' },
    { value: 1.5, duration: 300, easing: 'easeOutBounce' },
    { value: 1, duration: 300 }
  ],
  rotate: [
    { value: '20turn', duration: 2500, easing: 'easeInOutCirc' }, // Giro hipersónico
    { value: '-45deg', duration: 150 },
    { value: '45deg', duration: 150 },
    { value: '0deg', duration: 300 }
  ],
  translateY: [
    { value: -350, duration: 600, easing: 'easeOutExpo' },
    { value: 200, duration: 400, easing: 'easeInOutQuad' },
    { value: -100, duration: 300, easing: 'easeOutBounce' },
    { value: 0, duration: 300 }
  ],
  translateX: [
    { value: [0, 80], duration: 100 },
    { value: -80, duration: 100 },
    { value: 60, duration: 100 },
    { value: -60, duration: 100 },
    { value: 40, duration: 100 },
    { value: -40, duration: 100 },
    { value: 20, duration: 100 },
    { value: -20, duration: 100 },
    { value: 0, duration: 100 }
  ],
  backgroundColor: [
    { value: '#ff0000', duration: 150 },
    { value: '#00ff00', duration: 150 },
    { value: '#0000ff', duration: 150 },
    { value: '#ffff00', duration: 150 },
    { value: '#ff00ff', duration: 150 },
    { value: '#00ffff', duration: 150 },
    { value: 'rgba(255, 255, 255, 0)', duration: 150 }
  ],
  borderRadius: [
    { value: ['0%', '50%'], duration: 200 },
    { value: ['50%', '75%'], duration: 200 },
    { value: ['75%', '100%'], duration: 200 },
    { value: ['100%', '0%'], duration: 200 }
  ],
  filter: [
    { value: 'blur(0px)', duration: 0 },
    { value: 'blur(4px)', duration: 300 },
    { value: 'blur(0px)', duration: 300 }
  ],
  boxShadow: [
    { value: '0 0 20px #ff0055', duration: 200 },
    { value: '0 0 40px #00ffaa', duration: 200 },
    { value: '0 0 60px #0011ff', duration: 200 },
    { value: 'none', duration: 200 }
  ],
  duration: 6000,
  delay: anime.stagger(60, { start: 100 }),
  easing: 'easeInOutBack',
  complete: () => {
    console.log(' Animación final ejecutada..');
  }
});




        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });

  observerAlfonso.observe(document.getElementById('textoAnimado'));
  observerAlfonso.observe(document.getElementById('imagenAnimada'));
</script>

<section class="bg-gray-200 py-16 px-6 lg:px-24 min-h-screen flex flex-col md:flex-row">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-8 items-stretch">

    <!-- Lado Izquierdo (Texto amplio) -->
    <div class="md:w-2/3 space-y-8">
      <!-- Título largo -->
      <h2 id="texto1" class="text-4xl md:text-3xl font-bold text-gray-900 leading-tight">
      Hola, soy Gleyce
      </h2>

      <!-- Primer párrafo descriptivo -->
      <p id="texto2" class="text-gray-700 text-lg leading-relaxed">
       Soy de Brasil
      </p>

      <!-- Presentación de Gleyce -->
      <div id="texto3" class="mt-16 space-y-4">
        <p class="text-lg text-gray-800">
          Hola, soy <span class="font-bold">Gleyce</span>, y estoy buscando información sobre cómo seguir aprendiendo. ¡Soy de Brasil 🇧🇷 y tengo muchas ganas de comenzar!
        </p>
        <p class="text-gray-600 italic">
          
        </p>
        <p class="text-xl font-semibold text-indigo-600">
          ¡Conecta, aprende y evoluciona con nosotros! 
        </p>
      </div>
    </div>

    <!-- Lado Derecho (Imagen con animación) -->
    <div class="md:w-1/3 h-full flex items-center justify-center">
      <img id="imagenAnimada2" src="public/img/gleyce.jpeg" alt="Estudiante" class="w-full h-full object-cover rounded-xl shadow-xl">
    </div>
  </div>
</section>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
      anime({
    targets: '#imagenAnimada',
    opacity: [0, 1],
    scale: [
      { value: 3, duration: 300, easing: 'easeOutExpo' },
      { value: 0.2, duration: 200, easing: 'easeInBack' },
      { value: 2.5, duration: 300, easing: 'easeOutElastic(1, .5)' },
      { value: 1, duration: 400 }
    ],
    rotate: [
      { value: '10turn', duration: 2500, easing: 'easeInOutCirc' },
      { value: '0deg', duration: 300, easing: 'easeOutBounce' }
    ],
    translateY: [
      { value: -200, duration: 300 },
      { value: 100, duration: 300 },
      { value: 0, duration: 300 }
    ],
    easing: 'easeInOutSine',
    complete: () => {
      // Cambios de color locos cada segundo
      setInterval(() => {
        document.getElementById('imagenAnimada').style.filter =
          `hue-rotate(${Math.floor(Math.random() * 360)}deg)`;
        document.getElementById('imagenAnimada').style.transform =
          `scale(${Math.random() * 2.5 + 0.2})`;
      }, 1000);
    }
  });
 anime({
  targets: '#imagenAnimada2',
  opacity: [
    { value: 0, duration: 100 },
    { value: 1, duration: 400 }
  ],
  scale: [
    { value: 1.8, duration: 200, easing: 'easeOutElastic(1, .3)' },
    { value: 0.8, duration: 150, easing: 'easeInBack' },
    { value: 1.4, duration: 300, easing: 'easeOutBounce' },
    { value: 1.1, duration: 200 },
    { value: 1, duration: 200 }
  ],
  rotate: [
    { value: '4turn', duration: 600, easing: 'easeInOutBack' },
    { value: '-1turn', duration: 1500, easing: 'easeOutExpo' },
    { value: '0deg', duration: 400, easing: 'easeOutElastic(1, .6)' }
  ],
  translateX: [
    { value: -150, duration: 300, easing: 'easeOutQuad' },
    { value: 150, duration: 300, easing: 'easeInOutBack' },
    { value: 0, duration: 300, easing: 'easeOutElastic(1, .8)' }
  ],
  translateY: [
    { value: -100, duration: 200 },
    { value: 120, duration: 200 },
    { value: -50, duration: 200 },
    { value: 0, duration: 300 }
  ],
  easing: 'easeInOutSine',
  complete: () => {
    setInterval(() => {
      const el = document.getElementById('imagenAnimada2');
      el.style.filter = `
        hue-rotate(${Math.floor(Math.random() * 360)}deg)
        blur(${Math.random() * 2}px)
        contrast(${Math.random() * 1.5 + 0.5})
        saturate(${Math.random() * 2 + 0.5})
      `;
      el.style.transform = `
        scale(${(Math.random() * 0.7 + 0.8).toFixed(2)})
        rotate(${Math.floor(Math.random() * 360)}deg)
        skew(${Math.random() * 30 - 15}deg, ${Math.random() * 30 - 15}deg)
        translate(${Math.random() * 40 - 20}px, ${Math.random() * 40 - 20}px)
      `;
    }, 1000);
  }
});



  // Textos: distorsión onda + glitch matriz + reconstrucción + resplandor
  const textos = ['#texto1', '#texto2', '#texto3'];
  textos.forEach((id, i) => {
    anime({
      targets: id,
      opacity: [0, 1],
      translateY: [-100, 0],
      delay: i * 1000 + 500,
      duration: 1000,
      easing: 'easeOutElastic(1, .5)',
      begin: () => {
        const el = document.querySelector(id);
        el.innerHTML = el.innerText
          .split('')
          .map(c => `<span class="inline-block opacity-30 text-green-500">${c}</span>`)
          .join('');
      },
      complete: () => {
        const el = document.querySelector(id);
        el.innerHTML = el.innerText;
        anime({
          targets: id,
          boxShadow: [
            '0 0 10px #00ffff',
            '0 0 30px #ff00ff',
            '0 0 10px transparent'
          ],
          duration: 1500,
          easing: 'easeInOutSine',
          loop: true,
          direction: 'alternate'
        });

        // Textos vibran y saltan aleatoriamente
        setInterval(() => {
          el.style.transform = `rotate(${Math.random() * 20 - 10}deg) translateY(${Math.random() * 10}px)`;
          el.style.fontFamily = ['monospace', 'cursive', 'fantasy', 'Orbitron'][Math.floor(Math.random() * 4)];
        }, 800);
      }
    });
  });
</script>
<footer class="relative overflow-hidden bg-black text-white py-24 px-8 flex flex-col items-center space-y-10 z-10">
  <h2 id="footerTitle" class="text-5xl font-extrabold text-center">¡Gracias por visitar nuestro universo digital!</h2>
  
  <p id="footerSubtitle" class="text-xl italic text-center max-w-3xl">
    “La realidad es un glitch, el conocimiento es la clave para hackearla.” 🚀👾✨
  </p>

  <!-- Círculos psicodélicos flotantes -->
  <div id="particles" class="absolute inset-0 pointer-events-none z-0"></div>

  <!-- Botón alocado -->
  <button onclick="window.location.href='index.php'" id="footerButton" class="px-10 py-4 bg-gradient-to-r from-pink-500 via-yellow-500 to-green-500 text-black text-2xl font-bold rounded-full shadow-xl hover:scale-110 transition-transform duration-300 z-10">
    ¡Empieza la locura!
  </button>

  <!-- Créditos demente -->
  <p id="footerCredits" class="text-sm text-gray-400 z-10 tracking-widest">
    Proyecto Tamanaco, Powered by Caos & Café ☕🔥
  </p>
</footer>
<style>
  #particles span {
    position: absolute;
    border-radius: 50%;
    opacity: 0.6;
    pointer-events: none;
  }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
<script>
  // 1. Texto principal con distorsión + rebote + rotación
  anime({
    targets: '#footerTitle',
    scale: [
      { value: 0.5, duration: 300 },
      { value: 1.2, duration: 500 },
      { value: 1, duration: 400 }
    ],
    rotate: [
      { value: '5turn', duration: 2000 }
    ],
    color: ['#ff00ff', '#00ffff', '#ffffff'],
    easing: 'easeOutElastic(1, .6)',
    delay: 100
  });

  // 2. Subtítulo entra glitch + opacidad
  anime({
    targets: '#footerSubtitle',
    opacity: [0, 1],
    translateY: [-50, 0],
    delay: 1500,
    duration: 1200,
    easing: 'easeOutExpo',
  });

  // 3. Botón parpadea neón y rebota infinito
  anime({
    targets: '#footerButton',
    scale: [
      { value: 1.05, duration: 800 },
      { value: 1, duration: 800 }
    ],
    boxShadow: [
      '0 0 20px #0ff',
      '0 0 40px #f0f',
      '0 0 0px transparent'
    ],
    easing: 'easeInOutSine',
    direction: 'alternate',
    loop: true
  });

  // 4. Texto de créditos rotación loca + glitch tipo CRT
  anime({
    targets: '#footerCredits',
    rotateZ: [
      { value: 10, duration: 200 },
      { value: -10, duration: 200 },
      { value: 0, duration: 300 }
    ],
    scale: [
      { value: 1.2, duration: 300 },
      { value: 1, duration: 400 }
    ],
    loop: true,
    easing: 'easeInOutQuad',
    delay: 3000
  });

  // 5. Partículas flotantes psicodélicas
  const particleContainer = document.getElementById('particles');
  const colors = ['#ff00ff', '#00ffff', '#ffff00', '#00ff00', '#ff0000'];

  for (let i = 0; i < 40; i++) {
    const span = document.createElement('span');
    const size = Math.random() * 30 + 10;
    span.style.width = size + 'px';
    span.style.height = size + 'px';
    span.style.background = colors[Math.floor(Math.random() * colors.length)];
    span.style.top = Math.random() * 100 + '%';
    span.style.left = Math.random() * 100 + '%';
    particleContainer.appendChild(span);

    anime({
      targets: span,
      translateX: () => anime.random(-200, 200),
      translateY: () => anime.random(-200, 200),
      scale: [
        { value: 0.5, duration: 500 },
        { value: 1.5, duration: 500 }
      ],
      rotate: '1turn',
      direction: 'alternate',
      duration: anime.random(3000, 6000),
      delay: anime.random(0, 2000),
      loop: true,
      easing: 'easeInOutSine'
    });
  }

  // 6. Todo el footer vibra cada cierto tiempo
  setInterval(() => {
    const footer = document.querySelector('footer');
    footer.style.transform = `rotate(${Math.random() * 2 - 1}deg) translate(${Math.random() * 5}px, ${Math.random() * 5}px)`;
  }, 400);

  // 7. Cambios de fuente y color en el título constantemente
  const fuentes = ['cursive', 'fantasy', 'monospace', 'Orbitron', 'Comic Sans MS'];
  setInterval(() => {
    const title = document.getElementById('footerTitle');
    title.style.fontFamily = fuentes[Math.floor(Math.random() * fuentes.length)];
    title.style.color = colors[Math.floor(Math.random() * colors.length)];
  }, 1000);
</script>

</body>

</html>