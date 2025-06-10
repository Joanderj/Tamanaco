<?php
include 'db_connection.php';

$id_tarea = $_GET['id_tarea'] ?? null;
$tarea = [];

if ($id_tarea) {
    $stmt = $conn->prepare("SELECT * FROM tareas WHERE id_tarea = ?");
    $stmt->execute([$id_tarea]);
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
    <form method="post" action="guardar_insumos_tarea.php">
      <input type="hidden" name="id_tarea" value="<?= $tarea['id_tarea'] ?>">
<!-- BOTÓN PARA ABRIR DROPDOWN -->
<div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
        <i class="fas fa-tools text-green-600"></i>Seleccione los repuestos necesarios
    </h2>
    <button type="button" onclick="toggleDropdownRepuestos()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 shadow flex items-center">
        <i class="fas fa-plus mr-2"></i> Agregar repuestos
    </button>
</div>

<!-- DROPDOWN BUSCADOR -->
<div id="dropdownRepuestos" class="hidden mb-4 border rounded-lg p-4 bg-white shadow">
  <input id="buscarRepuesto" type="text" oninput="cargarRepuestos(1)" placeholder="Buscar repuesto..."
         class="border p-2 rounded w-full mb-2 focus:ring-2 focus:ring-blue-300" />
  <div id="contenedorRepuestos" class="max-h-60 overflow-y-auto"></div>
</div>

<!-- TABLA DE REPUESTOS SELECCIONADOS -->
<div id="tablaRepuestosAgregados" class="hidden mt-4">
  <table class="min-w-full text-sm text-left border rounded shadow">
    <thead class="bg-gray-100">
      <tr>
        <th class="p-2">Imagen</th>
        <th class="p-2">Detalles</th>
        <th class="p-2">Disponible</th>
        <th class="p-2">Stock Máx</th>
        <th class="p-2">Cantidad</th>
        <th class="p-2 text-center">Acciones</th>
      </tr>
    </thead>
    <tbody id="listaRepuestosAgregados"></tbody>
  </table>
</div>
<script>
let repuestosSeleccionados = [];
let repuestosServicio = [];

function totalRepuestosAgregados() {
  return repuestosSeleccionados.length + repuestosServicio.length;
}

// --- CARGA INTERNO ---
function toggleDropdownRepuestos() {
  if (totalRepuestosAgregados() >= 2) {
    alert("Solo puedes agregar hasta 2 repuestos.");
    return;
  }
  document.getElementById("dropdownRepuestos").classList.toggle("hidden");
  cargarRepuestos(1);
}

function cargarRepuestos(pagina = 1) {
  const buscar = document.getElementById("buscarRepuesto").value;
  fetch(`buscar_repuestos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("contenedorRepuestos").innerHTML = html;
    });
}

function agregarRepuestoDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMaximo, marca, modelo, tipo) {
  if (totalRepuestosAgregados() >= 2) {
    alert("Límite de 2 repuestos alcanzado.");
    return;
  }
  if (repuestosSeleccionados.some(r => r.id === id)) {
    alert("Este repuesto ya fue agregado desde inventario.");
    return;
  }

  const r = {
    id, nombre, unidad, clasificacion, imagen,
    marca, modelo, tipo,
    cantidad: disponible > 0 ? 1 : 0,
    pendiente: disponible > 0 ? 0 : 1,
    disponible, stockMaximo,
    origen: 'interno'
  };
  repuestosSeleccionados.push(r);
  renderizarTablaRepuestos();
  document.getElementById("dropdownRepuestos").classList.add("hidden");
}

// --- CARGA EXTERNO ---
function toggleDropdownRepuestosServicios() {
  if (totalRepuestosAgregados() >= 2) {
    alert("Solo puedes agregar hasta 2 repuestos.");
    return;
  }
  document.getElementById("dropdownRepuestosServicios").classList.toggle("hidden");
  cargarRepuestosServicios(1);
}

function cargarRepuestosServicios(pagina = 1) {
  const buscar = document.getElementById("buscarServicioRepuesto").value;
  fetch(`buscar_servicios_repuestos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById("contenedorRepuestosServicios").innerHTML = html;
    });
}

