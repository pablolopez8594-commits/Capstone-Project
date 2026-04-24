<?php 
    include "partials/header.php"; 
    if(isset($_GET["error"])){
        $error = trim($_GET["error"]);
        echo "<script>alert('$error');</script>";
    }
?>

<main class="container-fluid">
    <div class="container text-center">
        <form action="registeruser.php" Method="POST" enctype="multipart/form-data">
            <h1>Account Details</h1>
            <div class="row">
                <label for="email">EMAIL:</label>
                <Input type="email" name="email" id="email" required>
            </div>
            <div class="row">
                <label for="first_name">FIRST NAME:</label>
                <Input type="text" name="first_name" id="first_name" required>
            </div>
            <div class="row">
                <label for="last_name">LAST NAME:</label>
                <Input type="text" name="last_name" id="last_name" required>
            </div>
            <div class="row">
                <label for="password">PASSWORD:</label>
                <Input type="password" name="password" id="password" required>
            </div>
            <div class="row">
                <label for="passwordmatch">RE-ENTER PASSWORD:</label>
                <Input type="password" name="passwordmatch" id="passwordmatch" oninput="checkPasswordMatch()" required>
            </div>
            <div class="row">
                <span id="match_message"></span>
            </div>
            <div class="row">
                <label for="profile">CHOOSE A PROFILE PICTURE:</label>
                <Input type="file" name="profilepic" id="profilepic">
            </div>
            <div class="row">
                <label for="program">CHOOSE A PROGRAM:</label>
                <select id="program" name="program">
                    <option disabled selected value="">Loading programs...</option>
                </select>
            </div>
            <div class="row">
                <Input id="submitButton" class="btn btn-success" type="Submit" value="REGISTER NOW" disabled>
            </div>
        </form>
    </div>
</main>
<script>
    function checkPasswordMatch() {
    var password = document.getElementById("password");
    var passwordmatch = document.getElementById("passwordmatch");
    var message = document.getElementById("match_message");
    var submitButton = document.getElementById("submitButton");

    if (password.value === passwordmatch.value) {
        // Passwords match
        message.innerHTML = "Passwords match";
        message.style.color = "green";
        submitButton.disabled = false; // Enable the submit button
        // Set custom validity to an empty string to indicate it's valid for form submission
        confirm_password.setCustomValidity(''); 
    } else {
        // Passwords do not match
        message.innerHTML = "Passwords do not match";
        message.style.color = "red";
        submitButton.disabled = true; // Disable the submit button
        // Set a custom validity message to prevent form submission
        confirm_password.setCustomValidity("Passwords do not match");
    }
    // Also ensure passwords aren't blank
    if(password.value.trim() === '' || confirm_password.value.trim() === '') {
        message.innerHTML = "";
        submitButton.disabled = true;
    }
}
</script>
<?php  include "partials/nav.php";
 include "partials/footer.php"; ?>

<script>
(async () => {
    const select = document.getElementById("program");

    try {
        const res  = await fetch("/capstone/api/v1/programs.php");
        const data = await res.json();

        if (!data.ok || data.data.length === 0) {
            select.innerHTML = "<option disabled selected>No programs available</option>";
            return;
        }

        select.innerHTML = "<option disabled selected value=''>Select a program</option>";
        data.data.forEach(p => {
            const opt = document.createElement("option");
            opt.value       = p.program_ID;
            opt.textContent = p.Program_Name;
            select.appendChild(opt);
        });

    } catch (err) {
        select.innerHTML = "<option disabled selected>Could not load programs</option>";
    }
})();
</script>



