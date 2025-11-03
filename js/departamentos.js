(() => {
  const $ = s => document.querySelector(s);

  const modal   = $('#modalDepartamento');
  const titulo  = $('#modalTituloDepto');
  const form    = $('#formDepartamento');
  const btnNew  = $('#btnNuevoDepartamento');
  const btnCan  = $('#btnCancelarDepto');

  const modalC  = $('#modalConfirmDepto');
  const btnNo   = $('#btnNoDepto');
  const btnSi   = $('#btnSiDepto');

  const inputQ  = $('#buscarDepartamento');
  const toast   = $('#toast');

  let modo       = 'crear';
  let idEliminar = null;

  function toastMsg(msg, type='success'){
    toast.textContent = msg;
    toast.className   = 'toast ' + (type === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => (toast.style.display = 'none'), 2200);
  }

  function abrirNuevo(){
    modo = 'crear';
    titulo.textContent = 'Nuevo departamento';
    form.reset();
    $('#IdDepartamento').value = '';
    modal.setAttribute('aria-hidden', 'false');
  }
  function abrirEditar(row){
    modo = 'editar';
    titulo.textContent = 'Editar departamento';
    form.reset();
    $('#IdDepartamento').value = row.IdDepartamento;
    $('#Nombre').value         = row.Nombre || '';
    modal.setAttribute('aria-hidden', 'false');
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
    const tbody = document.querySelector('#tablaDepartamentos tbody');
    tbody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;
    try {
      const r    = await fetch('modulos/departamentos/listar.php', {credentials:'same-origin'});
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="4">No hay departamentos registrados</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      for (const d of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.IdDepartamento}</td>
          <td>${d.Nombre || ''}</td>
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
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(d));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(d.IdDepartamento));
        tbody.appendChild(tr);
      }

      if (inputQ && inputQ.value.trim() !== '') {
        aplicarFiltro(inputQ.value);
      }
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="4">No hay departamentos registrados</td></tr>`;
    }
  }

  async function guardar(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/departamentos/crear.php'
      : 'modulos/departamentos/actualizar.php';

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

  async function eliminarDepto(){
    if (!idEliminar) return cerrarConfirm();
    try {
      const fd = new FormData();
      fd.append('IdDepartamento', idEliminar);

      const r   = await fetch('modulos/departamentos/eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
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
    document.querySelectorAll('#tablaDepartamentos tbody tr').forEach(tr => {
      const nombre = (tr.children[1]?.textContent || '').toLowerCase();
      tr.style.display = nombre.includes(q) ? '' : 'none';
    });
  }

  // Eventos
  btnNew   && btnNew.addEventListener('click', abrirNuevo);
  btnCan   && btnCan.addEventListener('click', cerrarModal);
  form     && form.addEventListener('submit', guardar);
  btnNo    && btnNo.addEventListener('click', cerrarConfirm);
  btnSi    && btnSi.addEventListener('click', eliminarDepto);
  inputQ   && inputQ.addEventListener('input', () => aplicarFiltro(inputQ.value));


  cargarTabla();
})();