function agregarRepuestoDesdeServicio(id, nombre) {
  if (totalRepuestosAgregados() >= 2) {
    alert("Límite de 2 repuestos alcanzado.");
    return;
  }
  if (repuestosServicio.some(r => r.id === id)) {
    alert("Este repuesto ya fue agregado desde servicio.");
    return;
  }
  repuestosServicio.push({
    id, nombre, cantidad: 1, origen: 'externo'
  });
  renderizarTablaRepuestos();
  document.getElementById("dropdownRepuestosServicios").classList.add("hidden");
}

// --- CARGA AUTOMÁTICA DE SERVICIO YA GUARDADO ---
function cargarRepuestosDesdeServicioExistente(data) {
  data.forEach(item => {
    if (item.origen === 'interno') {
      repuestosSeleccionados.push({
        id: item.id,
        nombre: item.nombre,
        unidad: item.unidad || '',
        clasificacion: item.clasificacion || '',
        imagen: item.imagen || '',
        marca: item.marca || '',
        modelo: item.modelo || '',
        tipo: item.tipo || '',
        cantidad: item.status_id === 25 ? item.cantidad : 0,
        pendiente: item.status_id === 26 ? item.cantidad : 0,
        disponible: item.disponible || 0,
        stockMaximo: item.stockMaximo || 0,
        origen: 'interno'
      });
    } else if (item.origen === 'externo') {
      repuestosServicio.push({
        id: item.id,
        nombre: item.nombre,
        cantidad: item.cantidad,
        origen: 'externo'
      });
    }
  });

  renderizarTablaRepuestos();
}

// --- TABLA ---
function renderizarTablaRepuestos() {
  const cuerpo = document.getElementById("listaRepuestosAgregados");
  const tabla = document.getElementById("tablaRepuestosAgregados");
  cuerpo.innerHTML = "";

  repuestosSeleccionados.forEach((r, i) => {
    const total = r.cantidad + r.pendiente;
    cuerpo.innerHTML += `
      <tr class="border-b">
        <td class="p-2"><img src="${r.imagen}" class="w-12 h-12 rounded" /></td>
        <td class="p-2">
          <strong>${r.nombre}</strong><br>
          <small>${r.marca} / ${r.modelo} / ${r.tipo}</small><br>
          <small class="text-blue-600">Origen: Inventario</small>
        </td>
        <td class="p-2 text-center">${r.disponible}</td>
        <td class="p-2 text-center">${r.stockMaximo}</td>
        <td class="p-2 text-center">
          <input type="number" value="${total}" min="1"
            onblur="actualizarCantidadRepuesto(${i}, this.value)"
            class="w-20 text-center border rounded p-1" />
          <div class="text-xs text-green-700">Planificado: ${r.cantidad}</div>
          ${r.pendiente > 0 ? `<div class="text-xs text-yellow-600">Pendiente: ${r.pendiente}</div>` : ''}
          ${total > r.stockMaximo ? `<div class="text-xs text-red-600">Supera stock máximo</div>` : ''}
        </td>
        <td class="p-2 text-center">
          <button onclick="verRepuesto(${r.id})" class="text-blue-600"><i class="fas fa-eye"></i></button>
          <button onclick="quitarRepuestoInterno(${i})" class="text-red-600"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>`;
  });

  repuestosServicio.forEach((r, i) => {
    cuerpo.innerHTML += `
      <tr class="border-b bg-gray-100">
        <td class="p-2"><i class="fas fa-tools text-xl"></i></td>
        <td class="p-2">
          <strong>${r.nombre}</strong><br>
          <small class="text-purple-600">Origen: Servicio externo</small>
        </td>
        <td class="p-2 text-center" colspan="2">N/A</td>
        <td class="p-2 text-center">
          <input type="number" value="${r.cantidad}" min="1"
            onblur="actualizarCantidadServicio(${i}, this.value)"
            class="w-20 text-center border rounded p-1" />
        </td>
        <td class="p-2 text-center">
          <button onclick="quitarRepuestoServicio(${i})" class="text-red-600"><i class="fas fa-trash-alt"></i></button>
        </td>
      </tr>`;
  });

  tabla.classList.toggle("hidden", totalRepuestosAgregados() === 0);
  actualizarInputsOcultosRepuestos();
}

