(() => {
  const $ = (s) => document.querySelector(s);

  const modal        = $('#modalProveedor');
  const form         = $('#formProveedor');
  const modalTitulo  = $('#modalTituloProv');
  const btnNuevo     = $('#btnNuevoProveedor');
  const btnCancelar  = $('#btnCancelarProv');

  const modalConfirm = $('#modalConfirmProv');
  const btnNo        = $('#btnNoProv');
  const btnSi        = $('#btnSiProv');

  const inputBuscar  = $('#buscarProveedor');
  const toast        = $('#toast');

  let modo = 'crear';         
  let idAEliminar = null;


  function showToast(msg, type='success'){
    if (!toast) return;
    toast.textContent = msg;
    toast.className = 'toast ' + (type === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => (toast.style.display = 'none'), 2200);
  }

  function abrirModalCrear(){
    modo = 'crear';
    modalTitulo.textContent = 'Nuevo proveedor';
    form.reset();
    $('#IdProveedor').value = '';
    modal.setAttribute('aria-hidden', 'false');
  }
  function abrirModalEditar(p){
    modo = 'editar';
    modalTitulo.textContent = 'Editar proveedor';
    form.reset();

    $('#IdProveedor').value = p.IdProveedor;
    $('#Nombre').value      = p.Nombre ?? '';
    $('#Telefono').value    = p.Telefono ?? '';
    $('#Email').value       = p.Email ?? '';
    $('#Direccion').value   = p.Direccion ?? '';

    modal.setAttribute('aria-hidden', 'false');
  }
  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }

  function abrirConfirm(id){
    idAEliminar = id;
    modalConfirm.setAttribute('aria-hidden','false');
  }
  function cerrarConfirm(){
    idAEliminar = null;
    modalConfirm.setAttribute('aria-hidden','true');
  }

  // TABLA
  async function cargarTabla(){
    const tbody = document.querySelector('#tablaProveedores tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8">Cargando...</td></tr>`;
    try{
      const r = await fetch('modulos/proveedores/listar.php', { credentials:'same-origin' });
      const data = await r.json();

      if (!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="8">No hay proveedores registrados</td></tr>`;
        return;
      }

      //PARA EL NUMERO DE TELEFONO
      function formatearTelefono(tel) {
  if (!tel) return '';
  let num = tel.replace(/\D/g, ''); 
  if (num.length === 8) {
    return `${num.slice(0,4)}-${num.slice(4)}`;
  } else if (num.length === 12 && num.startsWith('505')) {

    return `+${num.slice(0,3)} ${num.slice(3,7)}-${num.slice(7)}`;
  } else {
    return tel;
  }
}



      tbody.innerHTML = '';
      for (const p of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${p.IdProveedor}</td>
          <td>${p.Nombre ?? ''}</td>
          <td>${formatearTelefono(p.Telefono)}</td>
          <td>${p.Email ?? ''}</td>
          <td>${p.Direccion ?? ''}</td>
          <td>${p.FechaRegistro ?? ''}</td>
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

        tr.querySelector('.btn-edit').addEventListener('click', () => abrirModalEditar(p));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(p.IdProveedor));

        tbody.appendChild(tr);
      }

      if (inputBuscar && inputBuscar.value.trim() !== '') {
        aplicarFiltro(inputBuscar.value.trim());
      }
    }catch(err){
      console.error(err);
      tbody.innerHTML = `<tr><td colspan="8">Error al cargar los datos</td></tr>`;
    }
  }

  //CREAR Y EDITAR
  async function guardarProveedor(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/proveedores/crear.php'
      : 'modulos/proveedores/actualizar.php';

    try{
      const r   = await fetch(url, { method:'POST', body: fd, credentials:'same-origin' });
      const res = await r.json();
      if (res.ok){
        cerrarModal();
        await cargarTabla();
        showToast(modo === 'crear' ? 'Se agregó con éxito' : 'Se editó con éxito', 'success');
      }else{
        showToast(res.msg || 'No se pudo guardar', 'error');
      }
    }catch(err){
      console.error(err);
      showToast('Error de red', 'error');
    }
  }

  // -ELIMINAR
  async function eliminarProveedor(){
    if (!idAEliminar) return cerrarConfirm();

    try{
      const fd = new FormData();
      fd.append('IdProveedor', idAEliminar);

      const r   = await fetch('modulos/proveedores/eliminar.php', { method:'POST', body: fd, credentials:'same-origin' });
      const res = await r.json();

      cerrarConfirm();

      if (res.ok){
        await cargarTabla();
        showToast('Se eliminó con éxito', 'success');
      }else{
        showToast(res.msg || 'No se pudo eliminar', 'error');
      }
    }catch(err){
      console.error(err);
      showToast('Se ha hecho una o mas compras con este proveedor', 'error');
    }
  }

  // BUSCAR
  function aplicarFiltro(q){
    q = (q || '').toLowerCase();
    document.querySelectorAll('#tablaProveedores tbody tr').forEach(tr => {
      const celdas = [1,2,3].map(i => (tr.children[i]?.textContent || '').toLowerCase());
      const hit = celdas.some(txt => txt.includes(q));
      tr.style.display = hit ? '' : 'none';
    });
  }

  const emailInput = document.getElementById("Email");

emailInput.addEventListener("input", function () {
    let v = emailInput.value;

    if (v.endsWith("@gmail.com")) return;
    v = v.replace(/@.*/, "");
    emailInput.value = v + "@gmail.com";
});



  // EVENTOS
  btnNuevo   && btnNuevo.addEventListener('click', abrirModalCrear);
  btnCancelar&& btnCancelar.addEventListener('click', cerrarModal);
  form       && form.addEventListener('submit', guardarProveedor);

  btnNo      && btnNo.addEventListener('click', cerrarConfirm);
  btnSi      && btnSi.addEventListener('click', eliminarProveedor);

  inputBuscar&& inputBuscar.addEventListener('input', () => aplicarFiltro(inputBuscar.value));


  cargarTabla();
})();
