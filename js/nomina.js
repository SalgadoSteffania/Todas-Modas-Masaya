(() => {
  const $ = s => document.querySelector(s);

  const tbody       = $('#tablaNomina tbody');
  const filtroCed   = $('#filtroCedula');
  const fechaDesde  = $('#nominaDesde');
  const fechaHasta  = $('#nominaHasta');
  const toast       = $('#toast');

  const modal       = $('#modalNomina');
  const tituloModal = $('#modalTituloNomina');
  const form        = $('#formNomina');
  const btnNueva    = $('#btnNuevaNomina');
  const btnCancel   = $('#btnCancelarNomina');

  const inputId     = $('#IdNomina');
  const inputCedula = $('#CedulaNomina');
  const inputNombre = $('#NombreEmpleadoNomina');
  const inputCargo  = $('#CargoEmpleadoNomina');
  const inputFecha  = $('#FechaRegistroNomina');

  const inputSalBase= $('#SalarioBasicoNomina');
  const inputHoras  = $('#HorasExtrasNomina');
  const inputBonos  = $('#BonosNomina');
  const inputIncen  = $('#IncentivosNomina');
  const inputPrest  = $('#PrestamosNomina');

  const modalVer    = $('#modalVerNomina');
  const verId       = $('#verIdNomina');
  const verCedula   = $('#verCedula');
  const verNombre   = $('#verNombre');
  const verCargo    = $('#verCargo');
  const verFecha    = $('#verFecha');
  const verDetBody  = $('#verDetalleBody');
  const verSalNeto  = $('#verSalarioNeto');
  const btnCerrarVer= $('#btnCerrarVerNomina');
  const linkPDF     = $('#btnDescargarNomina');

  let modo = 'crear';
  let nominas = [];
  let empleados = [];

  //Cargar empleados
  const empDiv = $('#empleadosDataNomina');
  if (empDiv) {
    try {
      empleados = JSON.parse(empDiv.dataset.empleados || '[]');
    } catch (e) {
      empleados = [];
    }
  }

  const showToast = (msg, type='success') => {
    if (!toast) return;
    toast.textContent = msg;
    toast.className = 'toast ' + (type==='success'?'success':'error');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 2200);
  };

  //  Buscar empleado por cédula 
  function findEmpleadoByCedula(ced) {
    if (!ced) return null;
    ced = ced.trim();
    return empleados.find(e => e.Cedula === ced) || null;
  }

  //  Autorrellenar nombre y cargo al escribir cédula
  function onCedulaInput() {
    const ced = inputCedula.value;
    const emp = findEmpleadoByCedula(ced);
    if (emp) {
      inputNombre.value = `${emp.Nombre} ${emp.Apellido}`;
      inputCargo.value  = emp.Cargo || '';
    } else {
      inputNombre.value = '';
      inputCargo.value  = '';
    }
  }

  // eventos para el autorrelleno
  inputCedula?.addEventListener('input', onCedulaInput);
  inputCedula?.addEventListener('change', onCedulaInput);

  // Cargar lista de nómina
  async function cargarNominas() {
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="12">Cargando...</td></tr>`;
    try {
      const r = await fetch('modulos/nomina/listar.php', { credentials:'same-origin' });
      const data = await r.json();
      if (!data.ok) {
        tbody.innerHTML = `<tr><td colspan="12">Error: ${data.msg}</td></tr>`;
        return;
      }
      nominas = data.data || [];
      renderNominas();
    } catch (e) {
      tbody.innerHTML = `<tr><td colspan="12">Error al cargar</td></tr>`;
    }
  }

  function renderNominas() {
    if (!tbody) return;
    tbody.innerHTML = '';

    const fCed   = (filtroCed?.value || '').toLowerCase();
    const fDesde = fechaDesde?.value || '';
    const fHasta = fechaHasta?.value || '';

    let count = 0;

    for (const n of nominas) {
      const texto = `${n.Cedula || ''} ${n.NombreCompleto || ''}`.toLowerCase();

      if (fCed && !texto.includes(fCed)) continue;

      const f = (n.FechaRegistro || '').slice(0,10);
      if (fDesde && f < fDesde) continue;
      if (fHasta && f > fHasta) continue;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${n.IdNomina}</td>
        <td>${n.Cedula}</td>
        <td>${n.NombreCompleto || ''}</td>
        <td>${n.Cargo || ''}</td>
        <td>${n.FechaRegistro || ''}</td>
        <td>C$ ${Number(n.SalarioBasico).toFixed(2)}</td>
        <td>C$ ${Number(n.SalarioBruto).toFixed(2)}</td>
        <td>C$ ${Number(n.DeduccionTotal).toFixed(2)}</td>
        <td>C$ ${Number(n.SalarioNeto).toFixed(2)}</td>
        <td style="text-align:center;">
          <button class="btn-icon btn-ver">
            <img src="img/vernomina.svg" alt="Ver">
          </button>
        </td>
        <td style="text-align:center;">
          <button class="btn-icon btn-edit">
            <img src="img/editar.png" alt="Editar">
          </button>
        </td>
        <td style="text-align:center;">
          <button class="btn-icon btn-del">
            <img src="img/eliminar.png" alt="Eliminar">
          </button>
        </td>
      `;

      tr.querySelector('.btn-ver').onclick  = () => verNomina(n.IdNomina);
      tr.querySelector('.btn-edit').onclick = () => editarNomina(n.IdNomina);
      tr.querySelector('.btn-del').onclick  = () => eliminarNomina(n.IdNomina);

      tbody.appendChild(tr);
      count++;
    }

    if (!count) {
      tbody.innerHTML = `<tr><td colspan="12">No se encontraron nóminas</td></tr>`;
    }
  }

  // Modal Nuevo y  Editar
  function abrirNuevaNomina() {
    modo = 'crear';
    tituloModal.textContent = 'Nueva nómina';
    form.reset();
    inputId.value = '';

   
    if (inputFecha) {
      inputFecha.value = new Date().toISOString().slice(0,10);
    }

    inputNombre.value = '';
    inputCargo.value  = '';

    modal.setAttribute('aria-hidden','false');
  }

  function cerrarModalNomina() {
    modal.setAttribute('aria-hidden','true');
  }

  async function editarNomina(IdNomina) {
    try {
      const r = await fetch(
        'modulos/nomina/detalle.php?IdNomina=' + encodeURIComponent(IdNomina),
        { credentials:'same-origin' }
      );
      const res = await r.json();
      if (!res.ok) {
        showToast(res.msg || 'No se pudo cargar la nómina', 'error');
        return;
      }

      modo = 'editar';
      tituloModal.textContent = 'Editar nómina';
      form.reset();

      const c = res.cabecera;
      const d = res.detalle;

      inputId.value      = c.IdNomina;
      inputCedula.value  = c.Cedula;
      inputFecha.value   = c.FechaRegistro || '';

      inputSalBase.value = c.SalarioBasico ?? 0;
      inputHoras.value   = d.HorasExtras   ?? 0;
      inputBonos.value   = d.Bonos         ?? 0;
      inputIncen.value   = d.Incentivos    ?? 0;
      inputPrest.value   = d.Prestamos     ?? 0;

 
      onCedulaInput();   

      modal.setAttribute('aria-hidden','false');
    } catch (e) {
      showToast('Error de red', 'error');
    }
  }

  // Guardar 
  async function guardarNomina(ev) {
    ev.preventDefault();

    const ced = inputCedula.value.trim();
    const sal = parseFloat(inputSalBase.value || '0');

    if (!ced || !sal || sal <= 0) {
      showToast('Cédula y salario básico son obligatorios', 'error');
      return;
    }

    const url = (modo === 'crear')
      ? 'modulos/nomina/crear.php'
      : 'modulos/nomina/actualizar.php';

    const fd = new FormData(form);

    try {
      const r   = await fetch(url, {
        method:'POST',
        body:fd,
        credentials:'same-origin'
      });
      const res = await r.json();

      if (res.ok) {
        cerrarModalNomina();
        await cargarNominas();
        showToast(res.msg || 'Nómina guardada');
      } else {
        showToast(res.msg || 'No se pudo guardar', 'error');
      }
    } catch (e) {
      showToast('Error de red', 'error');
    }
  }

  //  Eliminar =====
  function eliminarNomina(IdNomina) {
    const m = document.createElement('div');
    m.className = 'modal';
    m.setAttribute('aria-hidden','false');
    m.innerHTML = `
      <div class="modal-content small">
        <div class="modal-header">
          <h3>Confirmar eliminación</h3>
        </div>
        <p>¿Está seguro de eliminar esta nómina?</p>
        <div class="modal-actions">
          <button class="btn-cancelar">No</button>
          <button class="btn-eliminar">Sí, eliminar</button>
        </div>
      </div>
    `;
    document.body.appendChild(m);

    const btnNo  = m.querySelector('.btn-cancelar');
    const btnSi  = m.querySelector('.btn-eliminar');

    btnNo.onclick = () => m.remove();
    btnSi.onclick = async () => {
      try {
        const body = new URLSearchParams();
        body.append('IdNomina', IdNomina);

        const r = await fetch('modulos/nomina/eliminar.php', {
          method:'POST',
          body,
          credentials:'same-origin'
        });
        const res = await r.json();
        m.remove();
        if (res.ok) {
          await cargarNominas();
          showToast('Nómina eliminada');
        } else {
          showToast(res.msg || 'No se pudo eliminar','error');
        }
      } catch (e) {
        m.remove();
        showToast('Error de red','error');
      }
    };
  }

  // ===== Ver Nómina =====
  async function verNomina(IdNomina) {
    try {
      const r = await fetch(
        'modulos/nomina/detalle.php?IdNomina=' + encodeURIComponent(IdNomina),
        { credentials:'same-origin' }
      );
      const res = await r.json();
      if (!res.ok) {
        showToast(res.msg || 'No se pudo cargar la nómina', 'error');
        return;
      }

      const c = res.cabecera;
      const d = res.detalle;

      verId.textContent      = c.IdNomina;
      verCedula.textContent  = c.Cedula;
      verNombre.textContent  = c.NombreCompleto;
      verCargo.textContent   = c.Cargo;
      verFecha.textContent   = c.FechaRegistro;

   verDetBody.innerHTML = '';

const addRowMoney = (concepto, valor) => {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${concepto}</td>
    <td>C$ ${Number(valor).toFixed(2)}</td>
  `;
  verDetBody.appendChild(tr);
};

const addRowText = (concepto, valor) => {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${concepto}</td>
    <td>${valor}</td>
  `;
  verDetBody.appendChild(tr);
};

// Aquí usas dinero o texto según corresponda
addRowMoney('Salario básico', c.SalarioBasico);
addRowText('Horas extras', d.HorasExtras);               // ← solo número
addRowMoney('Valor horas extras', d.ValorHorasExtra);    // ← C$ xx.xx
addRowMoney('Bonos', d.Bonos);
addRowMoney('Incentivos', d.Incentivos);
addRowMoney('Préstamos', d.Prestamos);
addRowMoney('Salario bruto', c.SalarioBruto);
addRowMoney('INSS laboral', c.INNS);
addRowMoney('IR mensual', c.IR);
addRowMoney('Deducción total', c.DeduccionTotal);

verSalNeto.textContent = `C$ ${Number(c.SalarioNeto).toFixed(2)}`;



      if (linkPDF) {
        linkPDF.href = 'modulos/nomina/nomina_pdf.php?IdNomina=' +
                       encodeURIComponent(c.IdNomina);
      }

      modalVer.setAttribute('aria-hidden','false');
    } catch (e) {
      showToast('Error de red','error');
    }
  }

  function cerrarModalVer() {
    modalVer.setAttribute('aria-hidden','true');
  }

  // ==== Eventos ====
  btnNueva?.addEventListener('click', abrirNuevaNomina);
  btnCancel?.addEventListener('click', cerrarModalNomina);
  form?.addEventListener('submit', guardarNomina);

  filtroCed?.addEventListener('input', renderNominas);
  fechaDesde?.addEventListener('change', renderNominas);
  fechaHasta?.addEventListener('change', renderNominas);

  btnCerrarVer?.addEventListener('click', cerrarModalVer);

  // Carga inicial
  cargarNominas();
})();
