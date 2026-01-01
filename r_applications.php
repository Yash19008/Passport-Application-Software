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
                    <h1 class="m-0">Respond to Applications</h1>
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
            <div class="card">
                <div class="card-body">
                    <table id="respondTable" class="table table-bordered table-striped">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $now = date('Y-m-d H:i:s');
                            $result = $conn->query("SELECT a.*, l.name AS location, t.name AS type FROM applications a 
                                LEFT JOIN locations l ON l.id = a.location 
                                LEFT JOIN types t ON t.id = a.type 
                                WHERE a.app_dt < '$now' AND (a.status = 'processed' OR a.status = 'rescheduled') ORDER BY a.app_dt DESC");
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
                                    <td>" . date("d-m-Y H:i A", strtotime($row['app_dt'])) . "</td>
                                    <td>
                                    <button class='btn btn-sm btn-secondary fileNoBtn' data-id='{$row['id']}'>Enter File Number</button>
                                    <button class='btn btn-sm btn-primary rescheduleBtn' data-id='{$row['id']}'>Re-schedule</button>
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
    </section>
</div>

<!-- File Number Modal -->
<div class="modal fade" id="fileNoModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="fileNoForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enter File Number</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="file_app_id" id="file_app_id">
                    <div class="form-group">
                        <label>File Number:</label>
                        <input type="text" name="file_no" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="rescheduleForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Appointment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="res_app_id" id="res_app_id">
                    <div class="form-group">
                        <label>New Date & Time:</label>
                        <input type="datetime-local" name="new_app_dt" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <button class="btn btn-secondary" data-dismiss="modal" type="button">Cancel</button>
                </div>
            </div>
        </form>
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

        // Delete application
        $(document).on('click', '.deleteApp', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'This application will be deleted!',
                icon: 'warning',
                showCancelButton: true,
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

        // File Number
        $(document).on('click', '.fileNoBtn', function() {
            const appId = $(this).data('id');
            $('#file_app_id').val(appId);
            $('#fileNoModal').modal('show');
        });

        $('#fileNoForm').submit(function(e) {
            e.preventDefault();
            $.post('app_ajax.php', {
                action: 'update_file_no',
                id: $('#file_app_id').val(),
                file_no: $(this).find('[name="file_no"]').val()
            }, function(res) {
                if (res.status === 'success') {
                    $('#fileNoModal').modal('hide');
                    Swal.fire('Updated!', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            });
        });

        // Reschedule
        $(document).on('click', '.rescheduleBtn', function() {
            const appId = $(this).data('id');
            $('#res_app_id').val(appId);
            $('#rescheduleModal').modal('show');
        });

        $('#rescheduleForm').submit(function(e) {
            e.preventDefault();
            $.post('app_ajax.php', {
                action: 'reschedule',
                id: $('#res_app_id').val(),
                new_dt: $(this).find('[name="new_app_dt"]').val()
            }, function(res) {
                if (res.status === 'success') {
                    $('#rescheduleModal').modal('hide');
                    Swal.fire('Rescheduled!', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            });
        });
    });
</script>