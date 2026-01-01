<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$users = $conn->query("SELECT u.*, r.name AS rpo_name FROM user_ids u 
                       LEFT JOIN rpo_offices r ON u.rpo_office_id = r.id 
                       ORDER BY u.id DESC");

$rpo_offices = $conn->query("SELECT id, name FROM rpo_offices ORDER BY name ASC");
?>

<!-- Add Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Manage User IDs</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card shadow mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h3>User IDs</h3>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">Add New +</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblUsers" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>RPO Office</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                while ($row = $users->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                                        <td><?= htmlspecialchars($row['rpo_name']) ?></td>
                                        <td><?= date('d-m-Y H:i A', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm editBtn"
                                                data-id="<?= $row['id'] ?>"
                                                data-userid="<?= htmlspecialchars($row['user_id']) ?>"
                                                data-rpo="<?= $row['rpo_office_id'] ?>">
                                                Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $row['id'] ?>">Delete</button>
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
                    <h5>Add User ID</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>RPO Office</label>
                        <select name="rpo_office_id" class="form-control selectpicker" data-live-search="true" required>
                            <option value="">Select Office</option>
                            <?php while ($r = $rpo_offices->fetch_assoc()) { ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" name="user_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="add">
                    <button class="btn btn-success">Save</button>
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
                    <h5>Edit User ID</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>RPO Office</label>
                        <select name="rpo_office_id" id="editRPO" class="form-control selectpicker" data-live-search="true" required>
                            <option value="">Select Office</option>
                            <?php
                            $rpo_offices = $conn->query("SELECT id, name FROM rpo_offices ORDER BY name ASC");
                            while ($r = $rpo_offices->fetch_assoc()) { ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" name="user_id" id="editUserId" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="action" value="edit">
                    <button class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'inc/footer.php'; ?>

<!-- Bootstrap Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(function() {
        $('.selectpicker').selectpicker();

        $('#tblUsers').DataTable();

        // Add
        $("#addForm").submit(function(e) {
            e.preventDefault();
            $.post("process_user_ids.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status).then(() => location.reload());
            });
        });

        // Edit fill
        $(document).on("click", ".editBtn", function() {
            $("#editId").val($(this).data("id"));
            $("#editUserId").val($(this).data("userid"));
            $("#editRPO").val($(this).data("rpo")).selectpicker('refresh');
            $("#editModal").modal("show");
        });

        // Update
        $("#editForm").submit(function(e) {
            e.preventDefault();
            $.post("process_user_ids.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status).then(() => location.reload());
            });
        });

        // Delete
        $(document).on("click", ".deleteBtn", function() {
            let id = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("process_user_ids.php", {
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