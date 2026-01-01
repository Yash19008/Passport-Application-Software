<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

// Fetch call statuses
$statuses = $conn->query("SELECT * FROM call_status ORDER BY id DESC");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Call Status</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Call Status</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between">
                        <h3 class="m-0">Call Status</h3>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">Add New +</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblStatus" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="statusData">
                                <?php
                                $i = 1;
                                while ($row = $statuses->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= date('d-m-Y H:i A', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm editBtn"
                                                data-id="<?= $row['id'] ?>"
                                                data-name="<?= htmlspecialchars($row['name']) ?>">Edit</button>
                                            <button class="btn btn-danger btn-sm deleteBtn"
                                                data-id="<?= $row['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <form id="addForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Call Status</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form id="editForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Call Status</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="action" value="edit">
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'inc/footer.php'; ?>

<script>
    $(function() {

        $('#tblStatus').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#tblStatus_wrapper .col-md-6:eq(0)');

        // Add
        $("#addForm").submit(function(e) {
            e.preventDefault();
            $.post("process_call_status.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status).then(() => location.reload());
            });
        });

        // Edit fill
        $(document).on("click", ".editBtn", function() {
            $("#editId").val($(this).data("id"));
            $("#editName").val($(this).data("name"));
            $("#editModal").modal("show");
        });

        // Update
        $("#editForm").submit(function(e) {
            e.preventDefault();
            $.post("process_call_status.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status).then(() => location.reload());
            });
        });

        // Delete
        $(document).on("click", ".deleteBtn", function() {
            let id = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                text: "This will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("process_call_status.php", {
                        action: "delete",
                        id: id
                    }, function(res) {
                        let data = JSON.parse(res);
                        Swal.fire(data.status, data.message, data.status).then(() => location.reload());
                    });
                }
            });
        });

    });
</script>