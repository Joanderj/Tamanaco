<?php
// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'bd_tamanaco');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$sql = "SELECT * FROM empresa WHERE id_empresa=1";
$result = $conexion->query($sql);
$empresa = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $empresa['nombre']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Fuentes Web de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   

    <!-- Hoja de Estilos de Iconos -->
     
    <link href="public/lib/fontawesome-free-6.7.2-web/css/all.min.css" rel="stylesheet">
    <!-- Hoja de Estilos de Librerías -->
    <link href="public/lib/animate/animate.min.css" rel="stylesheet">
    <link href="public/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Hoja de Estilos Personalizada de Bootstrap -->
    <link href="public/css/bootstrap.min.css" rel="stylesheet">

    <!-- Hoja de Estilos de la Plantilla -->
    <link href="public/css/style.css" rel="stylesheet">
</head>

<body>
 <!-- Topbar Start -->
<div class="container-fluid">
    <div class="row bg-secondary py-2 px-lg-5">
        <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
            <div class="d-inline-flex align-items-center">
   
                <a class="text-white px-3" href="">Help</a>
                <span class="text-white">|</span>
                <a class="text-white pl-3" href="">Support</a>
            </div>
        </div>
        <div class="col-lg-6 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <a class="text-white px-3" href="https://www.facebook.com/@tamanacosports/">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a class="text-white px-3" href="">
    <i class="fab fa-x-twitter"></i>
</a>
               
                <a class="text-white px-3" href="https://www.instagram.com/tamanacosports/?hl=es">
                    <i class="fab fa-instagram"></i>
                </a>
                <a class="text-white pl-3" href="">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="row py-3 px-lg-5">
        <div class="col-lg-4">
            <a href="" class="navbar-brand d-none d-lg-block">
                <h1 class="m-0 display-5 text-capitalize"><span class="text-primary">Tama</span>naco</h1>
            </a>
        </div>
        <div class="col-lg-8 text-center text-lg-right">
            <div class="d-inline-flex align-items-center">
                <div class="d-inline-flex flex-column text-center pr-3 border-right">
                    <h6>Direccion</h6>
                    <p class="m-0"><?php echo $empresa['direccion']; ?></p>
                </div>
                <div class="d-inline-flex flex-column text-center px-3 border-right">
                    <h6>Correo</h6>
                    <p class="m-0"><?php echo $empresa['correo_1']; ?>
                    </p>
                </div>
                <div class="d-inline-flex flex-column text-center pl-3">
                    <h6>Telefono</h6>
                    <p class="m-0"><?php echo $empresa['telefono_1']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<!-- Navbar Start -->
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-dark navbar-dark py-3 py-lg-0 px-lg-5">
        <a href="" class="navbar-brand d-block d-lg-none">
            <h1 class="m-0 display-5 text-capitalize font-italic text-white"><span class="text-primary">Safety</span>First</h1>
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between px-3" id="navbarCollapse">
            <div class="navbar-nav mr-auto py-0">
                <a href="index.html" class="nav-item nav-link active">Inicio</a>
                <a href="about.html" class="nav-item nav-link text-white">Acerca de</a>
                <a href="contact.html" class="nav-item nav-link text-white">Contacto</a>
            </div>
            <div class="nav-item">
            <a href="public/seleccionar_perfil.php" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Iniciar sesión</a>

            </div>
        </div>
    </nav>
</div>
<!-- Navbar End -->


   <!-- Inicio del Carrusel -->
<div class="container-fluid px-0 mb-5">
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="w-100" src="public/img/carrusel3.jpg" alt="Imagen del carrusel 1">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 text-start">
                                <p class="fs-5 fw-medium text-primary text-uppercase animated slideInRight">55 años acompañando con el Deportista Latinoamericano</p>
                                <h1 class="display-1 text-white mb-5 animated slideInRight">Cree en ti, crece sin límites.</h1>
                                <a href="" class="btn btn-primary py-3 px-5 animated slideInRight">Explorar Más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img class="w-100" src="public/img/carrusel2.jpeg" alt="Imagen del carrusel 2">
                <div class="carousel-caption">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10 text-start">
                                <p class="fs-5 fw-medium text-primary text-uppercase animated slideInRight">Hazlo Posible</p>
                                <h1 class="display-1 text-white mb-5 animated slideInRight">Impulsado por tu pasión, supérate a lo grande.</h1>
                                <a href="" class="btn btn-primary py-3 px-5 animated slideInRight">Explorar Más</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botón para ir al slide anterior -->
        <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <!-- Botón para ir al siguiente slide -->
        <button class="carousel-control-next" type="button" data-bs-target="#header-carousel"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>
