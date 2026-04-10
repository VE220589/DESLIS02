document.addEventListener("DOMContentLoaded", () => {
  // Referencias del DOM
  const cartCount = document.getElementById("cart-count");
  const cartItemsContainer = document.getElementById("cart-items");
  const clearCartBtn = document.getElementById("clear-cart");
  const generateQuoteBtn = document.getElementById("generate-quote");
  const quoteModalEl = document.getElementById("quoteModal");
  const quoteModal = quoteModalEl ? new bootstrap.Modal(quoteModalEl) : null;
  const modalSummary = document.getElementById("modal-summary");
  const quoteForm = document.getElementById("quote-form");
  const btnLogout = document.getElementById("btn-logout");

  // ==========================================
  // 1. CERRAR SESIÓN
  // ==========================================
  if (btnLogout) {
    btnLogout.addEventListener("click", async () => {
      if (confirm("¿Seguro que deseas cerrar sesión?")) {
        await fetch("index.php?action=logout");
        window.location.href = "index.php?page=home";
      }
    });
  }

  // Si el carrito no existe en la página (usuario no logueado), detenemos el script
  if (!cartItemsContainer) return;

  // ==========================================
  // 2. CARGAR CARRITO
  // ==========================================
  async function loadCart() {
    try {
      // NUEVA RUTA MVC
      const response = await fetch("index.php?action=get_cart");
      const data = await response.json();

      cartCount.textContent = data.totalItems;

      if (!data.items || data.items.length === 0) {
        cartItemsContainer.innerHTML =
          "<p class='text-muted text-center'>Carrito vacío.</p>";
        return;
      }

      let html = "";
      data.items.forEach((item) => {
        let disabledPlus = item.cantidad >= 10 ? "disabled" : "";
        let disabledMinus = item.cantidad <= 1 ? "disabled" : "";

        html += `
                    <div class="border-bottom mb-2 pb-2">
                        <strong class="d-block text-truncate">${item.nombre}</strong>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-primary">$${item.precio.toFixed(2)} x ${item.cantidad}</span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary decrease" data-id="${item.id}" ${disabledMinus}>-</button>
                                <button class="btn btn-outline-secondary increase" data-id="${item.id}" ${disabledPlus}>+</button>
                                <button class="btn btn-danger remove" data-id="${item.id}"><i class="bi bi-trash"></i> X</button>
                            </div>
                        </div>
                    </div>
                `;
      });

      html += `
                <div class="mt-3 text-end">
                    <p class="mb-1">Subtotal: $${data.subtotal.toFixed(2)}</p>
                    <p class="mb-1 text-success">Descuento: -$${data.descuento.toFixed(2)}</p>
                    <p class="mb-1">IVA (13%): $${data.iva.toFixed(2)}</p>
                    <h5 class="mt-2 fw-bold text-primary">Total: $${data.total.toFixed(2)}</h5>
                </div>
            `;

      cartItemsContainer.innerHTML = html;
      addCartEventListeners();
    } catch (error) {
      console.error("Error cargando carrito", error);
    }
  }

  // ==========================================
  // 3. EVENTOS DE BOTONES
  // ==========================================
  function addCartEventListeners() {
    document.querySelectorAll(".increase").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await fetch("index.php?action=update_cart", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${btn.dataset.id}&action_type=increase`,
        });
        loadCart();
      });
    });

    document.querySelectorAll(".decrease").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await fetch("index.php?action=update_cart", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${btn.dataset.id}&action_type=decrease`,
        });
        loadCart();
      });
    });

    document.querySelectorAll(".remove").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await fetch("index.php?action=remove_from_cart", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${btn.dataset.id}`,
        });
        loadCart();
      });
    });
  }

  document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", async () => {
      await fetch("index.php?action=add_to_cart", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${button.dataset.id}`,
      });
      loadCart();
    });
  });

  clearCartBtn.addEventListener("click", async () => {
    await fetch("index.php?action=remove_from_cart", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `clear=true`,
    });
    loadCart();
  });

  // ==========================================
  // 4. GENERAR COTIZACIÓN
  // ==========================================

  // Inicializamos el modal de éxito de Bootstrap
  const successModalEl = document.getElementById("successModal");
  const successModal = successModalEl
    ? new bootstrap.Modal(successModalEl)
    : null;

  generateQuoteBtn.addEventListener("click", async () => {
    const response = await fetch("index.php?action=get_cart");
    const data = await response.json();

    if (data.items.length === 0) {
      alert("El carrito está vacío.");
      return;
    }

    modalSummary.innerHTML = `
            <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span> <strong>$${data.subtotal.toFixed(2)}</strong></div>
            <div class="d-flex justify-content-between mb-1 text-success"><span>Descuento:</span> <strong>-$${data.descuento.toFixed(2)}</strong></div>
            <div class="d-flex justify-content-between mb-2"><span>IVA (13%):</span> <strong>$${data.iva.toFixed(2)}</strong></div>
            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                <span class="fs-5">Total a pagar:</span> 
                <strong class="fs-5 text-primary">$${data.total.toFixed(2)}</strong>
            </div>
        `;
    quoteModal.show();
  });

  quoteForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const btnSubmit = quoteForm.querySelector("button[type='submit']");
    const originalText = btnSubmit.innerHTML;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = "Procesando...";

    const formData = new FormData(quoteForm);

    try {
      const response = await fetch("index.php?action=generate_quote", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        // 1. Ocultamos el modal del formulario
        quoteModal.hide();

        // 2. Limpiamos el formulario para la próxima vez
        quoteForm.reset();

        // 3. Llenamos el modal de éxito con los datos del backend
        document.getElementById("confirm-codigo").textContent = data.codigo;
        document.getElementById("confirm-fecha").textContent =
          data.fechaGeneracion;
        document.getElementById("confirm-validez").textContent =
          data.fechaValidez;
        document.getElementById("confirm-total").textContent =
          "$" + data.total.toFixed(2);

        // 4. Mostramos el modal de éxito
        successModal.show();

        // 5. Recargamos el carrito (que ahora estará vacío)
        loadCart();
      } else {
        alert("Error al generar: " + data.message);
      }
    } catch (error) {
      alert("Ocurrió un error de conexión.");
    } finally {
      // Restauramos el botón
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = originalText;
    }
  });

  // Inicializar carrito al cargar la página
  loadCart();
});