// --- ACTUALIZACIONES ---
function actualizarCantidadRepuesto(i, valor) {
  const nueva = parseInt(valor);
  const r = repuestosSeleccionados[i];
  if (isNaN(nueva) || nueva < 1) return;

  if (nueva <= r.disponible) {
    r.cantidad = nueva;
    r.pendiente = 0;
  } else {
    const pendiente = nueva - r.disponible;
    mostrarModalPendienteRepuesto(pendiente, () => {
      r.cantidad = r.disponible;
      r.pendiente = pendiente;
      renderizarTablaRepuestos();
    }, () => {
      renderizarTablaRepuestos();
    });
  }

  renderizarTablaRepuestos();
}

function actualizarCantidadServicio(i, valor) {
  const nueva = parseInt(valor);
  if (!isNaN(nueva) && nueva > 0) {
    repuestosServicio[i].cantidad = nueva;
  } else {
    alert("Cantidad no válida");
  }
  renderizarTablaRepuestos();
}

// --- QUITAR ---
function quitarRepuestoInterno(i) {
  repuestosSeleccionados.splice(i, 1);
  renderizarTablaRepuestos();
}

function quitarRepuestoServicio(i) {
  repuestosServicio.splice(i, 1);
  renderizarTablaRepuestos();
}

// --- MODAL ---
function mostrarModalPendienteRepuesto(pendiente, aceptar, cancelar) {
  const modal = document.createElement("div");
  modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
  modal.id = "modalPendienteRepuesto";
  modal.innerHTML = `
    <div class="bg-white p-6 rounded shadow-lg text-center w-80">
      <p>¿Agregar <strong>${pendiente}</strong> como <span class="text-yellow-600">pendiente</span>?</p>
      <div class="mt-4 flex justify-around">
        <button onclick="aceptarPendienteRepuesto()" class="bg-green-600 text-white px-4 py-1 rounded">Sí</button>
        <button onclick="cancelarPendienteRepuesto()" class="bg-gray-600 text-white px-4 py-1 rounded">No</button>
      </div>
    </div>`;
  document.body.appendChild(modal);
  window.aceptarPendienteRepuesto = () => {
    aceptar();
    cerrarModalPendienteRepuesto();
  };
  window.cancelarPendienteRepuesto = () => {
    cancelar();
    cerrarModalPendienteRepuesto();
  };
}

function cerrarModalPendienteRepuesto() {
  const modal = document.getElementById("modalPendienteRepuesto");
  if (modal) modal.remove();
}

// --- INPUTS OCULTOS ---
function actualizarInputsOcultosRepuestos() {
  const contenedor = document.getElementById("inputsRepuestosOcultos");
  contenedor.innerHTML = "";
  let i = 0, hay = false;

  repuestosSeleccionados.forEach(r => {
    if (r.cantidad > 0) {
      contenedor.innerHTML += `
        <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
        <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cantidad}">
        <input type="hidden" name="repuestos[${i}][status_id]" value="25">
        <input type="hidden" name="repuestos[${i}][origen]" value="interno">`;
      i++;
      hay = true;
    }
    if (r.pendiente > 0) {
      contenedor.innerHTML += `
        <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
        <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.pendiente}">
        <input type="hidden" name="repuestos[${i}][status_id]" value="26">
        <input type="hidden" name="repuestos[${i}][origen]" value="interno">`;
      i++;
      hay = true;
    }
  });

  repuestosServicio.forEach(r => {
    contenedor.innerHTML += `
      <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
      <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cantidad}">
      <input type="hidden" name="repuestos[${i}][status_id]" value="26">
      <input type="hidden" name="repuestos[${i}][origen]" value="externo">`;
    i++;
    hay = true;
  });

  return hay;
}

