(() => {
  const $ = s => document.querySelector(s);

  const modal   = $('#modalCliente');
  const titulo  = $('#modalTituloCli');
  const form    = $('#formCliente');
  const btnNew  = $('#btnNuevoCliente');
  const btnCan  = $('#btnCancelarCli');

  const modalC  = $('#modalConfirmCli');
  const btnNo   = $('#btnNoCli');
  const btnSi   = $('#btnSiCli');

  const inputQ  = $('#buscarCliente');
  const toast   = $('#toast');

  let modo       = 'crear';
  let idEliminar = null;

  function toastMsg(msg, type='success'){
    if (!toast) return;
    toast.textContent   = msg;
    toast.className     = 'toast ' + (type === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => (toast.style.display = 'none'), 2200);
  }

  function abrirNuevo(){
    modo = 'crear';
    titulo.textContent = 'Nuevo cliente';
    form.reset();
    $('#IdCliente').value = '';
    modal.setAttribute('aria-hidden','false');
  }
  function abrirEditar(c){
    modo = 'editar';
    titulo.textContent = 'Editar cliente';
    form.reset();

    $('#IdCliente').value       = c.IdCliente;
    $('#Nombre').value          = c.Nombre       || '';
    $('#Apellido').value        = c.Apellido     || '';
    $('#IdDepartamento').value  = c.IdDepartamento;
    $('#Direccion').value       = c.Direccion    || '';
    $('#Telefono').value        = c.Telefono     || '';
    $('#TipoCliente').value     = c.TipoCliente  || '';

    modal.setAttribute('aria-hidden','false');
  }
  function cerrarModal(){
    modal.setAttribute('aria-hidden','true');
  }

  function abrirConfirm(id){
    idEliminar = id;
    modalC.setAttribute('aria-hidden','false');
  }
  function cerrarConfirm(){
    idEliminar = null;
    modalC.setAttribute('aria-hidden','true');
  }

  async function cargarTabla(){
    const tbody = document.querySelector('#tablaClientes tbody');
    tbody.innerHTML = `<tr><td colspan="9">Cargando...</td></tr>`;
    try {
      const r    = await fetch('modulos/clientes/listar.php', {credentials:'same-origin'});
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="9">No hay clientes registrados</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      for (const c of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${c.IdCliente}</td>
          <td>${c.Nombre || ''}</td>
          <td>${c.Apellido || ''}</td>
          <td>${c.Departamento || ''}</td>
          <td>${c.Direccion || ''}</td>
          <td>${c.Telefono || ''}</td>
          <td>${c.TipoCliente || ''}</td>
          <td style="text-align:center;">
            <button class="btn-icon btn-edit" title="Editar">
              <img src="img/editar.png" alt="Editar">
            </button>
          </td>
          <td style="text-align:center;">
            <button class="btn-icon btn-del" title="Eliminar">
              <img src="img/eliminar.png" alt="Eliminar">
            </button>
          </td>
        `;
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(c));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(c.IdCliente));
        tbody.appendChild(tr);
      }

      if (inputQ && inputQ.value.trim() !== '') {
        aplicarFiltro(inputQ.value);
      }
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="9">Error al cargar los datos</td></tr>`;
    }
  }

  async function guardar(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/clientes/crear.php'
      : 'modulos/clientes/actualizar.php';

    try {
      const r   = await fetch(url, {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      if (res.ok){
        cerrarModal();
        await cargarTabla();
        toastMsg(modo === 'crear' ? 'Se agregó con éxito' : 'Se editó con éxito');
      } else {
        toastMsg(res.msg || 'No se pudo guardar', 'error');
      }
    } catch (e) {
      console.error(e);
      toastMsg('Error de red', 'error');
    }
  }

  async function eliminarCli(){
    if (!idEliminar) return cerrarConfirm();
    try {
      const fd = new FormData();
      fd.append('IdCliente', idEliminar);

      const r   = await fetch('modulos/clientes/eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      cerrarConfirm();
      if (res.ok){
        await cargarTabla();
        toastMsg('Se eliminó con éxito');
      } else {
        toastMsg(res.msg || 'No se pudo eliminar', 'error');
      }
    } catch (e) {
      console.error(e);
      toastMsg('Error de red', 'error');
    }
  }

  function aplicarFiltro(q){
    q = (q || '').toLowerCase();
    document.querySelectorAll('#tablaClientes tbody tr').forEach(tr => {
      const nombre   = (tr.children[1]?.textContent || '').toLowerCase();
      const apellido = (tr.children[2]?.textContent || '').toLowerCase();
      const tel      = (tr.children[5]?.textContent || '').toLowerCase();
      const match    = nombre.includes(q) || apellido.includes(q) || tel.includes(q);
      tr.style.display = match ? '' : 'none';
    });
  }

  // Eventos
  btnNew   && btnNew.addEventListener('click', abrirNuevo);
  btnCan   && btnCan.addEventListener('click', cerrarModal);
  form     && form.addEventListener('submit', guardar);
  btnNo    && btnNo.addEventListener('click', cerrarConfirm);
  btnSi    && btnSi.addEventListener('click', eliminarCli);
  inputQ   && inputQ.addEventListener('input', () => aplicarFiltro(inputQ.value));

  // Carga inicial
  cargarTabla();
})();
