// js/cargos.js
(() => {
  const $ = s => document.querySelector(s);

  const modal   = $('#modalCargo');
  const titulo  = $('#modalTituloCargo');
  const form    = $('#formCargo');
  const btnNew  = $('#btnNuevoCargo');
  const btnCan  = $('#btnCancelarCargo');

  const modalC  = $('#modalConfirmCargo');
  const btnNo   = $('#btnNoCargo');
  const btnSi   = $('#btnSiCargo');

  const inputQ  = $('#buscarCargo');
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
    titulo.textContent = 'Nuevo cargo';
    form.reset();
    $('#IdCargo').value = '';
    modal.setAttribute('aria-hidden','false');
  }
  function abrirEditar(row){
    modo = 'editar';
    titulo.textContent = 'Editar cargo';
    form.reset();
    $('#IdCargo').value   = row.IdCargo;
    $('#NombreCargo').value = row.Nombre || '';
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
    const tbody = document.querySelector('#tablaCargos tbody');
    tbody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;
    try {
      const r    = await fetch('modulos/cargos/listar.php', {credentials:'same-origin'});
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="4">No hay cargos registrados</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      for (const c of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${c.IdCargo}</td>
          <td>${c.Nombre || ''}</td>
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
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(c.IdCargo));
        tbody.appendChild(tr);
      }

      if (inputQ && inputQ.value.trim() !== '') {
        aplicarFiltro(inputQ.value);
      }
    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="4">Error al cargar los datos</td></tr>`;
    }
  }

  async function guardar(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/cargos/crear.php'
      : 'modulos/cargos/actualizar.php';

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

  async function eliminarCargo(){
    if (!idEliminar) return cerrarConfirm();
    try {
      const fd = new FormData();
      fd.append('IdCargo', idEliminar);

      const r   = await fetch('modulos/cargos/eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
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
      toastMsg('Hay uno o mas empleados con este cargo', 'error');
    }
  }

  function aplicarFiltro(q){
    q = (q || '').toLowerCase();
    document.querySelectorAll('#tablaCargos tbody tr').forEach(tr => {
      const nombre = (tr.children[1]?.textContent || '').toLowerCase();
      tr.style.display = nombre.includes(q) ? '' : 'none';
    });
  }

 
  btnNew   && btnNew.addEventListener('click', abrirNuevo);
  btnCan   && btnCan.addEventListener('click', cerrarModal);
  form     && form.addEventListener('submit', guardar);
  btnNo    && btnNo.addEventListener('click', cerrarConfirm);
  btnSi    && btnSi.addEventListener('click', eliminarCargo);
  inputQ   && inputQ.addEventListener('input', () => aplicarFiltro(inputQ.value));

 
  cargarTabla();
})();
