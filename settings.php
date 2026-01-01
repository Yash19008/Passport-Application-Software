<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$settings = [];
$result = $conn->query("SELECT skey, value FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['skey']] = $row['value'];
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Message Settings</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="post">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Available placeholders:</strong><br>
                            {{name}}, {{ap_id}}, {{dob}}, {{mob_no}}, {{ref_name}}, {{ref_no}}, {{type}}, {{location}}, {{user_id}}, {{password}}, {{app_dt}}, {{doc_list}}, {{latest_app_status}}
                        </div>
                        <div class="form-group">
                            <label>Primary Message</label>
                            <textarea name="primary_msg" class="form-control summernote"><?= htmlspecialchars($settings['primary_msg'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Reminder Message</label>
                            <textarea name="reminder_msg" class="form-control summernote"><?= htmlspecialchars($settings['reminder_msg'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Status Message</label>
                            <textarea name="status_msg" class="form-control summernote"><?= htmlspecialchars($settings['status_msg'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>

<!-- Summernote Scripts -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primary = $conn->real_escape_string($_POST['primary_msg']);
    $reminder = $conn->real_escape_string($_POST['reminder_msg']);
    $status = $conn->real_escape_string($_POST['status_msg']);

    $conn->query("UPDATE settings SET value = '$primary' WHERE skey = 'primary_msg'");
    $conn->query("UPDATE settings SET value = '$reminder' WHERE skey = 'reminder_msg'");
    $conn->query("UPDATE settings SET value = '$status' WHERE skey = 'status_msg'");

    echo "
    <script>
        Swal.fire('Updated', 'Settings saved successfully!', 'success').then(() => {
            window.location.href = 'settings.php';
        });
    </script>";
}
?>

<script>
    $(function() {
        $('.summernote').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    });
</script>