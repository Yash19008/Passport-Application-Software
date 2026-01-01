<?php
// applications.php
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
                    <h1 class="m-0">Manage Appointments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Applications</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="applicationsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Application ID</th>
                                        <th>Name</th>
                                        <th>DOB</th>
                                        <th>Mobile</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Appointment Date/Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $now = date('Y-m-d H:i:s');
                                    $result = $conn->query("SELECT a.*, l.name as location, t.name as type FROM applications a LEFT JOIN locations l ON l.id = a.location LEFT JOIN types t ON t.id = a.type WHERE a.status = 'processed' AND a.app_dt > '$now' ORDER BY a.app_dt ASC");
                                    $i = 0;
                                    while ($row = $result->fetch_assoc()) {
                                        $i++;
                                        echo "<tr>
                                            <td>{$i}</td>
                                            <td>{$row['ap_id']}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['dob']}</td>
                                            <td>{$row['mob_no']}</td>
                                            <td>{$row['type']}</td>
                                            <td>{$row['location']}</td>
                                            <td>" . ($row['app_dt'] != "" ? date("d-m-Y H:i A", strtotime($row['app_dt'])) : "N/A") . "</td>
                                            <td>
                                                <button class='btn btn-sm btn-info sendMsg' data-id='{$row['id']}'>Send Message</button>
                                                <a href='application_edit.php?id={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                                                <button class='btn btn-sm btn-danger deleteApp' data-id='{$row['id']}'>Delete</button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
                    <label>Select Message Type:</label><br>
                    <label><input type="radio" name="msgType" value="primary_msg" checked> Application Details</label>
                    <label class="ml-3"><input type="radio" name="msgType" value="reminder_msg"> Reminder</label>
                </div>
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
        $('#applicationsTable').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        $(document).on('click', '.deleteApp', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('app_ajax.php', {
                        action: 'delete',
                        id: id
                    }, function(res) {
                        if (res.status === 'success') {
                            Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    });
                }
            });
        });

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

        $('input[name="msgType"]').change(function() {
            loadMessageTemplate($(this).val());
        });

        function loadMessageTemplate(templateKey) {
            $.ajax({
                url: 'app_ajax.php',
                method: 'POST',
                data: {
                    action: 'get_template',
                    skey: templateKey
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