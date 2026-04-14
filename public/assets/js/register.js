document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("register-form");
  const alertBox = document.getElementById("register-alert");
  const submitButton = document.getElementById("btn-register");

  const fields = {
    nombre: {
      input: document.getElementById("nombre"),
      validate: (value) =>
        value.trim().length >= 3
          ? ""
          : "Ingresa un nombre con al menos 3 caracteres.",
    },
    email: {
      input: document.getElementById("email"),
      validate: (value) =>
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim())
          ? ""
          : "Ingresa un correo electr\u00f3nico v\u00e1lido.",
    },
    password: {
      input: document.getElementById("password"),
      validate: (value) =>
        value.length >= 8
          ? ""
          : "La contrase\u00f1a debe tener al menos 8 caracteres.",
    },
    confirm_password: {
      input: document.getElementById("confirm_password"),
      validate: (value) =>
        value === fields.password.input.value
          ? ""
          : "Las contrase\u00f1as no coinciden.",
    },
  };

  function setFieldState(fieldName, message) {
    const field = fields[fieldName];
    const errorNode = document.querySelector(`[data-error-for="${fieldName}"]`);

    if (!message) {
      field.input.classList.remove("is-invalid");
      field.input.classList.add("is-valid");
      errorNode.textContent = "";
      errorNode.classList.add("d-none");
      return true;
    }

    field.input.classList.remove("is-valid");
    field.input.classList.add("is-invalid");
    errorNode.textContent = message;
    errorNode.classList.remove("d-none");
    return false;
  }

  function validateField(fieldName) {
    return setFieldState(fieldName, fields[fieldName].validate(fields[fieldName].input.value));
  }

  Object.keys(fields).forEach((fieldName) => {
    fields[fieldName].input.addEventListener("input", () => {
      validateField(fieldName);
      if (fieldName === "password" && fields.confirm_password.input.value !== "") {
        validateField("confirm_password");
      }
    });
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    alertBox.classList.add("d-none");
    alertBox.classList.remove("alert-danger", "alert-success");

    const isValid = Object.keys(fields).every(validateField);
    if (!isValid) {
      alertBox.textContent = "Corrige los campos marcados antes de continuar.";
      alertBox.classList.remove("d-none");
      alertBox.classList.add("alert-danger");
      return;
    }

    const originalText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = "Creando cuenta...";

    try {
      const response = await fetch("index.php?action=register", {
        method: "POST",
        body: new FormData(form),
      });

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || "No se pudo completar el registro.");
      }

      alertBox.textContent = "Cuenta creada correctamente. Redirigiendo...";
      alertBox.classList.remove("d-none");
      alertBox.classList.add("alert-success");
      form.reset();
      Object.values(fields).forEach(({ input }) => input.classList.remove("is-valid"));

      setTimeout(() => {
        window.location.href = "index.php?page=services";
      }, 900);
    } catch (error) {
      alertBox.textContent = error.message || "Ocurri\u00f3 un error de conexi\u00f3n.";
      alertBox.classList.remove("d-none");
      alertBox.classList.add("alert-danger");
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    }
  });
});
