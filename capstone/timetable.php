<?php
require_once "partials/auth_guard.php";
include "partials/header.php";
?>

<main class="container">
  <h2>My Timetable</h2>
  <p id="err" style="color:red; text-align:center;"></p>
  <div id="timetableArea"></div>
</main>

<script>
const DAYS = ["Mon", "Tue", "Wed", "Thu", "Fri"];

function esc(str) {
    const d = document.createElement("div");
    d.textContent = str ?? "";
    return d.innerHTML;
}

function formatTime(timeStr) {
    const [h, m] = timeStr.split(":");
    const date = new Date();
    date.setHours(parseInt(h), parseInt(m));
    return date.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit" });
}

function buildTable(items) {
    if (items.length === 0) {
        return `<p style="color:#777;">No classes.</p>`;
    }

    const rows = items.map(it => `
        <tr>
            <td>${esc(formatTime(it.start_time))} - ${esc(formatTime(it.end_time))}</td>
            <td>${esc(it.course_name)}</td>
            <td>${esc(it.classroom_num)}</td>
            <td>${esc(it.staff_name)}</td>
        </tr>
    `).join("");

    return `
        <table class="directory-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Course</th>
                    <th>Room</th>
                    <th>Instructor</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    `;
}

(async () => {
    try {
        const res  = await fetch("/capstone/api/v1/me_timetable.php", { credentials: "same-origin" });
        const data = await res.json();

        if (!data.ok) {
            document.getElementById("err").textContent = data.error ?? "Could not load timetable.";
            return;
        }

        // Agrupar por día
        const byDay = { Mon: [], Tue: [], Wed: [], Thu: [], Fri: [] };
        data.data.forEach(row => {
            const day = row.day_of_week;
            if (byDay[day]) byDay[day].push(row);
        });

        // Construir HTML
        const html = DAYS.map(day => `
            <h3>${day}</h3>
            ${buildTable(byDay[day])}
        `).join("");

        document.getElementById("timetableArea").innerHTML = html;

    } catch (err) {
        document.getElementById("err").textContent = "Could not connect to server. Try again.";
    }
})();
</script>

<?php include "partials/nav.php"; ?>
<?php include "partials/footer.php"; ?>
