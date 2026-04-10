document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const loginAlert = document.getElementById("login-alert");
    const btnLogin = document.getElementById("btn-login");

    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        // Limpiamos alertas previas
        loginAlert.classList.add("d-none");
        loginAlert.classList.remove("alert-danger", "alert-success");

        // Cambiamos el estado del botón para indicar carga
        const originalText = btnLogin.innerHTML;
        btnLogin.innerHTML = "Verificando...";
        btnLogin.disabled = true;

        const formData = new FormData(loginForm);

        try {
            // Enviamos la petición AJAX al Enrutador apuntando a la acción 'login'
            const response = await fetch("index.php?action=login", {
                method: "POST",
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Mostramos éxito
                loginAlert.textContent = "¡Acceso concedido! Redirigiendo...";
                loginAlert.classList.add("alert-success");
                loginAlert.classList.remove("d-none");

                // Esperamos 1 segundo y redirigimos al catálogo
                setTimeout(() => {
                    window.location.href = "index.php?page=services";
                }, 1000);
                
            } else {
                // Mostramos el error devuelto por el controlador
                loginAlert.textContent = data.message;
                loginAlert.classList.add("alert-danger");
                loginAlert.classList.remove("d-none");
                
                // Restauramos el botón
                btnLogin.innerHTML = originalText;
                btnLogin.disabled = false;
            }
        } catch (error) {
            loginAlert.textContent = "Error de conexión con el servidor.";
            loginAlert.classList.add("alert-danger");
            loginAlert.classList.remove("d-none");
            btnLogin.innerHTML = originalText;
            btnLogin.disabled = false;
        }
    });
});