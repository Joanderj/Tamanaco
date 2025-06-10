document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('formGuardarTarea');

    form.onsubmit = function () {
        // Prepare data for products, spare parts, and tools
        prepararEnvioProductos();
        prepararEnvioInsumos();
        actualizarInputsOcultosRepuestos();
        return true;  // Allow form submission
    };

    function prepararEnvioProductos() {
        const inputHidden = document.getElementById("productos_seleccionados");
        inputHidden.value = JSON.stringify(productosSeleccionados);
    }

    function prepararEnvioInsumos() {
        const tareaInsumos = {
            productos: window.productosSeleccionados || [],
            repuestos: window.repuestosSeleccionados || [],
            herramientas: window.herramientasSeleccionadas || []
        };
        document.getElementById('tarea_insumos').value = JSON.stringify(tareaInsumos);
    }

    function actualizarInputsOcultosRepuestos() {
        const contenedor = document.getElementById("inputsRepuestosOcultos");
        contenedor.innerHTML = "";

        repuestosSeleccionados.forEach((r, i) => {
            contenedor.innerHTML += `
                <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
                <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cantidad}">
                <input type="hidden" name="repuestos[${i}][pendiente]" value="${r.pendiente}">
            `;
        });
    }
});