// --- VALIDACIÓN FINAL ---
function validarRepuestosSeleccionados() {
  const hay = actualizarInputsOcultosRepuestos();
  if (!hay) {
    alert("Debes seleccionar al menos un repuesto.");
    return false;
  }
  return true;
}

function verRepuesto(id) {
  window.open(`ver_repuesto.php?id=${id}`, '_blank');
}
</script>

<div id="inputsRepuestosOcultos"></div>
<hr class="my-6">
<!-- Contenedor del buscador y tabla de herramientas -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
            <i class="fas fa-tools text-indigo-600"></i>Seleccione las herramientas necesarias 
        </h2>
        <button type="button" onclick="toggleDropdownHerramientas()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 shadow flex items-center">
            <i class="fas fa-plus mr-2"></i> Agregar herramientas
        </button>
    </div>

    <!-- Dropdown de selección de herramientas -->
    <div id="dropdownHerramientas" class="border mt-3 p-4 rounded-md shadow-md bg-white hidden relative z-10">
        <div class="mb-3">
            <input type="text" id="buscarHerramienta" placeholder="Buscar herramienta..."
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-indigo-500"
                onkeyup="cargarHerramientas(1)">
        </div>
        <div id="contenedorHerramientas" class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"></div>
        <div id="paginacionHerramientas" class="mt-3 flex justify-center gap-2"></div>
        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleDropdownHerramientas()"
                class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i> Cerrar
            </button>
        </div>
    </div>

    <!-- Dropdown de herramientas -->
<div id="dropdownHerramientas" class="hidden absolute bg-white shadow-md rounded z-40 w-full max-w-lg">
    <input type="text" id="buscarHerramienta" onkeyup="cargarHerramientas()" class="w-full p-2 border-b" placeholder="Buscar herramienta...">
    <div id="contenedorHerramientas" class="max-h-60 overflow-y-auto"></div>
</div>

<!-- Tabla de herramientas agregadas -->
<div id="tablaHerramientasAgregadas" class="mt-4 hidden">
    <table class="min-w-full bg-white shadow rounded">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="p-2">Imagen</th>
                <th class="p-2">Herramienta</th>
                <th class="p-2">Disponible</th>
                <th class="p-2">Stock Mín.</th>
                <th class="p-2">Stock Máx.</th>
                <th class="p-2">Cantidad</th>
                <th class="p-2">Acciones</th>
            </tr>
        </thead>
        <tbody id="listaHerramientasAgregadas"></tbody>
    </table>
</div>

<!-- Input ocultos para herramientas -->
<div id="inputsHerramientasPlanificadas"></div>
<div id="inputsHerramientasPendientes"></div>

<script>
let herramientasSeleccionadas = [];

function toggleDropdownHerramientas() {
    const dropdown = document.getElementById("dropdownHerramientas");
    if (herramientasSeleccionadas.length >= 2) {
        alert("Solo puedes agregar 2 herramientas.");
        return;
    }
    dropdown.classList.toggle("hidden");
    cargarHerramientas(1);
}

