<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$data = $conn->query("SELECT * FROM office_enquiry_numbers ORDER BY id DESC");
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manage Office Enquiry Numbers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Office Enquiry Numbers</li>
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
                        <h4 class="m-0">Office Enquiry Numbers</h4>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addModal">Add New +</button>
                    </div>
                </div>

                <div class="card-body">
                    <table id="tblEnquiry" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Enquiry Number</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = $data->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['enquiry_number']) ?></td>
                                    <td><?= date('d-m-Y H:i A', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editBtn"
                                            data-id="<?= $row['id'] ?>"
                                            data-number="<?= $row['enquiry_number'] ?>">Edit</button>

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
                    <h5>Add Enquiry Number</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="text" name="enquiry_number"
                        class="form-control"
                        maxlength="11"
                        pattern="[0-9]{11}"
                        placeholder="Enter 11 digit number"
                        required>
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
                    <h5>Edit Enquiry Number</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <input type="text" name="enquiry_number" id="editNumber"
                        class="form-control"
                        maxlength="11"
                        pattern="[0-9]{11}"
                        required>
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

        $('#tblEnquiry').DataTable({
            responsive: true,
            autoWidth: false
        });

        /* ADD */
        $("#addForm").submit(function(e) {
            e.preventDefault();
            $.post("process_office_enquiry_numbers.php", $(this).serialize(), function(res) {
                let data = JSON.parse(res);
                Swal.fire(data.status, data.message, data.status)
                    .then(() => location.reload());
            });
        });

        /* EDIT FILL */
        $(document).on("click", ".editBtn", function() {
            $("#editId").val($(this).data("id"));
            $("#editNumber").val($(this).data("number"));
            $("#editModal").modal("show");
        });

        /* UPDATE */
        $("#editForm").submit(function(e) {
            e.preventDefault();
            $.post("process_office_enquiry_numbers.php", $(this).serialize(), function(res) {
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
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33"
            }).then((r) => {
                if (r.isConfirmed) {
                    $.post("process_office_enquiry_numbers.php", {
                            action: "delete",
                            id: id
                        },
                        function(res) {
                            let data = JSON.parse(res);
                            Swal.fire(data.status, data.message, data.status)
                                .then(() => location.reload());
                        });
                }
            });
        });

    });
</script>