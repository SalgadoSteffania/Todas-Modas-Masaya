(() => {
  const $ = s => document.querySelector(s);

  const modal   = $('#modalCategoria');
  const titulo  = $('#modalTituloCat');
  const form    = $('#formCategoria');
  const btnNew  = $('#btnNuevaCategoria');
  const btnCan  = $('#btnCancelarCat');

  const modalC  = $('#modalConfirmCat');
  const btnNo   = $('#btnNoCat');
  const btnSi   = $('#btnSiCat');

  const inputQ  = $('#buscarCategoria');
  const toast   = $('#toast');

  let modo = 'crear';
  let idEliminar = null;

  function toastMsg(msg, type='success'){
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success'?'success':'error');
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 2200);
  }

  function abrirNuevo(){
    modo = 'crear';
    titulo.textContent = 'Nueva categoría';
    form.reset();
    $('#IdCategoria').value = '';
    modal.setAttribute('aria-hidden','false');
  }
  function abrirEditar(row){
    modo = 'editar';
    titulo.textContent = 'Editar categoría';
    form.reset();
    $('#IdCategoria').value = row.IdCategoria;
    $('#Descripcion').value = row.Descripcion || '';
    modal.setAttribute('aria-hidden','false');
  }
  function cerrarModal(){ modal.setAttribute('aria-hidden','true'); }

  function abrirConfirm(id){ idEliminar = id; modalC.setAttribute('aria-hidden','false'); }
  function cerrarConfirm(){ idEliminar = null; modalC.setAttribute('aria-hidden','true'); }

  //Cargar tabla
  async function cargarTabla(){
    const tbody = document.querySelector('#tablaCategorias tbody');
    tbody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;
    try{
      const r = await fetch('modulos/categorias/listar.php', {credentials:'same-origin'});
      const data = await r.json();

      if(!Array.isArray(data) || !data.length){
        tbody.innerHTML = `<tr><td colspan="4">No hay categorías registradas</td></tr>`;
        return;
      }

      tbody.innerHTML = '';
      for(const c of data){
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${c.IdCategoria}</td>
          <td>${c.Descripcion || ''}</td>
          <td style="text-align:center;">
            <button class="btn-icon btn-edit" title="Editar">
              <img src="img/editar.png" alt="Editar">
            </button>
          </td>
          <td style="text-align:center;">
            <button class="btn-icon btn-del" title="Eliminar">
              <img src="img/eliminar.png" alt="Eliminar">
            </button>
          </td>`;
        tr.querySelector('.btn-edit').addEventListener('click', () => abrirEditar(c));
        tr.querySelector('.btn-del').addEventListener('click', () => abrirConfirm(c.IdCategoria));
        tbody.appendChild(tr);
      }

      if (inputQ && inputQ.value.trim()!=='') aplicarFiltro(inputQ.value);
    }catch(e){
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="4">Error al cargar los datos</td></tr>`;
    }
  }

  //Crear y actualizar
  async function guardar(ev){
    ev.preventDefault();
    const fd = new FormData(form);
    const url = (modo==='crear')
      ? 'modulos/categorias/crear.php'
      : 'modulos/categorias/actualizar.php';

    try{
      const r = await fetch(url, {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      if(res.ok){
        cerrarModal();
        await cargarTabla();
        toastMsg(modo==='crear' ? 'Se agregó con éxito' : 'Se editó con éxito');
      }else{
        toastMsg(res.msg || 'No se pudo guardar', 'error');
      }
    }catch(e){
      console.error(e);
      toastMsg('Error de red', 'error');
    }
  }

  //Eliminar
  async function eliminarCat(){
    if(!idEliminar) return cerrarConfirm();
    try{
      const fd = new FormData();
      fd.append('IdCategoria', idEliminar);
      const r = await fetch('modulos/categorias/eliminar.php', {method:'POST', body: fd, credentials:'same-origin'});
      const res = await r.json();
      cerrarConfirm();
      if(res.ok){ await cargarTabla(); toastMsg('Se eliminó con éxito'); }
      else{ toastMsg(res.msg || 'No se pudo eliminar', 'error'); }
    }catch(e){
      console.error(e);
      toastMsg('Uno o mas  productos tienen esta categoria', 'error');
    }
  }

  function aplicarFiltro(q){
    q = (q||'').toLowerCase();
    document.querySelectorAll('#tablaCategorias tbody tr').forEach(tr=>{
      const desc = (tr.children[1]?.textContent || '').toLowerCase();
      tr.style.display = desc.includes(q) ? '' : 'none';
    });
  }

  // Eventos
  btnNew?.addEventListener('click', abrirNuevo);
  btnCan?.addEventListener('click', cerrarModal);
  form?.addEventListener('submit', guardar);
  btnNo?.addEventListener('click', cerrarConfirm);
  btnSi?.addEventListener('click', eliminarCat);
  inputQ?.addEventListener('input', () => aplicarFiltro(inputQ.value));


  cargarTabla();
})();