function cargarHerramientas(pagina = 1) {
    const buscar = document.getElementById("buscarHerramienta").value || "";
    fetch(`buscar_herramientas_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorHerramientas").innerHTML = html;
        })
        .catch(error => {
            console.error("Error al cargar herramientas:", error);
        });
}

function agregarHerramientaDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMinimo, stockMaximo, marca, modelo, tipo) {
    if (herramientasSeleccionadas.length >= 2) {
        alert("Límite de 2 herramientas alcanzado.");
        return;
    }

    if (herramientasSeleccionadas.some(h => h.id === id)) {
        alert("Esta herramienta ya ha sido agregada.");
        return;
    }

    const nuevaHerramienta = {
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMinimo,
        stockMaximo
    };

    herramientasSeleccionadas.push(nuevaHerramienta);
    renderizarTablaHerramientas();
    document.getElementById("dropdownHerramientas").classList.add("hidden");
}

function renderizarTablaHerramientas() {
    const cuerpo = document.getElementById("listaHerramientasAgregadas");
    const contenedor = document.getElementById("tablaHerramientasAgregadas");
    cuerpo.innerHTML = "";

    herramientasSeleccionadas.forEach((h, i) => {
        const total = h.cantidad + h.pendiente;
        cuerpo.innerHTML += `
            <tr class="border-b">
                <td class="p-2">
                    <img src="${h.imagen}" alt="${h.nombre}" class="w-12 h-12 object-cover rounded-md">
                </td>
                <td class="p-2">
                    <div class="font-semibold">${h.nombre}</div>
                    <div class="text-sm text-gray-600">${h.marca} / ${h.modelo} / ${h.tipo} / ${h.unidad}</div>
                </td>
                <td class="p-2">${h.disponible}</td>
                <td class="p-2">${h.stockMinimo}</td>
                <td class="p-2">${h.stockMaximo}</td>
                <td class="p-2">
                    <input type="number" min="1" value="${total}" 
                        onblur="actualizarCantidadHerramienta(${i}, this.value)" 
                        class="w-20 border rounded p-1 text-center shadow-sm focus:ring-2 focus:ring-blue-300">
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">Planificado: ${h.cantidad}</span>
                        ${h.pendiente > 0 ? `<br><span class="text-yellow-600">Pendiente: ${h.pendiente}</span>` : ''}
                        ${total > h.stockMaximo
                            ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo estimado (${h.stockMaximo})</span>`
                            : ''}
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verHerramienta(${h.id})" class="text-blue-600 hover:text-blue-800" title="Ver">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" onclick="quitarHerramienta(${i})" class="text-red-600 hover:text-red-800" title="Quitar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    contenedor.classList.toggle("hidden", herramientasSeleccionadas.length === 0);
    actualizarInputsOcultosHerramientas();
}

function quitarHerramienta(index) {
    herramientasSeleccionadas.splice(index, 1);
    renderizarTablaHerramientas();
}

function actualizarCantidadHerramienta(index, nuevaCantidad) {
    const h = herramientasSeleccionadas[index];
    nuevaCantidad = parseInt(nuevaCantidad);

    if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
        alert("Cantidad no válida.");
        renderizarTablaHerramientas();
        return;
    }

    if (nuevaCantidad <= h.disponible) {
        h.cantidad = nuevaCantidad;
        h.pendiente = 0;
    } else {
        const excedente = nuevaCantidad - h.disponible;

        mostrarModalPendienteHerramienta(excedente, () => {
            h.cantidad = h.disponible;
            h.pendiente = excedente;
            renderizarTablaHerramientas();
        }, () => {
            renderizarTablaHerramientas(); // Cancelado
        });
        return;
    }

    if (nuevaCantidad > h.stockMaximo) {
        mostrarAlertaHerramienta("Estás superando el stock máximo recomendado.", "warning");
    }

    renderizarTablaHerramientas();
}

function actualizarInputsOcultosHerramientas() {
    const contenedorPlanificadas = document.getElementById("inputsHerramientasPlanificadas");
    const contenedorPendientes = document.getElementById("inputsHerramientasPendientes");
    contenedorPlanificadas.innerHTML = "";
    contenedorPendientes.innerHTML = "";

    let planIndex = 0;
    let pendIndex = 0;

    herramientasSeleccionadas.forEach(h => {
        if (h.cantidad > 0) {
            contenedorPlanificadas.innerHTML += `
                <input type="hidden" name="herramientas_planificadas[${planIndex}][id]" value="${h.id}">
                <input type="hidden" name="herramientas_planificadas[${planIndex}][cantidad]" value="${h.cantidad}">
                <input type="hidden" name="herramientas_planificadas[${planIndex}][status_id]" value="25">
            `;
            planIndex++;
        }

        if (h.pendiente > 0) {
            contenedorPendientes.innerHTML += `
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][id]" value="${h.id}">
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][cantidad]" value="${h.pendiente}">
                <input type="hidden" name="herramientas_pendientes[${pendIndex}][status_id]" value="26">
            `;
            pendIndex++;
        }
    });
}

function verHerramienta(id) {
    window.open(`ver_herramienta.php?id=${id}`, '_blank');
}

function mostrarModalPendienteHerramienta(cantidadPendiente, aceptarCallback, cancelarCallback) {
    const modal = document.createElement('div');
    modal.id = "modalPendienteHerramienta";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";

    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" id="btnAceptarPendienteHerramienta" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Sí</button>
                <button type="button" id="btnCancelarPendienteHerramienta" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">No</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById("btnAceptarPendienteHerramienta").onclick = () => {
        aceptarCallback();
        cerrarModalPendienteHerramienta();
    };

    document.getElementById("btnCancelarPendienteHerramienta").onclick = () => {
        cancelarCallback();
        cerrarModalPendienteHerramienta();
    };
}

function cerrarModalPendienteHerramienta() {
    const modal = document.getElementById("modalPendienteHerramienta");
    if (modal) modal.remove();
}

function mostrarAlertaHerramienta(mensaje, tipo = "info") {
    const color = tipo === "warning"
        ? "bg-yellow-100 text-yellow-800"
        : "bg-blue-100 text-blue-800";

    const alerta = document.createElement("div");
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${color}`;
    alerta.innerText = mensaje;

    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}
