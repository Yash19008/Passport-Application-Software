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
                    <h1 class="m-0">Manage Passport Applications</h1>
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
                        <div class="card-header">
                            <a href="application_add.php" class="btn btn-primary">Add Application</a>
                        </div>
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
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query("SELECT a.*, l.name as location, t.name as type FROM applications a LEFT JOIN locations l ON l.id = a.location LEFT JOIN types t ON t.id = a.type WHERE a.app_dt IS NULL AND a.status = 'new' ORDER BY a.id DESC");
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
                                            <td>" . ($row['created_at'] != "" ? date("d-m-Y H:i A", strtotime($row['created_at'])) : "N/A") . "</td>
                                            <td>
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
    });
</script>