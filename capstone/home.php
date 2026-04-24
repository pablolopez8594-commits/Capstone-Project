<?php
require_once "partials/auth_guard.php";
include "partials/header.php";
?>

<main class="container">
  <h1>WELCOME BACK!</h1>
  <div id="profileArea" class="profile"></div>
  <p id="err" style="color:red; text-align:center;"></p>
</main>

<script>
function esc(str) {
    const d = document.createElement("div");
    d.textContent = str ?? "";
    return d.innerHTML;
}

(async () => {
  try {
    const res  = await fetch("/capstone/api/v1/me.php", { credentials: "same-origin" });
    const json = await res.json();

    if (!json.ok) {
        document.getElementById("err").textContent = json.error;
        return;
    }

const s = json.data;
const imgSrc = s.student_photo ? s.student_photo : "uploads/default.png";

document.getElementById("profileArea").innerHTML = `
    <div class="profile-pic">
        <img id="profileImage" src="${imgSrc}" alt="profile" />
    </div>

    <h1>${esc(s.student_first_name)} ${esc(s.student_last_name)}</h1>
    <br/>
    <div class="label"><h2>STUDENT NUMBER</h2></div>
    <div class="student-box">${esc(String(s.student_IdNum))}</div>
    <br/>
    <div class="label"><h2>EMAIL</h2></div>
    <div class="student-box">${esc(s.student_email)}</div>
    <br/>
    <div class="student-box"><a class="btn btn-danger" href="logout.php">LOGOUT</a></div>
`;
  } catch (err) {
    document.getElementById("err").textContent = "Could not load profile. Try again.";
  }
})();
</script>

<?php include "partials/nav.php"; ?>
<?php include "partials/footer.php"; ?>