(() => {
  const $ = s => document.querySelector(s);

  const modal   = $('#modalProducto');
  const titulo  = $('#modalTituloProd');
  const form    = $('#formProducto');
  const btnNew  = $('#btnNuevoProducto');
  const btnCan  = $('#btnCancelarProd');

  const modalC  = $('#modalConfirmProd');
  const btnNo   = $('#btnNoProd');
  const btnSi   = $('#btnSiProd');

  const filtroCategoria = $('#filtroCategoria');
  const inputQ          = $('#buscarProducto');
  const toast           = $('#toast');

  let modo       = 'crear';
  let idEliminar = null;
  let productos  = []; 

  function toastMsg(msg, type='success'){
    if (!toast) return;
    toast.textContent   = msg;
    toast.className     = 'toast ' + (type === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => (toast.style.display = 'none'), 2200);
  }

  function abrirNuevo(){
    modo = 'crear';
    titulo.textContent = 'Nuevo producto';
    form.reset();
    $('#IdProducto').value = '';
    const sel = $('#IdCategoria');
    if (sel) sel.selectedIndex = 0;
    modal.setAttribute('aria-hidden','false');
  }

  function abrirEditar(p){
    modo = 'editar';
    titulo.textContent = 'Editar producto';
    form.reset();

    $('#IdProducto').value       = p.IdProducto;
    $('#IdCategoria').value      = p.IdCategoria;
    $('#Marca').value            = p.Marca || '';
    $('#Nombre').value           = p.Nombre || '';
    $('#Descripcion').value      = p.Descripcion || '';
    $('#Talla').value            = p.Talla || '';
    $('#Color').value            = p.Color || '';
    $('#Cantidad').value         = p.Cantidad || 0;
    $('#Precio_de_Venta').value  = p.Precio_de_Venta || 0;

    modal.setAttribute('aria-hidden','false');
  }

  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }
  function abrirConfirm(id){ idEliminar = id; modalC.setAttribute('aria-hidden','false'); }
  function cerrarConfirm(){ idEliminar = null; modalC.setAttribute('aria-hidden','true'); }

  function renderTabla(){
    const tbody = document.querySelector('#tablaProductos tbody');
    if (!tbody) return;

    const catSel = filtroCategoria ? filtroCategoria.value : '';
    const q      = (inputQ?.value || '').toLowerCase();

    if (!productos.length){
      tbody.innerHTML = `<tr><td colspan="11">No hay productos registrados</td></tr>`;
      return;
    }

    tbody.innerHTML = '';
    let count = 0;

    for (const p of productos){
      if (catSel && String(p.IdCategoria) !== String(catSel)) continue;

      const texto = [
        p.Nombre,
        p.Marca,
        p.Color,
        p.Talla,
        p.Descripcion
      ].join(' ').toLowerCase();

      if (q && !texto.includes(q)) continue;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p.IdProducto}</td>
        <td>${p.Categoria || ''}</td>
        <td>${p.Marca || ''}</td>
        <td>${p.Nombre || ''}</td>
        <td>${p.Descripcion || ''}</td>
        <td>${p.Talla || ''}</td>
        <td>${p.Color || ''}</td>
        <td>${p.Cantidad || 0}</td>
        <td>C$ ${Number(p.Precio_de_Venta).toFixed(2)}</td>
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
      tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(p));
      tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(p.IdProducto));
      tbody.appendChild(tr);
      count++;
    }

    if (!count){
      tbody.innerHTML = `<tr><td colspan="11">No se encontraron productos con ese filtro</td></tr>`;
    }
  }

  async function cargarTabla(){
    const tbody = document.querySelector('#tablaProductos tbody');
    if (tbody) tbody.innerHTML = `<tr><td colspan="11">Cargando...</td></tr>`;
    try {
      const r    = await fetch('modulos/productos/listar.php', {credentials:'same-origin'});
      const data = await r.json();
      productos  = Array.isArray(data) ? data : [];
      renderTabla();
    } catch (e) {
      console.error(e);
      if (tbody) tbody.innerHTML = `<tr><td colspan="11">Error al cargar los datos</td></tr>`;
    }
  }

  async function guardar(ev){
    ev.preventDefault();
    const fd  = new FormData(form);
    const url = (modo === 'crear')
      ? 'modulos/productos/crear.php'
      : 'modulos/productos/actualizar.php';

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

  async function eliminarProd(){
    if (!idEliminar) return cerrarConfirm();
    try {
      const fd = new FormData();
      fd.append('IdProducto', idEliminar);

      const r   = await fetch('modulos/productos/eliminar.php', {
        method:'POST', body: fd, credentials:'same-origin'
      });
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

  
  btnNew  && btnNew.addEventListener('click', abrirNuevo);
  btnCan  && btnCan.addEventListener('click', cerrarModal);
  form    && form.addEventListener('submit', guardar);
  btnNo   && btnNo.addEventListener('click', cerrarConfirm);
  btnSi   && btnSi.addEventListener('click', eliminarProd);
  filtroCategoria && filtroCategoria.addEventListener('change', renderTabla);
  inputQ          && inputQ.addEventListener('input', renderTabla);


  cargarTabla();
})();
