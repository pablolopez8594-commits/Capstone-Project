<?php
include "partials/header.php";
require_once "partials/auth_guard.php";
$search_term = trim($_GET["search"] ?? "");
$results = [];
$error_msg = "";


$scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$host = $_SERVER["HTTP_HOST"];

$apiUrl = $scheme . "://" . $host . "/capstone/api/v1/staff.php";
if ($search_term !== "") {
    $apiUrl .= "?q=" . urlencode($search_term);
}

try {

    $context = stream_context_create([
        "http" => [
            "ignore_errors" => true,
            "timeout" => 5
        ]
    ]);

    $json = @file_get_contents($apiUrl, false, $context);


    $statusLine = $http_response_header[0] ?? "";
    preg_match('~HTTP/\S+\s(\d{3})~', $statusLine, $m);
    $httpStatus = (int)($m[1] ?? 0);

    if ($json === false) {
        throw new Exception("Could not reach API endpoint: " . $apiUrl);
    }


    $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

    $data = json_decode($json, true);

    if (!is_array($data)) {
        throw new Exception("API returned non-JSON (HTTP $httpStatus): " . substr($json, 0, 200));
    }

    if ($httpStatus >= 400 || !($data["ok"] ?? false)) {
        $err = $data["error"] ?? "API error";
        $details = $data["details"] ?? "";
        throw new Exception($details !== "" ? "$err — $details" : $err);
    }

    $results = $data["data"] ?? [];

} catch (Throwable $e) {
    $error_msg = $e->getMessage();
}
?>

<main class="container">
    <h2>Staff Directory</h2>

    <div class="search-container">
        <form action="directory.php" method="GET">
            <input type="text"
                   name="search"
                   class="search-input"
                   placeholder="Search by Name or Office..."
                   value="<?php echo htmlspecialchars($search_term); ?>">

            <button type="submit" class="search-btn">Search</button>

            <?php if (!empty($search_term)) { ?>
                <a href="directory.php" class="clear-link">Clear Filter</a>
            <?php } ?>
        </form>
    </div>

    <?php if ($error_msg !== "") { ?>
        <p style="color:red; text-align:center;">
            Error fetching directory: <?php echo htmlspecialchars($error_msg); ?>
        </p>
    <?php } ?>

    <?php if (count($results) > 0) { ?>
        <div style="overflow-x:auto;">
            <table class="directory-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Program/Course</th>
                        <th>Email</th>
                        <th>Office</th>
                        <th>Availability</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row["staff_name"]); ?></strong></td>
                            <td>
                                <?php
                                    $courses = $row["courses"] ?? [];
                                    if (is_array($courses) && count($courses) > 0) {
                                        echo htmlspecialchars(implode(", ", $courses));
                                    } else {
                                        echo "<span style='color:#999; font-style:italic;'>No classes assigned</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($row["staff_email"]); ?>">
                                    <?php echo htmlspecialchars($row["staff_email"]); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($row["office_num"]); ?></td>
                            <td>
                                <?php
                                    $open_time = date("g:i A", strtotime($row["office_open"]));
                                    $close_time = date("g:i A", strtotime($row["office_close"]));
                                    echo htmlspecialchars($open_time . " - " . $close_time);
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div style="text-align: center; margin-top: 50px;">
            <h3>No staff members found matching "<?php echo htmlspecialchars($search_term); ?>"</h3>
            <p><a href="directory.php">View all staff</a></p>
        </div>
    <?php } ?>
</main>

<?php include "partials/nav.php"; ?>
<?php include "partials/footer.php"; ?>