</script>


<hr class="my-6">
<!-- Contenedor del buscador y tabla de productos -->
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">
            <i class="fas fa-box-open text-blue-600"></i>Seleccione los productos necesarios 
        </h2>
        <button type="button" onclick="toggleDropdownProductos()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 shadow flex items-center">
            <i class="fas fa-plus mr-2"></i> Agregar productos
        </button>
    </div>

    <!-- Dropdown de selección de productos -->
    <div id="dropdownProductos" class="border mt-3 p-4 rounded-md shadow-md bg-white hidden relative z-10">
        <div class="mb-3">
            <input type="text" id="buscarProducto" placeholder="Buscar producto..."
                class="w-full border border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500"
                onkeyup="cargarProductos(1)">
        </div>
        <div id="contenedorProductos" class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"></div>
        <div id="paginacionProductos" class="mt-3 flex justify-center gap-2"></div>
        <div class="flex justify-end mt-3">
            <button type="button" onclick="toggleDropdownProductos()"
                class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700 transition">
                <i class="fas fa-times mr-2"></i> Cerrar
            </button>
        </div>
    </div>

    <!-- Tabla de productos agregados -->
    <div id="tablaProductosAgregados" class="mt-5 hidden">
        <table class="min-w-full border border-gray-300 rounded-md shadow-md text-sm text-gray-800 bg-white">
            <thead class="bg-gray-100">
                <tr>
                     <th class="px-3 py-2">Imagen</th>
            <th class="px-3 py-2">Especificación</th>
            <th class="px-3 py-2">Cantidad</th>
            <th class="px-3 py-2">Stock Mínimo</th>
            <th class="px-3 py-2">Stock Máximo</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2">Quitar</th>
                </tr>
            </thead>
            <tbody id="listaProductosAgregados"></tbody>
        </table>
    </div>
</div>
<!-- Contenedores ocultos donde se crean los inputs para enviar al backend -->
<div id="inputsProductosPlanificados" class="hidden"></div>
<div id="inputsProductosPendientes" class="hidden"></div>
<!-- Campo oculto para enviar los productos seleccionados (JSON, opcional) -->
<input type="hidden" name="productos_seleccionados" id="productos_seleccionados">
<script>
let productosSeleccionados = [];

function toggleDropdownProductos() {
    if (productosSeleccionados.length >= 2) {
        mostrarAlerta("Solo puedes agregar 2 productos.", "warning");
        return;
    }
    document.getElementById("dropdownProductos").classList.toggle("hidden");
    cargarProductos(1);
}

