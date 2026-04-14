document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");
  const loginAlert = document.getElementById("login-alert");
  const btnLogin = document.getElementById("btn-login");
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  function showAlert(type, message) {
    loginAlert.textContent = message;
    loginAlert.className = `alert alert-${type}`;
  }

  function validateEmail() {
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
    emailInput.classList.toggle("is-invalid", !isValid && emailInput.value !== "");
    emailInput.classList.toggle("is-valid", isValid);
    return isValid;
  }

  function validatePassword() {
    const isValid = passwordInput.value.trim().length >= 8;
    passwordInput.classList.toggle(
      "is-invalid",
      !isValid && passwordInput.value !== ""
    );
    passwordInput.classList.toggle("is-valid", isValid);
    return isValid;
  }

  emailInput.addEventListener("input", validateEmail);
  passwordInput.addEventListener("input", validatePassword);

  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    loginAlert.className = "alert d-none";

    const isValid = validateEmail() && validatePassword();
    if (!isValid) {
      showAlert(
        "danger",
        "Ingresa un correo v\u00e1lido y una contrase\u00f1a de al menos 8 caracteres."
      );
      return;
    }

    const originalText = btnLogin.textContent;
    btnLogin.textContent = "Verificando...";
    btnLogin.disabled = true;

    try {
      const response = await fetch("index.php?action=login", {
        method: "POST",
        body: new FormData(loginForm),
      });

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message || "Credenciales incorrectas.");
      }

      showAlert("success", "\u00a1Acceso concedido! Redirigiendo...");
      setTimeout(() => {
        window.location.href = "index.php?page=services";
      }, 900);
    } catch (error) {
      showAlert("danger", error.message || "Error de conexi\u00f3n con el servidor.");
      btnLogin.textContent = originalText;
      btnLogin.disabled = false;
    }
  });
});
