<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Application Status Checker</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Status Check</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table id="respondTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Application ID</th>
                                <th>File Number</th>
                                <th>Name</th>
                                <th>DOB</th>
                                <th>Mobile</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("
                                        SELECT 
                                            a.*, 
                                            l.name AS location, 
                                            t.name AS type,
                                            s.status AS latest_status,
                                            s.last_checked
                                        FROM applications a
                                        LEFT JOIN locations l ON l.id = a.location 
                                        LEFT JOIN types t ON t.id = a.type 
                                        LEFT JOIN (
                                            SELECT s1.*
                                            FROM application_status s1
                                            INNER JOIN (
                                                SELECT app_id, MAX(created_at) AS max_created
                                                FROM application_status
                                                GROUP BY app_id
                                            ) s2 ON s1.app_id = s2.app_id AND s1.created_at = s2.max_created
                                        ) s ON s.app_id = a.id
                                        WHERE a.status = 'pending' AND a.file_no IS NOT NULL 
                                        ORDER BY a.id DESC
                                    ");

                            $i = 0;
                            while ($row = $result->fetch_assoc()) {
                                $i++;
                                $status = $row['latest_status'] ?? 'Not checked';
                                $last_checked = $row['last_checked'] ? date("d/m/y H:i A", strtotime($row['last_checked'])) : 'N/A';
                                $status_display = $status . ($last_checked ? "<br><small class='text-muted'>Last Checked: {$last_checked}</small>" : '');

                                echo "<tr>
                                        <td>{$i}</td>
                                        <td>{$row['ap_id']}</td>
                                        <td>{$row['file_no']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['dob']}</td>
                                        <td>{$row['mob_no']}</td>
                                        <td>{$row['type']}</td>
                                        <td>{$row['location']}</td>
                                        <td class='status_col'>{$status_display}</td>
                                        <td>
                                            <button class='btn btn-sm btn-success closeApp' data-id='{$row['id']}' data-apid='{$row['ap_id']}'>Close</button>
                                            <button class='btn btn-sm btn-success stuckApp' data-id='{$row['id']}' data-apid='{$row['ap_id']}'>Mark as Stuck</button>
                                            <button class='btn btn-sm btn-info checkStatus' data-id='{$row['id']}' data-fileno='{$row['file_no']}' data-dob='{$row['dob']}'>Check Status</button>
                                            <button class='btn btn-sm btn-primary sendMsg' data-id='{$row['id']}'>Send Message</button>
                                        </td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Message:</label>
                    <textarea id="msgEditor" class="form-control summernote"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">Send</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>

<script>
    $(function() {
        $('#respondTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#respondTable_wrapper .col-md-6:eq(0)');

        // Close Application
        $(document).on('click', '.closeApp', function() {
            const id = $(this).data('id');
            const ap_id = $(this).data('apid');
            Swal.fire({
                title: 'Enter Passport Number',
                input: 'text',
                inputLabel: '',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Passport number is required!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('app_ajax.php', {
                        action: 'close',
                        id: id,
                        passport_no: result.value
                    }, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Completed!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        });

        // Mark Application as Stuck
        $(document).on('click', '.stuckApp', function() {
            const id = $(this).data('id');
            const ap_id = $(this).data('apid');

            Swal.fire({
                title: 'Are you sure?',
                text: `Mark application ${ap_id} as Stuck?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, mark as stuck',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('app_ajax.php', {
                        action: 'mark_stuck',
                        id: id
                    }, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Updated!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', res.message || 'Unable to update status.', 'error');
                        }
                    }, 'json');
                }
            });
        });

        // Check Status via Flask API
        $(document).on('click', '.checkStatus', function() {
            const id = $(this).data('id');
            const file_no = $(this).data('fileno');
            const dob = $(this).data('dob');
            const $row = $(this).closest('tr');

            Swal.fire({
                title: 'Checking status...',
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            $.post('app_ajax.php', {
                action: 'fetch_status',
                id: id,
                file_no: file_no,
                dob: dob
            }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Status Updated', 'Latest status has been fetched and saved.', 'success');

                    const now = formatDateTime(); // Example: "7/19/2025, 3:45:12 PM"
                    const formattedStatus = `${res.new_status || 'Updated'}<br><small class='text-muted'>Last Checked: ${now}</small>`;
                    $row.find('td.status_col').html(formattedStatus);
                } else {
                    Swal.fire('Error', res.message || 'Unable to fetch status.', 'error');
                }
            }, 'json');
        });

        function formatDateTime() {
            const now = new Date();

            const day = now.getDate().toString().padStart(2, '0');
            const month = (now.getMonth() + 1).toString().padStart(2, '0'); // still 0-indexed
            const year = String(now.getFullYear()).slice(-2); // last 2 digits

            const hours = now.getHours().toString().padStart(2, '0'); // 24-hour format
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const ampm = now.getHours() >= 12 ? 'PM' : 'AM';

            return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
        }

        $('.summernote').summernote({
            height: 250
        });

        let selectedApp = {};

        $(document).on('click', '.sendMsg', function() {
            const appId = $(this).data('id');

            $.ajax({
                url: 'app_ajax.php',
                method: 'POST',
                data: {
                    action: 'get_application_data',
                    id: appId
                },
                success: function(res) {
                    if (res.status === 'success') {
                        selectedApp = res.data;
                        loadMessageTemplate($('input[name="msgType"]:checked').val());
                        $('#messageModal').modal('show');
                    }
                }
            });
        });

        loadMessageTemplate();

        function loadMessageTemplate() {
            $.ajax({
                url: 'app_ajax.php',
                method: 'POST',
                data: {
                    action: 'get_template',
                    skey: 'status_msg'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        let template = res.value;

                        let processedApp = {
                            ...selectedApp
                        }; // shallow copy

                        if (processedApp.doc_list) {
                            const docs = processedApp.doc_list.split(',').map(item => `<li>${item.trim()}</li>`).join('');
                            processedApp.doc_list = `<ul>${docs}</ul>`;
                        }

                        // Clear the editor before inserting new content
                        $('#msgEditor').summernote('code', '');

                        const replaced = template.replace(/{{(.*?)}}/g, (match, key) => processedApp[key] || '');
                        $('#msgEditor').summernote('code', replaced);
                    }
                }
            });
        }
    });
</script>