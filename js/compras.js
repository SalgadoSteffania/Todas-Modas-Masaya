(() => {
  const $ = s => document.querySelector(s);

  const tablaBody   = $('#tablaCompras tbody');
  const inputBuscar = $('#buscarCompra');
  const toast       = $('#toast');

  const modalCompra   = $('#modalCompra');
  const tituloCompra  = $('#modalTituloCompra');
  const formCompra    = $('#formCompra');
  const btnNueva      = $('#btnNuevaCompra');
  const btnCancelar   = $('#btnCancelarCompra');
  const btnAgregarFila= $('#btnAgregarFila');
  const tbodyDetalle  = $('#detalleBody');
  const totalCompraEl = $('#TotalCompra');
  const plantillaFila = $('#filaDetalleTemplate');

  const modalFactura  = $('#modalFactura');
  const factId        = $('#factIdCompra');
  const factComprador = $('#factComprador');
  const factProveedor = $('#factProveedor');
  const factFecha     = $('#factFecha');
  const factDetalle   = $('#factDetalleBody');
  const factTotal     = $('#factTotal');
  const btnCerrarFact = $('#btnCerrarFactura');
  const btnDescarga   = $('#btnDescargarFactura');

  let modo = 'crear'; 
  let compras = [];

  function showToast(msg, type='success') {
    if (!toast) return;
    toast.textContent   = msg;
    toast.className     = 'toast ' + (type === 'success' ? 'success' : 'error');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2200);
  }


  async function cargarCompras() {
    if (!tablaBody) return;
    tablaBody.innerHTML = `<tr><td colspan="7">Cargando...</td></tr>`;

    try {
      const r = await fetch('modulos/compras/listar.php', {
        credentials: 'same-origin'
      });

      const data = await r.json();

      if (!data.ok) {
        console.error(data.msg);
        tablaBody.innerHTML =
          `<tr><td colspan="7">Error: ${data.msg}</td></tr>`;
        return;
      }

      compras = Array.isArray(data.data) ? data.data : [];
      renderCompras();
    } catch (e) {
      console.error(e);
      tablaBody.innerHTML =
        `<tr><td colspan="7">Error al cargar las compras</td></tr>`;
    }
  }

  function renderCompras() {
    if (!tablaBody) return;
    const q = (inputBuscar?.value || '').toLowerCase();
    tablaBody.innerHTML = '';
    let count = 0;

    for (const c of compras) {
      const texto = `${c.Comprador || ''} ${c.Proveedor || ''}`.toLowerCase();
      if (q && !texto.includes(q)) continue;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${c.IdCompra}</td>
        <td>${c.Comprador || ''}</td>
        <td>${c.Proveedor || ''}</td>
        <td>${c.Fecha || ''}</td>
        <td style="text-align:center;">
          <button class="btn-icon btn-fact" title="Ver factura">
            <img src="img/factura.png" alt="Factura">
          </button>
        </td>
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

      tr.querySelector('.btn-fact')
        .addEventListener('click', () => verFactura(c.IdCompra));
      tr.querySelector('.btn-edit')
        .addEventListener('click', () => editarCompra(c.IdCompra));
      tr.querySelector('.btn-del')
        .addEventListener('click', () => eliminarCompra(c.IdCompra));

      tablaBody.appendChild(tr);
      count++;
    }

    if (!count) {
      tablaBody.innerHTML =
        `<tr><td colspan="7">No se encontraron compras</td></tr>`;
    }
  }


  function abrirNuevaCompra() {
    modo = 'crear';
    tituloCompra.textContent = 'Nueva compra';
    formCompra.reset();
    $('#IdCompra').value = '';

    const hoy = new Date().toISOString().slice(0, 10);
    const fechaInput = $('#FechaCompra');
    if (fechaInput) fechaInput.value = hoy;

    tbodyDetalle.innerHTML = '';
    agregarFilaDetalle();
    recalcularTotales();
    modalCompra.setAttribute('aria-hidden', 'false');
  }

  function cerrarModalCompra() {
    modalCompra.setAttribute('aria-hidden', 'true');
  }

  function agregarFilaDetalle(det = null) {
    if (!plantillaFila || !tbodyDetalle) return;

    const clone = plantillaFila.content.cloneNode(true);
    const tr = clone.querySelector('tr');
    const selProd = tr.querySelector('.sel-producto');
    const inpCant = tr.querySelector('.inp-cantidad');
    const inpPrec = tr.querySelector('.inp-precio');
    const btnRem  = tr.querySelector('.btn-remove-row');

    if (det) {
      selProd.value = det.IdProducto;
      inpCant.value = det.Cantidad;
      inpPrec.value = det.PrecioUnitario;
    }

    function onChange() {
      recalcularFila(tr);
      recalcularTotales();
    }

    inpCant.addEventListener('input', onChange);
    inpPrec.addEventListener('input', onChange);
    btnRem.addEventListener('click', () => {
      tr.remove();
      recalcularTotales();
    });

    tbodyDetalle.appendChild(tr);
    recalcularFila(tr);
    recalcularTotales();
  }

  function recalcularFila(tr) {
    const cant = parseFloat(tr.querySelector('.inp-cantidad')?.value || '0');
    const prec = parseFloat(tr.querySelector('.inp-precio')?.value || '0');
    const sub  = cant * prec;
    const celda = tr.querySelector('.celda-subtotal');
    if (celda) celda.textContent = `C$ ${sub.toFixed(2)}`;
  }

  function recalcularTotales() {
    let total = 0;
    tbodyDetalle?.querySelectorAll('tr').forEach(tr => {
      const cant = parseFloat(tr.querySelector('.inp-cantidad')?.value || '0');
      const prec = parseFloat(tr.querySelector('.inp-precio')?.value || '0');
      total += cant * prec;
    });
    if (totalCompraEl) totalCompraEl.value = `C$ ${total.toFixed(2)}`;
  }

  async function guardarCompra(ev) {
    ev.preventDefault();

    const filas = tbodyDetalle?.querySelectorAll('tr') || [];
    if (!filas.length) {
      showToast('Agregue al menos un producto', 'error');
      return;
    }

    const fd = new FormData(formCompra);
    const url = (modo === 'crear')
      ? 'modulos/compras/crear.php'
      : 'modulos/compras/actualizar.php';

    try {
      const r   = await fetch(url, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const res = await r.json();

      if (res.ok) {
        cerrarModalCompra();
        await cargarCompras();
        showToast(
          res.msg || (modo === 'crear'
                      ? 'Compra registrada'
                      : 'Compra actualizada'),
          'success'
        );
      } else {
        showToast(res.msg || 'No se pudo guardar', 'error');
      }
    } catch (e) {
      console.error(e);
      showToast('Error de red', 'error');
    }
  }

  async function editarCompra(IdCompra) {
    try {
      const r   = await fetch(
        'modulos/compras/detalle.php?IdCompra=' +
        encodeURIComponent(IdCompra),
        { credentials: 'same-origin' }
      );
      const res = await r.json();
      if (!res.ok) {
        showToast(res.msg || 'No se pudo cargar la compra', 'error');
        return;
      }

      modo = 'editar';
      tituloCompra.textContent = 'Editar compra';
      formCompra.reset();

      $('#IdCompra').value = res.cabecera.IdCompra;
      if (res.cabecera.IdProveedor) {
        const selProv = $('#IdProveedor');
        if (selProv) selProv.value = res.cabecera.IdProveedor;
      }
      const fechaInput = $('#FechaCompra');
      if (fechaInput) fechaInput.value = res.cabecera.Fecha;

      tbodyDetalle.innerHTML = '';
      (res.detalles || []).forEach(det => agregarFilaDetalle(det));
      recalcularTotales();

      modalCompra.setAttribute('aria-hidden', 'false');
    } catch (e) {
      console.error(e);
      showToast('Error cargando compra', 'error');
    }
  }

  function eliminarCompra(IdCompra) {

  const modal = document.createElement('div');
  modal.className = 'modal';
  modal.setAttribute('aria-hidden', 'false');

  modal.innerHTML = `
    <div class="modal-content small">
      <div class="modal-header">
        <h3>Confirmar eliminación</h3>
      </div>
      <p>¿Está seguro de eliminar la compra?</p>
      <div class="modal-actions">
        <button class="btn-cancelar">No</button>
        <button class="btn-eliminar">Sí, eliminar</button>
      </div>
    </div>
  `;

  document.body.appendChild(modal);

  const btnCancelar = modal.querySelector('.btn-cancelar');
  const btnEliminar = modal.querySelector('.btn-eliminar');

  btnCancelar.addEventListener('click', () => {
    modal.remove();
  });


  btnEliminar.addEventListener('click', async () => {
    try {
      const body = new URLSearchParams();
      body.append('IdCompra', IdCompra);

      const r   = await fetch('modulos/compras/eliminar.php', {
        method: 'POST',
        body,
        credentials: 'same-origin'
      });
      const res = await r.json();

      modal.remove();

      if (res.ok) {
        await cargarCompras();
        showToast('Compra eliminada con éxito', 'success');
      } else {
        showToast(res.msg || 'No se pudo eliminar la compra', 'error');
      }
    } catch (e) {
      console.error(e);
      modal.remove();
      showToast('Error de red', 'error');
    }
  });
}


  async function verFactura(IdCompra) {
    try {
      const r   = await fetch(
        'modulos/compras/detalle.php?IdCompra=' +
        encodeURIComponent(IdCompra),
        { credentials: 'same-origin' }
      );
      const res = await r.json();
      if (!res.ok) {
        showToast(res.msg || 'No se pudo cargar el detalle', 'error');
        return;
      }

      const cab = res.cabecera;
      factId.textContent        = cab.IdCompra;
      factComprador.textContent = cab.Comprador;
      factProveedor.textContent = cab.Proveedor;
      factFecha.textContent     = cab.Fecha;

      factDetalle.innerHTML = '';
      (res.detalles || []).forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.Producto || ''}</td>
          <td>${d.Cantidad || 0}</td>
          <td>C$ ${Number(d.PrecioUnitario).toFixed(2)}</td>
          <td>C$ ${Number(d.Subtotal).toFixed(2)}</td>
        `;
        factDetalle.appendChild(tr);
      });

      factTotal.textContent = `C$ ${Number(res.total || 0).toFixed(2)}`;

      if (btnDescarga) {
        btnDescarga.href =
          'modulos/compras/factura_pdf.php?IdCompra=' +
          encodeURIComponent(IdCompra);
      }

      modalFactura.setAttribute('aria-hidden', 'false');
    } catch (e) {
      console.error(e);
      showToast('Error de red al cargar factura', 'error');
    }
  }

  function cerrarModalFactura() {
    modalFactura.setAttribute('aria-hidden', 'true');
  }

  
  btnNueva      && btnNueva.addEventListener('click', abrirNuevaCompra);
  btnCancelar   && btnCancelar.addEventListener('click', cerrarModalCompra);
  btnAgregarFila&& btnAgregarFila.addEventListener('click', () => agregarFilaDetalle());
  formCompra    && formCompra.addEventListener('submit', guardarCompra);
  inputBuscar   && inputBuscar.addEventListener('input', renderCompras);
  btnCerrarFact && btnCerrarFact.addEventListener('click', cerrarModalFactura);


  cargarCompras();
})();
