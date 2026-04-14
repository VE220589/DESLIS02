document.addEventListener("DOMContentLoaded", () => {
  const config = window.appConfig || { isAdmin: false, isLoggedIn: false };
  const cartCount = document.getElementById("cart-count");
  const cartItemsContainer = document.getElementById("cart-items");
  const clearCartBtn = document.getElementById("clear-cart");
  const generateQuoteBtn = document.getElementById("generate-quote");
  const quoteModalEl = document.getElementById("quoteModal");
  const successModalEl = document.getElementById("successModal");
  const quoteModal = quoteModalEl ? new bootstrap.Modal(quoteModalEl) : null;
  const successModal = successModalEl
    ? new bootstrap.Modal(successModalEl)
    : null;
  const modalSummary = document.getElementById("modal-summary");
  const quoteForm = document.getElementById("quote-form");
  const quoteAlert = document.getElementById("quote-alert");
  const btnLogout = document.getElementById("btn-logout");
  const catalogFeedback = document.getElementById("catalog-feedback");
  const serviceForm = document.getElementById("service-form");
  const serviceAlert = document.getElementById("service-alert");
  const btnSaveService = document.getElementById("btn-save-service");
  const serviceModalEl = document.getElementById("serviceModal");
  const serviceModal = serviceModalEl
    ? bootstrap.Modal.getOrCreateInstance(serviceModalEl)
    : null;

  const quoteValidators = {
    "quote-nombre": (value) =>
      value.trim().length >= 3 ? "" : "Ingresa un nombre v\u00e1lido.",
    "quote-empresa": (value) =>
      value.trim().length >= 2 ? "" : "Ingresa el nombre de la empresa.",
    "quote-email": (value) =>
      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim())
        ? ""
        : "Ingresa un correo electr\u00f3nico v\u00e1lido.",
    "quote-telefono": (value) =>
      /^[0-9+\-\s()]{8,20}$/.test(value.trim())
        ? ""
        : "Ingresa un tel\u00e9fono v\u00e1lido.",
  };

  const serviceValidators = {
    "service-nombre": (value) =>
      value.trim().length >= 3
        ? ""
        : "El nombre debe tener al menos 3 caracteres.",
    "service-descripcion": (value) =>
      value.trim().length >= 10
        ? ""
        : "La descripci\u00f3n debe tener al menos 10 caracteres.",
    "service-precio": (value) => {
      const price = Number(value);
      return price >= 100 && price <= 10000
        ? ""
        : "El precio debe estar entre $100 y $10,000.";
    },
    "service-categoria": (value) =>
      value ? "" : "Selecciona una categor\u00eda v\u00e1lida.",
  };

  function showAlert(container, type, message) {
    if (!container) return;
    container.textContent = message;
    container.className = `alert alert-${type}`;
  }

  function hideAlert(container) {
    if (!container) return;
    container.textContent = "";
    container.className = "alert d-none";
  }

  function setFieldError(inputId, message) {
    const input = document.getElementById(inputId);
    const error = document.querySelector(`[data-error-for="${inputId}"]`);
    if (!input || !error) return !message;

    if (!message) {
      input.classList.remove("is-invalid");
      input.classList.add("is-valid");
      error.textContent = "";
      error.classList.add("d-none");
      return true;
    }

    input.classList.remove("is-valid");
    input.classList.add("is-invalid");
    error.textContent = message;
    error.classList.remove("d-none");
    return false;
  }

  function validateGroup(validators) {
    return Object.entries(validators).every(([inputId, validator]) =>
      setFieldError(inputId, validator(document.getElementById(inputId).value))
    );
  }

  Object.keys(quoteValidators).forEach((inputId) => {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener("input", () => {
      setFieldError(inputId, quoteValidators[inputId](input.value));
    });
  });

  Object.keys(serviceValidators).forEach((inputId) => {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.addEventListener("input", () => {
      setFieldError(inputId, serviceValidators[inputId](input.value));
    });
    input.addEventListener("change", () => {
      setFieldError(inputId, serviceValidators[inputId](input.value));
    });
  });

  async function request(url, options = {}) {
    const response = await fetch(url, options);
    return response.json();
  }

  async function loadCart() {
    if (!cartItemsContainer) return;

    try {
      const data = await request("index.php?action=get_cart");

      if (!data.success) {
        throw new Error(data.message || "No se pudo cargar el carrito.");
      }

      cartCount.textContent = data.totalItems;

      if (!data.items || data.items.length === 0) {
        cartItemsContainer.innerHTML =
          "<p class='text-muted text-center'>Carrito vac\u00edo.</p>";
        return;
      }

      let html = "";
      data.items.forEach((item) => {
        const disabledPlus = item.cantidad >= 10 ? "disabled" : "";
        const disabledMinus = item.cantidad <= 1 ? "disabled" : "";

        html += `
          <div class="border-bottom mb-2 pb-2">
            <strong class="d-block">${item.nombre}</strong>
            <small class="text-muted">${item.categoria}</small>
            <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
              <span class="text-primary">$${item.precio.toFixed(2)} x ${item.cantidad}</span>
              <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary decrease" data-id="${item.id}" ${disabledMinus}>-</button>
                <button class="btn btn-outline-secondary increase" data-id="${item.id}" ${disabledPlus}>+</button>
                <button class="btn btn-danger remove" data-id="${item.id}">X</button>
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
      cartItemsContainer.innerHTML =
        "<p class='text-danger text-center'>No se pudo cargar el carrito.</p>";
    }
  }

  function addCartEventListeners() {
    document.querySelectorAll(".increase").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await mutateCart({
          id: btn.dataset.id,
          action_type: "increase",
        });
      });
    });

    document.querySelectorAll(".decrease").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await mutateCart({
          id: btn.dataset.id,
          action_type: "decrease",
        });
      });
    });

    document.querySelectorAll(".remove").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await removeFromCart({ id: btn.dataset.id });
      });
    });
  }

  async function mutateCart(payload) {
    try {
      const data = await request("index.php?action=update_cart", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(payload),
      });

      if (!data.success) {
        throw new Error(data.message);
      }

      hideAlert(catalogFeedback);
      await loadCart();
    } catch (error) {
      showAlert(catalogFeedback, "danger", error.message);
    }
  }

  async function removeFromCart(payload) {
    try {
      const data = await request("index.php?action=remove_from_cart", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(payload),
      });

      if (!data.success) {
        throw new Error(data.message);
      }

      hideAlert(catalogFeedback);
      await loadCart();
    } catch (error) {
      showAlert(catalogFeedback, "danger", error.message);
    }
  }

  document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", async () => {
      try {
        const data = await request("index.php?action=add_to_cart", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ id: button.dataset.id }),
        });

        if (!data.success) {
          throw new Error(data.message);
        }

        showAlert(catalogFeedback, "success", "Servicio agregado al carrito.");
        await loadCart();
      } catch (error) {
        showAlert(catalogFeedback, "danger", error.message);
      }
    });
  });

  if (clearCartBtn) {
    clearCartBtn.addEventListener("click", async () => {
      await removeFromCart({ clear: "true" });
    });
  }

  if (btnLogout) {
    btnLogout.addEventListener("click", async () => {
      if (!confirm("\u00bfSeguro que deseas cerrar sesi\u00f3n?")) return;

      await fetch("index.php?action=logout");
      window.location.href = "index.php?page=home";
    });
  }

  if (generateQuoteBtn) {
    generateQuoteBtn.addEventListener("click", async () => {
      try {
        const data = await request("index.php?action=get_cart");

        if (!data.items || data.items.length === 0) {
          showAlert(
            catalogFeedback,
            "warning",
            "Agrega al menos un servicio antes de generar la cotizaci\u00f3n."
          );
          return;
        }

        modalSummary.innerHTML = `
          <div class="d-flex justify-content-between mb-1"><span>Subtotal:</span> <strong>$${data.subtotal.toFixed(
            2
          )}</strong></div>
          <div class="d-flex justify-content-between mb-1 text-success"><span>Descuento:</span> <strong>-$${data.descuento.toFixed(
            2
          )}</strong></div>
          <div class="d-flex justify-content-between mb-2"><span>IVA (13%):</span> <strong>$${data.iva.toFixed(
            2
          )}</strong></div>
          <div class="d-flex justify-content-between border-top pt-2 mt-2">
            <span class="fs-5">Total a pagar:</span> 
            <strong class="fs-5 text-primary">$${data.total.toFixed(2)}</strong>
          </div>
        `;

        hideAlert(quoteAlert);
        quoteModal.show();
      } catch (error) {
        showAlert(catalogFeedback, "danger", error.message);
      }
    });
  }

  if (quoteForm) {
    quoteForm.addEventListener("submit", async (event) => {
      event.preventDefault();

      const isValid = validateGroup(quoteValidators);
      if (!isValid) {
        showAlert(quoteAlert, "danger", "Completa correctamente todos los datos del cliente.");
        return;
      }

      hideAlert(quoteAlert);
      const submitButton = quoteForm.querySelector("button[type='submit']");
      const originalText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.textContent = "Procesando...";

      try {
        const data = await request("index.php?action=generate_quote", {
          method: "POST",
          body: new FormData(quoteForm),
        });

        if (!data.success) {
          throw new Error(data.message || "No se pudo generar la cotizaci\u00f3n.");
        }

        quoteModal.hide();
        document.getElementById("confirm-codigo").textContent = data.codigo;
        document.getElementById("confirm-fecha").textContent =
          data.fechaGeneracion;
        document.getElementById("confirm-validez").textContent =
          data.fechaValidez;
        document.getElementById("confirm-total").textContent =
          "$" + data.total.toFixed(2);

        successModal.show();
        quoteForm.reset();
        document.getElementById("quote-nombre").value =
          document.getElementById("quote-nombre").defaultValue;
        Object.keys(quoteValidators).forEach((inputId) => {
          const input = document.getElementById(inputId);
          input.classList.remove("is-valid", "is-invalid");
          const errorNode = document.querySelector(`[data-error-for="${inputId}"]`);
          if (errorNode) {
            errorNode.textContent = "";
            errorNode.classList.add("d-none");
          }
        });

        showAlert(catalogFeedback, "success", "Cotizaci\u00f3n generada correctamente.");
        await loadCart();
      } catch (error) {
        showAlert(quoteAlert, "danger", error.message);
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
      }
    });
  }

  if (config.isAdmin) {
    document.querySelectorAll(".delete-service").forEach((button) => {
      button.addEventListener("click", async () => {
        const serviceName = button.dataset.name || "este servicio";
        if (!confirm(`\u00bfDeseas eliminar ${serviceName}?`)) return;

        try {
          const data = await request("index.php?action=delete_service", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ id: button.dataset.id }),
          });

          if (!data.success) {
            throw new Error(data.message || "No se pudo eliminar el servicio.");
          }

          showAlert(catalogFeedback, "success", data.message);
          window.location.reload();
        } catch (error) {
          showAlert(catalogFeedback, "danger", error.message);
        }
      });
    });

    if (serviceForm) {
      serviceForm.addEventListener("submit", async (event) => {
        event.preventDefault();

        const isValid = validateGroup(serviceValidators);
        if (!isValid) {
          showAlert(serviceAlert, "danger", "Revisa los datos del nuevo servicio.");
          return;
        }

        hideAlert(serviceAlert);
        const originalText = btnSaveService.textContent;
        btnSaveService.disabled = true;
        btnSaveService.textContent = "Guardando...";

        try {
          const data = await request("index.php?action=create_service", {
            method: "POST",
            body: new FormData(serviceForm),
          });

          if (!data.success) {
            throw new Error(data.message || "No se pudo guardar el servicio.");
          }

          showAlert(serviceAlert, "success", data.message);
          setTimeout(() => window.location.reload(), 800);
        } catch (error) {
          showAlert(serviceAlert, "danger", error.message);
        } finally {
          btnSaveService.disabled = false;
          btnSaveService.textContent = originalText;
        }
      });

      serviceModalEl.addEventListener("hidden.bs.modal", () => {
        hideAlert(serviceAlert);
        serviceForm.reset();
        Object.keys(serviceValidators).forEach((inputId) => {
          const input = document.getElementById(inputId);
          input.classList.remove("is-valid", "is-invalid");
          const errorNode = document.querySelector(`[data-error-for="${inputId}"]`);
          if (errorNode) {
            errorNode.textContent = "";
            errorNode.classList.add("d-none");
          }
        });
      });
    }
  }

  if (config.isLoggedIn) {
    loadCart();
  }
});