</div>
<!-- Carousel End -->
    <!-- About Start -->
    <div class="container-fluid py-5" id="about">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <img class="img-fluid mb-4 mb-lg-0" src="public/img/tamanaco.png" alt="">
                </div>
                <div class="col-lg-7">
                    <h6 class="text-uppercase text-primary mb-3" style="letter-spacing: 3px;">Sobre Nosotros</h6>
                    <h1 class="display-4 mb-3"><span class="text-primary">Historia de</span> Tamanaco</h1>
                    <p><?php echo $empresa['historia']; ?>.</p>
                    <a class="btn btn-primary font-weight-bold py-3 px-5 mt-2" type="button" data-toggle="modal" data-target="#exampleModalLong">Mas informacion</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

<!-- Inicio de Servicios -->
<div class="container-xxl py-5">
    <div class="container">
        <!-- Sección de Título de Servicios -->
        <div class="text-center mx-auto pb-4 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="fw-medium text-uppercase text-primary mb-2">Nuestros Mantenimientos</p>
            <h1 class="display-5 mb-4">Categorias de Mantenimientos</h1>
        </div>
        <!-- Fila de Servicios -->
        <div class="row gy-5 gx-4">
            <!-- Servicio: Mantenimiento de Máquinas para Productos Deportivos -->
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                <div class="service-item">
                    <img class="img-fluid" src="public/img/service-1.jpg" alt="Mantenimiento de Máquinas">
                    <div class="service-img">
                        <img class="img-fluid" src="public/img/service-1.jpg" alt="Mantenimiento de Máquinas">
                    </div>
                    <div class="service-detail">
                        <div class="service-title">
                            <hr class="w-25">
                            <h3 class="mb-0">Mantenimientos Preventivos</h3>
                            <hr class="w-25">
                        </div>
                        <div class="service-text">
                            <p class="text-white mb-0">Son acciones planificadas que se realizan de manera regular para evitar fallos o averías en equipos y sistemas, asegurando su correcto funcionamiento y prolongando su vida útil.</p>
                        </div>
                    </div>
                    <a class="btn btn-light" href="">Leer Más</a>
                </div>
            </div>
            <!-- Servicio: Ingeniería Civil y de Gas -->
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                <div class="service-item">
                    <img class="img-fluid" src="public/img/service-2.jpg" alt="Ingeniería Civil y de Gas">
                    <div class="service-img">
                        <img class="img-fluid" src="public/img/service-2.jpg" alt="Ingeniería Civil y de Gas">
                    </div>
                    <div class="service-detail">
                        <div class="service-title">
                            <hr class="w-25">
                            <h3 class="mb-0">Mantenimientos Correctivos</h3>
                            <hr class="w-25">
                        </div>
                        <div class="service-text">
                            <p class="text-white mb-0">Son intervenciones inmediatas que responden a fallos inesperados que afectan operaciones críticas, priorizando la solución rápida para minimizar impactos negativos.</p>
                        </div>
                    </div>
                    <a class="btn btn-light" href="">Leer Más</a>
                </div>
            </div>
            <!-- Servicio: Ingeniería de Energía y Potencia -->
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                <div class="service-item">
                    <img class="img-fluid" src="public/img/service-3.jpg" alt="Ingeniería de Energía y Potencia">
                    <div class="service-img">
                        <img class="img-fluid" src="public/img/service-3.jpg" alt="Ingeniería de Energía y Potencia">
                    </div>
                    <div class="service-detail">
                        <div class="service-title">
                            <hr class="w-25">
                            <h3 class="mb-0">Mantenimientos Urgentes</h3>
                            <hr class="w-25">
                        </div>
                        <div class="service-text">
                            <p class="text-white mb-0">Nuestro equipo se especializa en la ingeniería de energía y potencia, proporcionando soluciones de mantenimiento para garantizar el óptimo funcionamiento de las máquinas y sistemas energéticos.</p>
                        </div>
                    </div>
                    <a class="btn btn-light" href="">Leer Más</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin de Servicios -->

