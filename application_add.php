<?php
// application_add.php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

// Fetch existing types and locations
$types = $conn->query("SELECT id, name FROM types ORDER BY name");
$locations = $conn->query("SELECT id, name FROM locations ORDER BY name");
$rpo_offices = $conn->query("SELECT id, name FROM rpo_offices ORDER BY name");
$annexureTypes = $conn->query("SELECT id, name FROM annexure_types ORDER BY name");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Passport Application</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="applications.php">Applications</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <form id="addApplicationForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mob_no" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Office Enquiry Number</label>
                                    <input type="text" name="office_no" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Reference Name</label>
                                    <input type="text" name="ref_name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Whatsapp Number</label>
                                    <input type="text" name="ref_no" class="form-control">
                                </div>
                                <?php
                                $docNames = $conn->query("SELECT id, name FROM document_names ORDER BY name");
                                ?>
                                <div class="form-group">
                                    <label>Document List</label>
                                    <div class="input-group mb-2">
                                        <select name="doc_list[]" class="form-control" multiple required>
                                            <?php while ($row = $docNames->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['name']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="new_doc_name" placeholder="Add new document name">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addDocName">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-4">
                                    <label>Add Remarks</label>
                                    <textarea name="remarks" rows="3" class="form-control" placeholder="Add any remarks here..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type</label>
                                    <div class="input-group">
                                        <select name="type_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Type</option>
                                            <?php while ($row = $types->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <input type="text" class="form-control" id="new_type" placeholder="Add new type">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addType">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>RPO Office</label>
                                    <div class="input-group">
                                        <select name="rpo_office_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select RPO Office</option>
                                            <?php while ($row = $rpo_offices->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <input type="text" class="form-control" id="new_rpo_office" placeholder="Add new office">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addRpoOffice">+</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Location</label>
                                    <div class="input-group">
                                        <select name="location_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Location</option>
                                            <?php while ($row = $locations->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <input type="text" class="form-control" id="new_location" placeholder="Add new location">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addLocation">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>User ID</label>
                                    <select name="user_id" id="user_id" class="form-control selectpicker" data-live-search="true" required>
                                        <option value="">Select User ID</option>
                                        <!-- Options will load dynamically based on RPO Office -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Appointment Date & Time</label>
                                    <input type="datetime-local" id="app_dt" name="app_dt" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Application Status</label>
                                    <select name="application_status" class="form-control selectpicker" required>
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" class="form-control selectpicker" required>
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="paid">Fully Paid</option>
                                        <option value="partial">Partial</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Annexures List</label>
                                    <div class="input-group mb-2">
                                        <select name="annexure_list[]" class="form-control" multiple required>
                                            <?php while ($row = $annexureTypes->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['name']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="new_annexure_name" placeholder="Add new annexure name">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-info" id="addAnnexureName">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4">Upload Documents</h5>
                        <table class="table table-bordered" id="docTable">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>File</th>
                                    <th>Remarks</th>
                                    <th><button type="button" class="btn btn-success btn-sm" id="addDocRow">Add +</button></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="doc_type[]" class="form-control" required></td>
                                    <td><input type="file" name="doc_file[]" class="form-control p-1" required></td>
                                    <td><input type="text" name="doc_remarks[]" class="form-control"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeDocRow">x</button></td>
                                </tr>
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-success">Save Application</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>

<script>
    $(function() {
        $('#addDocRow').click(function() {
            $('#docTable tbody').append(`
            <tr>
                <td><input type="text" name="doc_type[]" class="form-control" required></td>
                <td><input type="file" name="doc_file[]" class="form-control p-1" required></td>
                <td><input type="text" name="doc_remarks[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeDocRow">x</button></td>
            </tr>
            `);
        });

        $(document).on('click', '.removeDocRow', function() {
            $(this).closest('tr').remove();
        });

        $('#addApplicationForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add');

            $.ajax({
                url: 'app_ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            if ($('#app_dt').val() != "") {
                                window.location.href = 'p_applications.php';
                            } else {
                                window.location.href = 'applications.php';
                            }
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        });

        // Add Type
        $('#addType').on('click', function() {
            const newType = $('#new_type').val().trim();
            if (!newType) return;

            $.post('app_ajax.php', {
                action: 'add_type',
                name: newType
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="type_id"]');
                    $select.append(`<option value="${res.id}" selected>${newType}</option>`);
                    $select.selectpicker('destroy').selectpicker();
                    $('#new_type').val('');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        // Add Location
        $('#addLocation').on('click', function() {
            const newLoc = $('#new_location').val().trim();
            if (!newLoc) return;

            $.post('app_ajax.php', {
                action: 'add_location',
                name: newLoc
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="location_id"]');
                    $select.append(`<option value="${res.id}" selected>${newLoc}</option>`);
                    $select.selectpicker('destroy').selectpicker();
                    $('#new_location').val('');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        // Add RPO Office
        $('#addRpoOffice').on('click', function() {
            const newOffice = $('#new_rpo_office').val().trim();
            if (!newOffice) return;

            $.post('app_ajax.php', {
                action: 'add_rpo_office',
                name: newOffice
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="rpo_office_id"]');
                    $select.append(`<option value="${res.id}" selected>${newOffice}</option>`);
                    $select.selectpicker('destroy').selectpicker();
                    $('#new_rpo_office').val('');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        $('#addDocName').on('click', function() {
            const newDoc = $('#new_doc_name').val().trim();
            if (!newDoc) return;

            $.post('app_ajax.php', {
                action: 'add_document_name',
                name: newDoc
            }, function(res) {
                if (res.status === 'success') {
                    $('select[name="doc_list[]"]').append(`<option value="${newDoc}" selected>${newDoc}</option>`);
                    $('#new_doc_name').val('');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        // Fetch User IDs based on selected RPO Office
        $('select[name="rpo_office_id"]').on('change', function() {
            const rpoId = $(this).val();
            if (!rpoId) {
                $('#user_id').html('<option value="">Select User ID</option>').selectpicker('refresh');
                return;
            }
            $.post('app_ajax.php', {
                action: 'get_user_ids',
                rpo_office_id: rpoId
            }, function(res) {
                if (res.status === 'success') {
                    let options = '<option value="">Select User ID</option>';
                    res.data.forEach(function(user) {
                        options += `<option value="${user.id}">${user.user_id} (Pass: ${user.password})</option>`;
                    });
                    $('#user_id').html(options).selectpicker('refresh');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        // Add Annexure Name
        $('#addAnnexureName').on('click', function() {
            const newAnnex = $('#new_annexure_name').val().trim();
            if (!newAnnex) return;
            $.post('app_ajax.php', {
                action: 'add_annexure_name',
                name: newAnnex
            }, function(res) {
                if (res.status === 'success') {
                    $('select[name="annexure_list[]"]').append(`<option value="${newAnnex}" selected>${newAnnex}</option>`);
                    $('#new_annexure_name').val('');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });
    });
</script>