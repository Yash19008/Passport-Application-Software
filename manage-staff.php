<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$staff = $conn->query("SELECT * FROM staff ORDER BY id DESC");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Staff Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Staff</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between">
                        <h3 class="m-0">Manage Staff</h3>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal">Add New +</button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="tblStaff" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = $staff->fetch_assoc()) { ?>
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
    </section>
</div>

<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <form id="addForm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="text" name="name" class="form-control" placeholder="Enter staff name" required>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form id="editForm">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="text" name="name" id="editName" class="form-control" required>
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

        $('#tblStaff').DataTable({
            responsive: true,
            autoWidth: false
        });

        /* ADD */
        $("#addForm").submit(function(e) {
            e.preventDefault();
            $.post("process_staff.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status)
                    .then(() => location.reload());
            });
        });

        /* FILL EDIT */
        $(document).on("click", ".editBtn", function() {
            $("#editId").val($(this).data("id"));
            $("#editName").val($(this).data("name"));
            $("#editModal").modal("show");
        });

        /* UPDATE */
        $("#editForm").submit(function(e) {
            e.preventDefault();
            $.post("process_staff.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status)
                    .then(() => location.reload());
            });
        });

        /* DELETE */
        $(document).on("click", ".deleteBtn", function() {
            let id = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                text: "This will be deleted permanently!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("process_staff.php", {
                        action: "delete",
                        id: id
                    }, function(res) {
                        let data = JSON.parse(res);
                        Swal.fire(data.status, data.message, data.status)
                            .then(() => location.reload());
                    });
                }
            });
        });

    });
</script>