<!-- Features Start -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="position-relative me-lg-4">
                    <!-- Imagen de la característica -->
                    <img class="img-fluid w-100" src="public/img/jajajaa.png" alt="Descripción de la imagen">
                    <span class="position-absolute top-50 start-100 translate-middle bg-white rounded-circle d-none d-lg-block" style="width: 120px; height: 120px;"></span>
                    <!-- Botón para abrir el modal del video -->
                    <button type="button" class="btn-play" data-bs-toggle="modal"
                        data-src="public/img/tamanaco.mp4" data-bs-target="#videoModal">
                        <span></span>
                    </button>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <p class="fw-medium text-uppercase text-primary mb-2">¡Por qué elegirnos!</p>
                <h1 class="display-5 mb-4">Bienvenido a <?php echo $empresa['nombre']; ?></h1>
                <p class="mb-4"><?php echo $empresa['mision']; ?>.</p>
                <div class="row gy-4">
                    <div class="col-12">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                <i class="fa fa-check text-white"></i>
                            </div>
                            <div class="ms-4">
                                <h4>Nuestra Visión</h4>
                                <span><?php echo $empresa['vision']; ?>.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                <i class="fa fa-check text-white"></i>
                            </div>
                            <div class="ms-4">
                                <h4>Objetivo General</h4>
                                <span><?php echo $empresa['objetivo_general']; ?>.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-lg-square rounded-circle bg-primary">
                                <i class="fa fa-check text-white"></i>
                            </div>
                            <div class="ms-4">
                                <h4>Objetivos Específicos</h4>
                                <span><?php echo $empresa['objetivos_especificos']; ?>.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Features End -->



<!-- Video Modal Start -->
<div class="modal modal-video fade" id="videoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Video</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <!-- Relación de aspecto 16:9 -->
                <div class="ratio ratio-16x9">
                    <video class="embed-responsive-item" controls>
                        <source src="_img src=__ alt=_SegundoMarido__Segundo Marido - Opera 2025-03-03 15-55-14.mp4" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Video Modal End -->

    <!-- Inicio del Blog -->
<div class="container pt-5">
    <!-- Sección de Título del Blog -->
    <div class="d-flex flex-column text-center mb-5">
        <h4 class="text-secondary mb-3">Blog de Tamanaco</h4>
        <h1 class="display-4 m-0"><span class="text-primary">Actualizaciones</span> Desde el Blog</h1>
    </div>
    <!-- Fila de Artículos del Blog -->
    <div class="row pb-3">
        <!-- Primer Artículo -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 mb-2">
                <img class="card-img-top" src="public/img/project-1.jpg" alt="Mantenimiento de máquinas">
                <div class="card-body bg-light p-4">
                    <h4 class="card-title text-truncate">Mantenimiento de Máquinas en Tamanaco</h4>
                    <div class="d-flex mb-3">
                        <small class="mr-2"><i class="fa fa-user text-muted"></i> Admin</small>

                    </div>
                    <p>El mantenimiento regular de nuestras máquinas es crucial para garantizar un rendimiento óptimo. En Tamanaco, realizamos revisiones periódicas y ajustes necesarios para asegurar que cada equipo funcione de manera eficiente.</p>
                    <a class="font-weight-bold" href="">Leer Más</a>
                </div>
            </div>
        </div>
        <!-- Segundo Artículo -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 mb-2">
                <img class="card-img-top" src="public/img/project-2.jpg" alt="Importancia del Mantenimiento">
                <div class="card-body bg-light p-4">
                    <h4 class="card-title text-truncate">La Importancia del Mantenimiento</h4>
                    <div class="d-flex mb-3">
                        <small class="mr-2"><i class="fa fa-user text-muted"></i> Admin</small>
                    </div>
                    <p>Un buen mantenimiento no solo prolonga la vida útil de las máquinas, sino que también previene accidentes y mejora la seguridad de nuestros empleados. En Tamanaco, priorizamos la formación continua en este aspecto.</p>
                    <a class="font-weight-bold" href="">Leer Más</a>
                </div>
            </div>
        </div>
        <!-- Tercer Artículo -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 mb-2">
                <img class="card-img-top" src="public/img/project-3.jpg" alt="Nuevas Tecnologías en Mantenimiento">
                <div class="card-body bg-light p-4">
                    <h4 class="card-title text-truncate">Nuevas Tecnologías en Mantenimiento</h4>
                    <div class="d-flex mb-3">
                        <small class="mr-2"><i class="fa fa-user text-muted"></i> Admin</small>
             
                    </div>
                    <p>En Tamanaco, estamos adoptando nuevas tecnologías para el mantenimiento de nuestras máquinas. Esto incluye sistemas de monitoreo en tiempo real que nos permiten detectar problemas antes de que ocurran.</p>
                    <a class="font-weight-bold" href="">Leer Más</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fin del Blog -->
