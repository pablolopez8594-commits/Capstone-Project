<?php include "partials/header.php"; ?>

<main class="container">
    <div class="container text-center">
        <form id="loginForm" method="post" action="#">
            <h1>Account Details</h1>
            <div class="row">
                <label for="email">EMAIL:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="row">
                <label for="password">PASSWORD:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <!-- Aquí aparecen los errores -->
            <div class="row">
                <p id="loginError" style="color:red; display:none;"></p>
            </div>

            <div class="row">
                <button class="btn btn-success" type="submit" id="loginBtn">LOGIN</button>
            </div>
        </form>
        <div class="row">
            <a class="btn btn-primary" href="register.php">REGISTER</a>
        </div>
    </div>
</main>

<script>
document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const btn = document.getElementById("loginBtn");
  const errorEl = document.getElementById("loginError");

  errorEl.style.display = "none";
  btn.disabled = true;
  btn.textContent = "Logging in...";

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  try {
    const res = await fetch("/capstone/api/v1/auth_login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify({ email, password }),
    });

    const data = await res.json();

    if (data.ok) {
      window.location.href = "/capstone/index.php";
    } else {
      errorEl.textContent = data.error ?? "Login failed.";
      errorEl.style.display = "block";
    }
  } catch (err) {
    errorEl.textContent = "Network/JS error: " + err.message;
    errorEl.style.display = "block";
  } finally {
    btn.disabled = false;
    btn.textContent = "LOGIN";
  }
});
</script>

<?php include "partials/nav.php"; ?>
<?php include "partials/footer.php"; ?>