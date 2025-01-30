import { loadLogUsuarios } from "../components/log_usuarios.js";
import { loadLogAplicacion } from "../components/log_aplicacion.js";
import { loadDoctorCitas, deleteDoctorCita } from "../components/citas.js";

document.addEventListener("DOMContentLoaded", () => {
    const idRol = localStorage.getItem("id_rol"); // Obtener el ID del rol del usuario
    const idDoctor = localStorage.getItem("id_doctores") || null;

    if (!idRol) {
        alert("No se encontró un rol asignado. Contacta al administrador.");
        return;
    }

    // Validar la existencia de contenedores
    const opciones = document.getElementById("opciones");
    const secciones = document.getElementById("secciones");
    

    if (!opciones || !secciones) {
        console.error("No se encontraron los contenedores necesarios en el DOM.");
        return;
    }

    // Obtener los permisos del rol desde el backend
    fetch("http://localhost/GoCanSeguridadSistemas/src/modules/php/validar_permisos.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_rol=${encodeURIComponent(idRol)}`,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                generarInterfaz(data.permisos, opciones, secciones, idDoctor);
            } else {
                alert("Error al obtener permisos del rol: " + data.message);
            }
        })
        .catch((error) => console.error("Error al obtener permisos del rol:", error));
});

function generarInterfaz(permisos, opciones, secciones, idDoctor) {
    if (permisos.registro_mascotas) { //REGISTRO MASCOTAS
        opciones.innerHTML += `
            <div id="bt0" class="button" onclick="openModal('petModal')">
                <i class="fi fi-sr-paw-heart"></i>
                <h5>Registro mascotas</h5>
            </div>`;
    }

    if (permisos.ver_mascotas_registradas) { //VER MASCOTAS REGISTRADAS
        opciones.innerHTML += `
            <div id="bt1" class="button" onclick="openModal('tablaModal')">
                <i class="fi fi-sr-pets"></i>
                <h5>Información Mascotas</h5>
            </div>`;
    }

    if (permisos.registro_receta) { //REGISTRO RECETAS
        opciones.innerHTML += `
            <div id="bt2" class="button" onclick="openModal('reportModal')">
                <i class="fi fi-sr-clipboard-list"></i>
                <h5>Registro Reportes</h5>
            </div>`;
    }

    if (permisos.ver_reportes_recetas) { //VER HISTORIAL DE RECETAS
        opciones.innerHTML += `
            <div id="bt3" class="button" onclick="openModal('historyModal')">
                <i class="fi fi-sr-time-past"></i>
                <h5>Historial Reportes</h5>
            </div>`;
    }

    if (permisos.ver_clientes_registrados) { //VER CLIENTES REGISTRADOS
        opciones.innerHTML += `
            <div id="bt4" class="button" onclick="openModal('clientesModal')">
                <i class="fi fi-sr-target-audience"></i>
                <h5>Clientes Registrados</h5>
            </div>`;
    }

    if (permisos.gestion_roles) { //GESTION DE ROLES
        opciones.innerHTML += `
            <div id="bt5" class="button" onclick="openModal('rolesModal')">
                <i class="fi fi-sr-dice-d6"></i>
                <h5>Administración de Roles</h5>
            </div>`;
    }

    if (permisos.editar_configuracion) { //EDITAR CONFIGURACION DE CONTRASEÑAS
        opciones.innerHTML += `
            <div id="bt6" class="button" onclick="openModal('passwordConfigModal')">
                <i class="fi fi-sr-settings"></i>
                <h5>Configuración Contraseñas</h5>
            </div>`;
    }

    if (permisos.registro_funcionarios) { //REGISTRO FUNCIONARIOS 
        opciones.innerHTML += `
            <div id="bt7" class="button" onclick="openModal('doctorModal')">
                <i class="fi fi-sr-stethoscope"></i>
                <h5>Registro funcionarios</h5>
            </div>`;
    }

    if (permisos.administracion_mascota) { //ADMINISTRACION MASCOTAS
        opciones.innerHTML += `
            <div id="bt8" class="button" onclick="openModal('tablaModalGestion')">
                <i class="fi fi-sr-pets"></i>
                <h5>Gestión Mascotas</h5>
            </div>`;
    }

    if (permisos.registrar_citas) { //AGENTAR CITA
        opciones.innerHTML += `
            <div id="bt9" class="button" onclick="openModal('reserveModal')">
                <i class="fi fi-sr-notebook-alt"></i>
                <h5>Agendar Cita</h5>
            </div>`;
    }

    if (idDoctor && permisos.ver_citas) { //VER CITAS DOCTORES
        secciones.innerHTML += `
        <div class="separator" style="margin: 30px;">
            <h3>Citas Próximas</h3>
            <button id="sortButton" onclick="sortCitas()">Ordenar por Fecha</button>
            <input type="text" id="searchInput" oninput="filtrarCitas()" placeholder="Buscar por propietario...">
        </div>
        <table id="citasTable" style="border-radius: 8px;">
            <tbody>
                <!-- Citas se llenarán dinámicamente -->
            </tbody>
        </table>`;

        console.log(`Cargando citas para el doctor ID: ${idDoctor}`);

        loadDoctorCitas(
            `http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_citas_doctor.php?id_doctor=${idDoctor}`,
            "#citasTable tbody"
        );
    } else if (idDoctor==null && permisos.ver_citas) { //VER CITAS CLIENTES
        opciones.innerHTML += `
        <div id="bt10" class="button" onclick="openModal('viewReservationsModal');">
            <i class="fi fi-sr-ballot"></i>
            <h5>Reservas</h5>
        </div>`;
    }

    if (permisos.vista_log_usuarios) { //VISTA LOG USUARIOS
        secciones.innerHTML += `
            <section style="margin: 30px; max-height: 270px; margin-bottom: 30px;">
                <h3 class="separator" style="margin-bottom: 20px;">Registro de Usuarios en la Página</h3>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="log-usuarios-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nombre del Usuario</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se añadirán aquí mediante JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>`;
            const urlLogUsuarios = 'http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_logs_usuarios.php';
        loadLogUsuarios(urlLogUsuarios, "#log-usuarios-table tbody");
    }

    if (permisos.vista_log_aplicacion) { //VISTA LOG APLICACION
        secciones.innerHTML += `
            <section style="margin: 30px; max-height: 270px; margin-bottom: 30px;">
                <h3 class="separator" style="margin-bottom: 20px;">Registro de Acciones en la Aplicación</h3>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                    <table id="log-aplicacion-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Nombre del Usuario</th>
                                <th>Acción</th>
                                <th>Función Afectada</th>
                                <th>Dato Modificado</th>
                                <th>Descripción</th>
                                <th>Valor Original</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se añadirán aquí mediante JavaScript -->
                        </tbody>
                    </table>
                </div>
            </section>`;

        const urlLogAplicacion = "http://localhost/GoCanSeguridadSistemas/src/modules/php/obtener_log_aplicacion.php";
        loadLogAplicacion(urlLogAplicacion, "#log-aplicacion-table tbody");
    }

    if (permisos.vista_servicios_anuncios) { //VISTA SERVICIOS Y ANUNCIOS
        secciones.innerHTML += `
        <section class="section category" id="category" aria-label="category">
            <div class="container">
            <h2 class="h2 section-title">
                <span class="span">Ofrecemos</span> servicios como
            </h2>
            <ul class="has-scrollbar">
                <li class="scrollbar-item">
                <div class="category-card">
                    <figure class="card-banner img-holder" style="--width: 330; --height: 300;">
                    <img src="../../assets/Images/servicio1.jpg" width="330" height="300" loading="lazy" alt="Cat Food"
                        class="img-cover">
                    </figure>
                    <h3 class="h3">
                    <a href="#" class="card-title">Vacunas</a>
                    </h3>
                </div>
                </li>
                <li class="scrollbar-item">
                <div class="category-card">
                    <figure class="card-banner img-holder" style="--width: 330; --height: 300;">
                    <img src="../../assets/Images/servicio2.jpg" width="330" height="300" loading="lazy" alt="Cat Toys"
                        class="img-cover">
                    </figure>
                    <h3 class="h3">
                    <a href="#" class="card-title">Peluquería</a>
                    </h3>
                </div>
                </li>
                <li class="scrollbar-item">
                <div class="category-card">
                    <figure class="card-banner img-holder" style="--width: 330; --height: 300;">
                    <img src="../../assets/Images/servicio3.jpg" width="330" height="300" loading="lazy" alt="Dog Food"
                        class="img-cover">
                    </figure>
                    <h3 class="h3">
                    <a href="#" class="card-title">Odontología</a>
                    </h3>
                </div>
                </li>
                <li class="scrollbar-item">
                <div class="category-card">
                    <figure class="card-banner img-holder" style="--width: 330; --height: 300;">
                    <img src="../../assets/Images/servicio4.jpg" width="330" height="300" loading="lazy" alt="Dog Toys"
                        class="img-cover">
                    </figure>
                    <h3 class="h3">
                    <a href="#" class="card-title">Desparasitación</a>
                    </h3>
                </div>
                </li>
                <li class="scrollbar-item">
                <div class="category-card">
                    <figure class="card-banner img-holder" style="--width: 330; --height: 300;">
                    <img src="../../assets/Images/Servicio5.jpg" width="330" height="300" loading="lazy"
                        alt="Dog Sumpplements" class="img-cover">
                    </figure>
                    <h3 class="h3">
                    <a href="#" class="card-title">Atención 24/7</a>
                    </h3>
                </div>
                </li>
            </ul>
            </div>
        </section>

        <!-- OFERTAS -->
        <section class="section offer" aria-label="offer">
            <div class="container">
            <ul class="grid-list">
                <li>
                <div class="offer-card has-bg-image">
                    <div class="bg-blur" style="background-image: url('../../assets/Images/offer-banner-1.jpg');"></div>
                    <p class="card-subtitle">Aprovecha la oferta</p>
                    <h3 class="h3 card-title">
                        2x1 <span class="span">en la vacuna triplefelina</span>
                    </h3>
                    <a href="#" class="btn">Leer Más</a>
                </div>
                </li>
                <li>
                <div class="offer-card has-bg-image">
                    <div class="bg-blur" style="background-image: url('../../assets/Images/offer-banner-2.jpg');"></div>
                    <p class="card-subtitle">¿Quieres viajar con tu mascota?</p>
                    <h3 class="h3 card-title">
                        Conoce los Requisitos <span class="span">para viajar con tu mascota</span>
                    </h3>
                    <a href="#" class="btn">Leer Más</a>
                </div>
                </li>
                <li>
                <div class="offer-card has-bg-image">
                    <div class="bg-blur" style="background-image: url('../../assets/Images/offer-banner-3.jpg');"></div>
                    <p class="card-subtitle">Ampliación de vacunas</p>
                    <h3 class="h3 card-title">
                        Ahora en GoCan <span class="span">puedes vacunar a tus conejos</span>
                    </h3>
                    <a href="#" class="btn">Leer Más</a>
                </div>
                </li>
            </ul>
            </div>
        </section>`;

    }

    if (permisos.vista_catalogo) {
        secciones.innerHTML += `
        <script src="../core/catalogo.js"></script>
        <script src="../core/favoritos.js"></script>
        <section class="section product" id="shop" aria-label="product">
        <div class="container">
          <h2 class="h2 section-title">
            <span class="span">Catálogo</span> en línea
          </h2>
          <div class="carousel-container">
            <button class="carousel-button prev" onclick="moveCarousel(-1)">
              &#10094;
            </button>
            <div class="carousel-slide">
              <ul class="grid-list">
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C1A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Commodo leo sed porta"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C1B.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Commodo leo sed porta"
                        class="img-cover hover"
                      />

                      <button
                        class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Dog Chow Adulto: Medianos</a
                        >
                        <p class="invisible descripcion">Alimento balanceado para perros adultos de tamaño mediano, formulado para apoyar la salud general, incluyendo digestión, huesos y pelaje.
                        </p>
                      </h3>
                      <data class="card-price precio" value="88"
                        >88 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C1A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C2A.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Purus consequat congue sit"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C2B.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Purus consequat congue sit"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Dog Chow Adulto: Pequeños</a
                        >
                        <p class="invisible descripcion">Alimento completo para perros adultos de raza pequeña, diseñado para optimizar la salud digestiva y mantener un pelaje brillante.
                        </p>
                      </h3>

                      <data class="card-price precio" value="86"
                        >86 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C2A.jpg</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C3A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C3A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover hover"
                      />
                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Sobre de Comida Dog Chow Cena Carne: Adultos</a
                        >
                        <p class="invisible descripcion">Sobre de comida para perros adultos, sabor carne, diseñado para ofrecer una cena nutritiva y sabrosa.
                        </p>
                      </h3>
                      <data class="card-price precio" value="7"
                        >7 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C3A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="width: 360; height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C4A.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C4B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Dog Chow Pollo Molido Todas las Etapas</a
                        >
                        <p class="invisible descripcion">Alimento de pollo molido para perros de todas las etapas de vida, proporciona nutrición completa y equilibrada.
                        </p>
                      </h3>
                      <data class="card-price precio" value="55"
                        >55 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C4A.jpg</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C5A.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C5B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Morbi vel arcu scelerisque"
                        class="img-cover hover"
                      />
                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Comida para Perros DOGUI Adulto</a
                        >
                        <p class="invisible descripcion">Alimento balanceado especialmente diseñado para perros adultos, ofreciendo una nutrición completa y equilibrada para su bienestar.
                        </p>
                      </h3>

                      <data class="card-price precio" value="55.5"
                        >55.5 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C5A.jpg</p>
                    </div>
                  </div>
                </li>

                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C6A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Nam justo libero porta ege"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C6B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Nam justo libero porta ege"
                        class="img-cover hover"
                      />
                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Doguito purina carne</a
                        >
                        <p class="invisible descripcion">Una nutritiva y deliciosa opción para perros adultos, formulada con carne de alta calidad para una alimentación balanceada y sabrosa.
                        </p>
                      </h3>
                      <data class="card-price precio" value="11"
                        >11 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C6A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C7A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Nam justo libero porta ege"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C7B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Nam justo libero porta ege"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>

                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>

                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Royal Canin Medio Adulto</a
                        >
                        <p class="invisible descripcion">Comida diseñada específicamente para perros adultos de tamaño mediano, proporcionando una nutrición completa y equilibrada para su salud y bienestar.
                        </p>
                      </h3>

                      <data class="card-price precio" value="90"
                        >90 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C7A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C8A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C8B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>

                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Alimento Royal Canin Mini Junior</a
                        >
                        <p class="invisible descripcion">Comida especialmente formulada para cachorros de razas pequeñas, brindándoles la nutrición adecuada para su crecimiento y desarrollo óptimos.
                        </p>
                      </h3>
                      <data class="card-price precio" value="72"
                        >72 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C8A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C9A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C9B.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Whiskas Sabor carne para adultos</a
                        >
                        <p class="invisible descripcion">Alimento diseñado para gatos adultos, ofreciendo un sabor a carne irresistible y una nutrición completa para mantener su salud y vitalidad.
                        </p>
                      </h3>
                      <data class="card-price precio" value="50"
                        >50 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C9A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C10A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C10B.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Whiskas Sabor pollo para adultos</a
                        >
                        <p class="invisible descripcion">Alimento especialmente creado para gatos adultos, con un delicioso sabor a pollo y una fórmula que proporciona la nutrición completa que necesitan para mantenerse saludables y activos.
                        </p>
                      </h3>
                      <data class="card-price precio" value="42"
                        >42 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C10A.png</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C11A.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C11B.jpg"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover hover"
                      />

                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Felix Purina Fantastic Comida Húmeda para Gato
                          Adulto Pack Surtido Carnes</a
                        >
                        <p class="invisible descripcion">Selección variada de alimentos húmedos para gatos adultos, ofreciendo una mezcla deliciosa de carnes para satisfacer el paladar de tu felino y proporcionarle una alimentación equilibrada.
                        </p>
                      </h3>
                      <data class="card-price precio" value="66"
                        >66 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C11A.jpg</p>
                    </div>
                  </div>
                </li>
                <li>
                  <div class="product-card">
                    <div
                      class="card-banner img-holder"
                      style="--width: 360; --height: 360"
                    >
                      <img
                        src="../../assets/Images/Catalogo/C12A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover default"
                      />
                      <img
                        src="../../assets/Images/Catalogo/C12A.png"
                        width="360"
                        height="360"
                        loading="lazy"
                        alt="Etiam commodo leo sed"
                        class="img-cover hover"
                      />
                      <button
                      class="card-action-btn favorito"
                        aria-label="add to card"
                        title="Add To Card"
                      >
                        <ion-icon
                          name="bookmark-outline"
                          aria-hidden="true"
                        ></ion-icon>
                      </button>
                    </div>
                    <div class="card-content">
                      <div class="wrapper">
                        <div class="rating">
                          <span class="star" data-value="5">&#9733;</span>
                          <span class="star" data-value="4">&#9733;</span>
                          <span class="star" data-value="3">&#9733;</span>
                          <span class="star" data-value="2">&#9733;</span>
                          <span class="star" data-value="1">&#9733;</span>
                        </div>
                        <!-- Número de reseñas -->
                        <span class="review-count">(0)</span>
                      </div>
                      <h3 class="h3">
                        <a
                          class="card-title nombre"
                          >Gato Castrado Podium Cat</a
                        >
                        <p class="invisible descripcion">Alimento especialmente diseñado para gatos castrados, con una fórmula que ayuda a mantener un peso saludable y cuidar la salud del tracto urinario, proporcionando una nutrición completa y equilibrada para su bienestar.
                        </p>
                      </h3>
                      <data class="card-price precio" value="28"
                        >28 bs</data
                      >
                      <p class="invisible categoria">Comida</p>
                      <p class="invisible imagen">http://localhost/GoCanSeguridadSistemas/src/assets/Images/Catalogo/C12A.png</p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <button class="carousel-button next" onclick="moveCarousel(1)">
              &#10095;
            </button>
          </div>
        </div>
        </section>`;

        let currentSlide = 0;

        const moveCarousel = step => {
            const slideContainer = document.querySelector('.carousel-slide');
            const slides = document.querySelectorAll('.product-card');
            const slideWidth = document.querySelector('.product-card').clientWidth;
            const totalWidth = slideContainer.scrollWidth;
            const visibleWidth = slideContainer.offsetWidth;
            const maxSlide = Math.floor((totalWidth - visibleWidth) / (slideWidth )); // 15 es el margen entre tarjetas
          
            currentSlide += step;
            currentSlide = Math.max(0, Math.min(currentSlide, maxSlide)); // Limitar currentSlide entre 0 y maxSlide
          
            const newTransform = -currentSlide * (slideWidth + 25); // 15 es el margen derecho de .product-card
            slideContainer.style.transform = `translateX(${newTransform}px)`;
        };
          
        document.querySelector('.carousel-button.prev').addEventListener('click', () => moveCarousel(-1));
        document.querySelector('.carousel-button.next').addEventListener('click', () => moveCarousel(1));
    }

    // Si no hay permisos, mostrar mensaje
    if (opciones.innerHTML.trim() === "" && secciones.innerHTML.trim() === "") {
        secciones.innerHTML = "<p>No tienes permisos para acceder a esta funcionalidad.</p>";
    }
}

// Funciones de botones
function verMascotasRegistradas() {
    alert("Accediendo a la Información de Mascotas...");
}

function gestionarRoles() {
    alert("Accediendo a Gestión de Roles...");
}

function editarConfiguracion() {
    alert("Accediendo a Configuración...");
}

// Funciones de botones
function sortCitas() {
    alert("Ordenando Citas...");
}

function filtrarCitas() {
    const searchText = document.getElementById("searchInput").value.toLowerCase();
    const idDoctor = localStorage.getItem("id_doctores");
    if (idDoctor) {
        loadDoctorCitas("http://localhost/GoCanSeguridadSistemas/src/modules/php/doctores.php", "#citasTable tbody", searchText);
    }
}