<!-- Contacto Inicio -->
<div class="container-xxl py-5">
    <div class="container">
        <div class="text-center">
            <h1 class="display-4 m-0"><span class="text-primary"></span>Contactanos</h1>
        </div></div>
        <div class="row g-5 justify-content-center mb-5">
            <!-- Sección de contacto telefónico -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-phone-alt fa-2x text-primary"></i>
                    </div>
                    <h4 class="mb-3">Número de Teléfono</h4>
                    <p class="mb-2"><?php echo $empresa['telefono_2']; ?></p>
                    <p class="mb-4">  </p>
                    <a class="btn btn-primary px-4" href="tel:+0123456789">Llama Ahora <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <!-- Sección de contacto por correo electrónico -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-envelope-open fa-2x text-primary"></i>
                    </div>
                    <h4 class="mb-3">Dirección de Correo Electrónico</h4>
                    <p class="mb-2"><?php echo $empresa['correo_2']; ?></p>
                    <p class="mb-4">   </p>
                    <a class="btn btn-primary px-4" href="mailto:info@tamanaco.com">Envía un Correo <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
            <!-- Sección de dirección de la oficina -->
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-light text-center h-100 p-5">
                    <div class="btn-square bg-white rounded-circle mx-auto mb-4" style="width: 90px; height: 90px;">
                        <i class="fa fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <h4 class="mb-3">Dirección de la Oficina</h4>
                    <p class="mb-2"></p>
                    <p class="mb-4"><?php echo $empresa['direccion']; ?>.</p>
                    <a class="btn btn-primary px-4" href="https://goo.gl/maps/ejemploAcarigua" target="blank">Dirección <i class="fa fa-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-12 wow fadeInUp" data-wow-delay="0.1s">
                <iframe class="w-100"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15875.000000000002!2d-69.2045714!3d9.5747336!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e7dc15f78f2cd93%3A0x22db5d950a926a91!2sTamanaco%20C.A.!5e0!3m2!1ses!2sbd!4v1603794290143!5m2!1ses!2sbd"
                frameborder="0" style="min-height: 450px; border:0;" allowfullscreen="" aria-hidden="false"
                tabindex="0"></iframe>
            </div>
        </div>
        
        
        
        <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <p class="fw-medium text-uppercase text-primary mb-2">Contáctanos</p>
                <h1 class="display-5 mb-4">Si Tienes Alguna Consulta, No Dudes en Contactarnos</h1>
                <p class="mb-4">¿Tienes alguna pregunta o necesitas más información? ¡Estamos aquí para ayudarte! No dudes en ponerte en contacto con nosotros.</p>
                <div class="row g-4">
                    <div class="col-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-primary rounded-circle">
                                <i class="fa fa-phone-alt text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Llámame</h6>
                                <span><?php echo $empresa['telefono_1']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex">
                            <div class="flex-shrink-0 btn-square bg-primary rounded-circle">
                                <i class="fa fa-envelope text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6>Escríbenos</h6>
                                <span><?php echo $empresa['correo_1']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.5s">
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="name" placeholder="Tu Nombre">
                                <label for="name">Tu Nombre</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" placeholder="Tu Correo Electrónico">
                                <label for="email">Tu Correo Electrónico</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="subject" placeholder="Asunto">
                                <label for="subject">Asunto</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Deja un mensaje aquí" id="message" style="height: 150px"></textarea>
                                <label for="message">Mensaje</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary py-3 px-5" type="submit">Enviar Mensaje</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Contacto Fin -->




    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-white py-5 px-sm-3 px-lg-5" style="margin-top: 90px;">
        <div class="row pt-5">
            <div class="col-12 mb-4 px-4">
                <div class="row mb-5 p-4" style="background: rgba(256, 256, 256, .05);">
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-primary text-uppercase mb-2" style="letter-spacing: 5px;">Direccion</h5>
                            <p class="mb-4 m-md-0"><?php echo $empresa['direccion']; ?>.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-primary text-uppercase mb-2" style="letter-spacing: 5px;">Correo</h5>
                            <p class="mb-4 m-md-0"><?php echo $empresa['correo_1']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-md-center">
                            <h5 class="text-primary text-uppercase mb-2" style="letter-spacing: 5px;">Telefono</h5>
                            <p class="m-0"><?php echo $empresa['telefono_1']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-12">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <p>Somos la marca deportiva que representa a los venezolanos, con el firme propósito de creer en ti y apoyarte en tu camino hacia la superación personal y deportiva.</p>
                        <div class="d-flex justify-content-start mt-4">
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['twitter']; ?>"><i class="fab fa-twitter"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['facebook']; ?>"><i class="fab fa-facebook-f"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square mr-2" href="<?php echo $empresa['instagram']; ?>"><i class="fab fa-linkedin-in"></i></a>
                            <a class="btn btn-sm btn-outline-light btn-sm-square" href="<?php echo $empresa['instagram']; ?>"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Destacados</h5>
                        <div class="d-flex flex-column justify-content-start">
                            <a class="text-white btn-scroll mb-2" href="#service"><i class="fa fa-angle-right mr-2"></i>Sobre nosotros</a>
                            <a class="text-white btn-scroll mb-2" href="#service"><i class="fa fa-angle-right mr-2"></i>Mision y Vision</a>
                            <a class="text-white btn-scroll mb-2" href="#service"><i class="fa fa-angle-right mr-2"></i>Blog</a>
                            <a class="text-white btn-scroll mb-2" href="#service"><i class="fa fa-angle-right mr-2"></i>Contactanos</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mb-5">
                <h5 class="text-primary text-uppercase mb-4" style="letter-spacing: 5px;">Comprometidos contigo y el deporte.
                </h5>
                <p>En Tamanaco, somos más que una marca deportiva: somos tu aliado para crecer, superar tus límites y alcanzar tus metas, todo con productos de calidad que reflejan el espíritu venezolano.
                </p>
                <div class="w-100">
                    
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-white text-center border-top py-4 px-sm-3 px-md-5" style="border-color: rgba(256, 256, 256, .05) !important;">
        <p class="m-0 text-white"> Venezuela <a href="#"> &copy;Tamanaco. </a>|Todos los derechos reservados 2025 | Desarrollado por: <a href="https://htmlcodex.com">Joander Gallo, Sebastian Moquera, Brian Verga, Daniel Betecur, Rafa Quesale, Luciano Taradosona y nuestro pana Jose pastor</a></p>
    </div>
    <!-- Footer End -->

    



    <!-- JavaScript Librerias -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

  

    <!-- Javascript -->
    <script src="js/main.js"></script>
</body>

</html>

<?php
// Captura la ruta actual de la URL
$ruta = $_SERVER['REQUEST_URI'];

// Lógica para cargar contenido dinámico basado en la ruta
switch ($ruta) {
    case '/proyecto_tamanaco/tamanaco':
        $contenido = "";
        break;
    case '/proyecto_tamanaco':
    case '/proyecto_tamanaco/':
        $contenido = "";
        break;
    default:
        http_response_code(404);
        $contenido = "";
        break;
}
?>


  

    <div id="contenido">
        <?= $contenido; ?>
    </div>

    <script>
        // Modifica la URL sin recargar la página
        const rutaActual = window.location.pathname;

        // Esto asegura que el nombre del archivo esté oculto
        if (rutaActual.includes('index.php')) {
            const rutaLimpia = rutaActual.replace('index.php', '');
            window.history.replaceState({}, '', rutaLimpia);
        }
    </script>