function cargarProductos(pagina = 1) {
    const buscar = document.getElementById("buscarProducto").value;
    fetch(`buscar_productos_ajax.php?pagina=${pagina}&buscar=${encodeURIComponent(buscar)}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("contenedorProductos").innerHTML = html;
        });
}

function agregarProductoDesdeInventario(id, nombre, unidad, clasificacion, imagen, disponible, stockMinimo, stockMaximo, marca, modelo, tipo) {
    if (productosSeleccionados.length >= 2) {
        mostrarAlerta("Límite de 2 productos alcanzado.", "warning");
        return;
    }

    if (productosSeleccionados.some(p => p.id === id)) {
        mostrarAlerta("Este producto ya fue agregado.", "warning");
        return;
    }

    productosSeleccionados.push({
        id, nombre, unidad, clasificacion, imagen,
        marca, modelo, tipo,
        cantidad: disponible > 0 ? 1 : 0,
        pendiente: 0,
        disponible,
        stockMinimo,
        stockMaximo
    });

    renderizarTablaProductos();
    document.getElementById("dropdownProductos").classList.add("hidden");
}

function renderizarTablaProductos() {
    const tbody = document.getElementById("listaProductosAgregados");
    const contenedor = document.getElementById("tablaProductosAgregados");
    tbody.innerHTML = "";

    productosSeleccionados.forEach((p, i) => {
        const total = p.cantidad + p.pendiente;
        tbody.innerHTML += `
            <tr class="border-b">
                <td class="p-2"><img src="${p.imagen}" alt="${p.nombre}" class="w-12 h-12 object-cover rounded-md"></td>
                <td class="p-2">
                    <div class="font-semibold">${p.nombre}</div>
                    <div class="text-sm text-gray-600">${p.marca} / ${p.modelo} / ${p.tipo} / ${p.unidad}</div>
                </td>
                <td class="p-2">${p.disponible}</td>
                <td class="p-2">${p.stockMinimo}</td>
                <td class="p-2">${p.stockMaximo}</td>
                <td class="p-2">
                    <input type="number" min="1" value="${total}" 
                        onblur="actualizarCantidadProducto(${i}, this.value)" 
                        class="w-20 border rounded p-1 text-center shadow-sm focus:ring-2 focus:ring-blue-300">
                    <div class="text-xs text-gray-500 mt-1">
                        <span class="text-green-600">Planificado: ${p.cantidad}</span>
                        ${p.pendiente > 0 ? `<br><span class="text-yellow-600">Pendiente: ${p.pendiente}</span>` : ''}
                        ${total > p.stockMaximo ? `<br><span class="text-red-600 font-semibold">Supera el stock máximo (${p.stockMaximo})</span>` : ''}
                    </div>
                </td>
                <td class="p-2 flex gap-2 justify-center">
                    <button type="button" onclick="verProducto(${p.id})" class="text-blue-600 hover:text-blue-800" title="Ver"><i class="fas fa-eye"></i></button>
                    <button type="button" onclick="quitarProducto(${i})" class="text-red-600 hover:text-red-800" title="Quitar"><i class="fas fa-trash-alt"></i></button>
                </td>
            </tr>
        `;
    });

    contenedor.classList.toggle("hidden", productosSeleccionados.length === 0);
    actualizarInputsOcultosProductos();
}

function quitarProducto(index) {
    productosSeleccionados.splice(index, 1);
    renderizarTablaProductos();
}

function actualizarCantidadProducto(index, nuevaCantidad) {
    let cantidad = Math.floor(parseFloat(nuevaCantidad));
    if (isNaN(cantidad) || cantidad < 1) {
        mostrarAlerta("Cantidad no válida.", "warning");
        renderizarTablaProductos();
        return;
    }

    const p = productosSeleccionados[index];

    if (cantidad <= p.disponible) {
        p.cantidad = cantidad;
        p.pendiente = 0;
        renderizarTablaProductos();
    } else {
        const excedente = cantidad - p.disponible;
        mostrarModalPendiente(excedente, () => {
            p.cantidad = p.disponible;
            p.pendiente = excedente;
            renderizarTablaProductos();
        }, renderizarTablaProductos);
    }

    if (cantidad > p.stockMaximo) {
        mostrarAlerta("Estás superando el stock máximo recomendado.", "warning");
    }
}

function verProducto(id) {
    window.open(`ver_producto.php?id=${id}`, '_blank');
}

function mostrarModalPendiente(cantidadPendiente, aceptarCallback, cancelarCallback) {
    cerrarModalPendiente(); // Por si hay uno anterior
    const modal = document.createElement('div');
    modal.id = "modalPendiente";
    modal.className = "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
    modal.innerHTML = `
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
            <h3 class="text-lg font-bold mb-2">Inventario insuficiente</h3>
            <p>¿Deseas marcar <strong>${cantidadPendiente}</strong> como <span class="text-yellow-600 font-bold">pendiente</span>?</p>
            <div class="mt-4 flex justify-center gap-4">
                <button type="button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" id="btnAceptarPendiente">Sí</button>
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600" id="btnCancelarPendiente">No</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    document.getElementById("btnAceptarPendiente").onclick = () => {
        aceptarCallback();
        cerrarModalPendiente();
    };

    document.getElementById("btnCancelarPendiente").onclick = () => {
        cancelarCallback();
        cerrarModalPendiente();
    };
}

