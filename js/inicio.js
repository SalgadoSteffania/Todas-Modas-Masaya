(() => {
  async function cargarDashboard() {
    try {
      const r = await fetch('modulos/inicio/contadores.php', {
        credentials: 'same-origin'
      });
      const data = await r.json();

      if (!data.ok) return;

      const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
      };

      setText('countClientes',     data.Clientes ?? 0);
      setText('countProveedores',  data.Proveedores ?? 0);
      setText('countInventario',   data.Inventario ?? 0);
      setText('countVendidas',     data.Vendidas ?? 0);

      const importe = Number(data.Importe || 0).toLocaleString('es-NI', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      setText('countImporte',  'C$ ' + importe);
      setText('countImporte2', 'C$ ' + importe);

    } catch (e) {
      console.error('Error cargando dashboard', e);
    }
  }

  cargarDashboard();
})();
