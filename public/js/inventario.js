
   // Mostrar/ocultar el formulario de filtros
function toggleFilterForm() {
    const form = document.getElementById('filterForm');
    form.classList.toggle('hidden');
}

// Función para aplicar filtros
function applyFilters() {
    const nombre = document.getElementById('nombre').value;
    const minimo = document.getElementById('minimo').value;
    const almacen = document.getElementById('almacen').value;


    // Redirigir a la misma página con los filtros aplicados
    window.location.href = `?nombre=${encodeURIComponent(nombre)}&minimo=${minimo}&almacen=${almacen}`;
}

// Función para filtrar la tabla en tiempo real
function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('dataTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let rowContainsFilter = false;

        for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
                const cellValue = cells[j].textContent || cells[j].innerText;
                if (cellValue.toLowerCase().indexOf(filter) > -1) {
                    rowContainsFilter = true;
                    break;
                }
            }
        }

        if (rowContainsFilter) {
            rows[i].style.display = ""; // Mostrar la fila
        } else {
            rows[i].style.display = "none"; // Ocultar la fila
        }
    }
}


