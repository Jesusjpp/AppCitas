// JS Resumido (Datos en memoria para testing PHP)
let pacientes = [{ id: 1, nombre: 'Juan Pérez', documento: '12345678', telefono: '555-1234', correo: 'juan@email.com' }];
let citas = [{ id: 1, paciente: 'Juan Pérez', fecha: '2023-10-15', hora: '10:00', odontologo: 'Dr. López', estado: 'Pendiente' }];

// Navegación tabs (alternar secciones)
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.seccion').forEach(s => s.classList.add('hidden'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab + '-seccion').classList.remove('hidden');
    });
});

// Funciones auxiliares resumidas
function poblarPacientes() {
    const select = document.getElementById('paciente-cita');
    select.innerHTML = '<option value="">Seleccione un paciente</option>';
    pacientes.forEach(p => {
        const option = document.createElement('option');
        option.value = p.id;
        option.textContent = p.nombre;
        select.appendChild(option);
    });
}

function actualizarTablaPacientes() {
    const tbody = document.querySelector('#tabla-pacientes tbody');
    tbody.innerHTML = '';
    pacientes.forEach(p => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${p.id}</td><td>${p.nombre}</td><td>${p.documento}</td><td>${p.telefono}</td><td>${p.correo}</td><td><button onclick="editarRegistro('paciente', ${p.id})">Editar</button> <button onclick="eliminarRegistro('paciente', ${p.id})">Eliminar</button></td>`;
        tbody.appendChild(tr);
    });
}

function actualizarTablaCitas() {
    const tbody = document.querySelector('#tabla-citas tbody');
    tbody.innerHTML = '';
    citas.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${c.id}</td><td>${c.paciente}</td><td>${c.fecha}</td><td>${c.hora}</td><td>${c.odontologo}</td><td>${c.estado}</td><td><button onclick="editarRegistro('cita', ${c.id})">Editar</button> <button onclick="cancelarRegistro('cita', ${c.id})">Cancelar</button></td>`;
        tbody.appendChild(tr);
    });
}

// Agregar Paciente
document.getElementById('btn-agregar-paciente').addEventListener('click', () => {
    const inputs = ['nombre-paciente', 'documento-paciente', 'telefono-paciente', 'correo-paciente'];
    const valores = inputs.map(id => document.getElementById(id).value);
    if (valores.every(v => v)) {
        const nuevo = { id: Date.now(), nombre: valores[0], documento: valores[1], telefono: valores[2], correo: valores[3] };
        pacientes.push(nuevo);
        actualizarTablaPacientes();
        poblarPacientes();
        alert('Paciente agregado');
        inputs.forEach(id => document.getElementById(id).value = '');
    } else {
        alert('Complete todos los campos');
    }
});

// Agregar Cita
document.getElementById('btn-agregar-cita').addEventListener('click', () => {
    const inputs = ['paciente-cita', 'fecha-cita', 'hora-cita', 'odontologo-cita'];
    const valores = inputs.map(id => document.getElementById(id).value);
    if (valores.every(v => v)) {
        const pacienteNombre = pacientes.find(p => p.id == valores[0])?.nombre || 'Desconocido';
        const nueva = { id: Date.now(), paciente: pacienteNombre, fecha: valores[1], hora: valores[2], odontologo: valores[3], estado: 'Pendiente' };
        citas.push(nueva);
        actualizarTablaCitas();
        alert('Cita agendada');
        inputs.forEach(id => document.getElementById(id).value = '');
    } else {
        alert('Complete todos los campos');
    }
});

// Funciones de edición/cancelar unificadas
function editarRegistro(tipo, id) {
    if (tipo === 'paciente') {
        const p = pacientes.find(p => p.id === id);
        document.getElementById('modal-titulo').textContent = 'Editar Paciente';
        document.getElementById('formulario-edicion').innerHTML = `
            <input type="text" value="${p.nombre}" id="edit-nombre" /><input type="text" value="${p.documento}" id="edit-documento" />
            <input type="tel" value="${p.telefono}" id="edit-telefono" /><input type="email" value="${p.correo}" id="edit-correo" />
            <button onclick="guardarEdicion('paciente', ${id})">Guardar</button>
        `;
    } else {
        const c = citas.find(c => c.id === id);
        document.getElementById('modal-titulo').textContent = 'Editar Cita';
        document.getElementById('formulario-edicion').innerHTML = `
            <input type="date" value="${c.fecha}" id="edit-fecha" /><input type="time" value="${c.hora}" id="edit-hora" />
            <input type="text" value="${c.odontologo}" id="edit-odontologo" />
            <select id="edit-estado"><option value="Pendiente" ${c.estado === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
            <option value="Confirmada" ${c.estado === 'Confirmada' ? 'selected' : ''}>Confirmada</option>
            <option value="Cancelada" ${c.estado === 'Cancelada' ? 'selected' : ''}>Cancelada</option></select>
            <button onclick="guardarEdicion('cita', ${id})">Guardar</button>
        `;
    }
    document.getElementById('modal-edicion').style.display = 'block';
}

function guardarEdicion(tipo, id) {
    if (tipo === 'paciente') {
        const campos = ['nombre', 'documento', 'telefono', 'correo'];
        const valores = campos.map(c => document.getElementById(`edit-${c}`).value);
        const index = pacientes.findIndex(p => p.id === id);
        if (index !== -1) {
            pacientes[index] = { ...pacientes[index], ...Object.fromEntries(campos.map((c, i) => [c, valores[i]])) };
            actualizarTablaPacientes();
            poblarPacientes();
        }
    } else {
        const campos = ['fecha', 'hora', 'odontologo', 'estado'];
        const valores = campos.map(c => document.getElementById(`edit-${c}`).value);
        const index = citas.findIndex(c => c.id === id);
        if (index !== -1) {
            citas[index] = { ...citas[index], ...Object.fromEntries(campos.map((c, i) => [c, valores[i]])) };
            actualizarTablaCitas();
        }
    }
    cerrarModal();
    alert(`${tipo} actualizado`);
}

function eliminarRegistro(tipo, id) {
    if (confirm(`¿Eliminar este ${tipo}?`)) {
        if (tipo === 'paciente') {
            pacientes = pacientes.filter(p => p.id !== id);
            actualizarTablaPacientes();
            poblarPacientes();
        } else {
            citas = citas.filter(c => c.id !== id);
            actualizarTablaCitas();
        }
    }
}

function cancelarRegistro(tipo, id) {
    if (confirm('¿Cancelar esta cita?')) {
        const index = citas.findIndex(c => c.id === id);
        if (index !== -1) {
            citas[index].estado = 'Cancelada';
            actualizarTablaCitas();
        }
    }
}

function cerrarModal() {
    document.getElementById('modal-edicion').style.display = 'none';
}

// Inicializar
poblarPacientes();
actualizarTablaPacientes();
actualizarTablaCitas();