function cerrarModalPendiente() {
    const modal = document.getElementById("modalPendiente");
    if (modal) modal.remove();
}

function mostrarAlerta(mensaje, tipo = "info") {
    const colores = {
        warning: "bg-yellow-100 text-yellow-800",
        info: "bg-blue-100 text-blue-800"
    };
    const alerta = document.createElement('div');
    alerta.className = `fixed top-5 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow-lg z-50 ${colores[tipo] || colores.info}`;
    alerta.innerHTML = mensaje;
    document.body.appendChild(alerta);
    setTimeout(() => alerta.remove(), 4000);
}

function actualizarInputsOcultosProductos() {
    const contenedorPlanificadas = document.getElementById("inputsProductosPlanificados");
    const contenedorPendientes = document.getElementById("inputsProductosPendientes");
    contenedorPlanificadas.innerHTML = "";
    contenedorPendientes.innerHTML = "";

    let planIndex = 0, pendIndex = 0;

    productosSeleccionados.forEach(p => {
        if (p.cantidad > 0) {
            contenedorPlanificadas.innerHTML += `
                <input type="hidden" name="productos_planificados[${planIndex}][id]" value="${p.id}">
                <input type="hidden" name="productos_planificados[${planIndex}][cantidad]" value="${p.cantidad}">
                <input type="hidden" name="productos_planificados[${planIndex}][status_id]" value="25">
            `;
            planIndex++;
        }

        if (p.pendiente > 0) {
            contenedorPendientes.innerHTML += `
                <input type="hidden" name="productos_pendientes[${pendIndex}][id]" value="${p.id}">
                <input type="hidden" name="productos_pendientes[${pendIndex}][cantidad]" value="${p.pendiente}">
                <input type="hidden" name="productos_pendientes[${pendIndex}][status_id]" value="26">
            `;
            pendIndex++;
        }
    });
}

function prepararEnvioProductos() {
    document.getElementById("productos_seleccionados").value = JSON.stringify(productosSeleccionados);
    actualizarInputsOcultosProductos();
    return true;
}
</script>

<div class="flex justify-end mt-8 gap-4">
    <button type="submit" onclick="return prepararEnvioProductos();" name="accion" value="guardar" class="bg-green-600 text-white px-6 py-2 rounded-md shadow hover:bg-green-700 flex items-center gap-2">
        <i class="fas fa-save"></i> Guardar
    </button>
    <button type="button" onclick="window.close();" class="bg-gray-500 text-white px-6 py-2 rounded-md shadow hover:bg-gray-600 flex items-center gap-2">
        <i class="fas fa-times"></i> Cerrar
    </button>
</div>
    </form>
