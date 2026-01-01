<?php
// application_edit.php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$id = $_GET['id'] ?? 0;
$app = $conn->query("SELECT * FROM applications WHERE id = $id")->fetch_assoc();
$documents = $conn->query("SELECT * FROM documents WHERE app_id = $id");

// Fetch dropdown data
$types = $conn->query("SELECT id, name FROM types ORDER BY name");
$locations = $conn->query("SELECT id, name FROM locations ORDER BY name");
$rpo_offices = $conn->query("SELECT id, name FROM rpo_offices ORDER BY name");
$annexureTypes = $conn->query("SELECT id, name FROM annexure_types ORDER BY name");
$docNames = $conn->query("SELECT id, name FROM document_names ORDER BY name");

// Selected multi-select values
$selectedDocs = array_map('trim', explode(',', $app['doc_list'] ?? ''));
$selectedAnnex = array_map('trim', explode(',', $app['annexure_list'] ?? ''));
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/css/bootstrap-select.min.css">

<style>
    .timeline {
        list-style: none;
        padding-left: 0;
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        width: 2px;
        background: #dee2e6;
        top: 0;
        bottom: 0;
    }

    .timeline li {
        position: relative;
        margin-bottom: 20px;
        padding-left: 60px;
    }

    .timeline li::before {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        background: #007bff;
        border-radius: 50%;
        left: 23px;
        top: 5px;
    }

    .timeline .timestamp {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Passport Application</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="applications.php">Applications</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <form id="editApplicationForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= $app['name'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= $app['dob'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mob_no" class="form-control" value="<?= $app['mob_no'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Office Enquiry Number</label>
                                    <input type="text" name="office_no" class="form-control" value="<?= $app['office_no'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Reference Name</label>
                                    <input type="text" name="ref_name" class="form-control" value="<?= $app['ref_name'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Whatsapp Number</label>
                                    <input type="text" name="ref_no" class="form-control" value="<?= $app['ref_no'] ?>">
                                </div>

                                <div class="form-group">
                                    <label>Document List</label>
                                    <div class="input-group mb-2">
                                        <select name="doc_list[]" class="form-control" multiple required>
                                            <?php while ($row = $docNames->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['name']) ?>" <?= in_array($row['name'], $selectedDocs) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </option>
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
                                    <textarea name="remarks" rows="3" class="form-control"></textarea>
                                    <!-- Button to open remarks history modal -->
                                    <button type="button" class="btn btn-secondary mb-3" data-toggle="modal" data-target="#remarksModal" id="viewRemarksBtn">
                                        View Remarks History
                                    </button>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type</label>
                                    <div class="input-group">
                                        <select name="type_id" class="form-control selectpicker" data-live-search="true" required>
                                            <option value="">Select Type</option>
                                            <?php while ($row = $types->fetch_assoc()): ?>
                                                <option value="<?= $row['id'] ?>" <?= $app['type'] == $row['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </option>
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
                                                <option value="<?= $row['id'] ?>" <?= $app['rpo_office_id'] == $row['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </option>
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
                                                <option value="<?= $row['id'] ?>" <?= $app['location'] == $row['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </option>
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
                                    <select name="user_id" id="user_id" class="form-control selectpicker" data-live-search="true" required></select>
                                </div>

                                <div class="form-group">
                                    <label>Appointment Date & Time</label>
                                    <input type="datetime-local" id="app_dt" name="app_dt" class="form-control" value="<?= $app['app_dt'] ? date('Y-m-d\TH:i', strtotime($app['app_dt'])) : '' ?>">
                                </div>

                                <div class="form-group">
                                    <label>Application Status</label>
                                    <select name="application_status" class="form-control selectpicker" required>
                                        <option value="Submitted" <?= $app['application_status'] == 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                        <option value="Draft" <?= $app['application_status'] == 'Draft' ? 'selected' : '' ?>>Draft</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <select name="payment_status" class="form-control selectpicker" required>
                                        <option value="Fully Paid" <?= $app['payment_status'] == 'Fully Paid' ? 'selected' : '' ?>>Fully Paid</option>
                                        <option value="Partial" <?= $app['payment_status'] == 'Partial' ? 'selected' : '' ?>>Partial</option>
                                        <option value="Pending" <?= $app['payment_status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Annexures List</label>
                                    <div class="input-group mb-2">
                                        <select name="annexure_list[]" class="form-control" multiple required>
                                            <?php while ($row = $annexureTypes->fetch_assoc()): ?>
                                                <option value="<?= htmlspecialchars($row['name']) ?>" <?= in_array($row['name'], $selectedAnnex) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </option>
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

                        <h5 class="mt-4">Documents</h5>
                        <table class="table table-bordered" id="docTable">
                            <thead>
                                <tr>
                                    <th>Document Name</th>
                                    <th>File</th>
                                    <th>Remarks</th>
                                    <th><button type="button" class="btn btn-success btn-sm" id="addDocRow">Add +</button></th>
                                </tr>
                            </thead>
                            <tbody id="newDocuments">
                                <?php while ($doc = $documents->fetch_assoc()): ?>
                                    <tr data-id="<?= $doc['id'] ?>">
                                        <td><?= $doc['type'] ?></td>
                                        <td><a href="uploads/<?= $doc['file'] ?>" class="btn btn-sm btn-primary" target="_blank">View</a></td>
                                        <td><?= $doc['remarks'] ?></td>
                                        <td><button type="button" class="btn btn-danger btn-sm deleteDoc">x</button></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <button type="submit" class="btn btn-success">Update Application</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Remarks History Modal -->
<div class="modal fade" id="remarksModal" tabindex="-1" role="dialog" aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remarksModalLabel">Remarks History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="timeline" id="remarksTimeline">
                    <!-- Timeline items will be injected here -->
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta3/js/bootstrap-select.min.js"></script>
<script>
    $(function() {
        const selectedUserId = <?= json_encode($app['user_id']) ?>;

        $('#addDocRow').click(function() {
            $('#newDocuments').append(`
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

        $(document).on('click', '.deleteDoc', function() {
            const row = $(this).closest('tr');
            const docId = row.data('id');
            Swal.fire({
                    title: 'Delete this document?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(result => {
                    if (result.isConfirmed) {
                        $.post('app_ajax.php', {
                            action: 'delete_doc',
                            doc_id: docId
                        }, function(response) {
                            if (response.status === 'success') {
                                row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        }, 'json');
                    }
                });
        });

        $('#editApplicationForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update');

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
                },
                dataType: 'json'
            });
        });

        $('#viewRemarksBtn').on('click', function() {
            const appId = <?= $id ?>; // Define this PHP variable in your Edit page
            $.post('app_ajax.php', {
                action: 'get_remarks',
                app_id: appId
            }, function(res) {
                if (res.status === 'success') {
                    let html = '';
                    res.remarks.forEach(r => {
                        html += `
                    <li>
                        <div><strong>${r.remark}</strong></div>
                        <div class="timestamp">${r.created_at}</div>
                    </li>
                `;
                    });
                    $('#remarksTimeline').html(html || '<li>No remarks found.</li>');
                } else {
                    $('#remarksTimeline').html('<li>Error fetching remarks.</li>');
                }
            }, 'json');
        });

        $('#addType').click(function() {
            const newType = $('#new_type').val().trim();
            if (!newType) return;

            $.post('app_ajax.php', {
                action: 'add_type',
                name: newType
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="type_id"]');

                    // Destroy and rebuild selectpicker
                    $select.selectpicker('destroy');
                    $select.append(`<option value="${res.id}" selected>${newType}</option>`);
                    $select.selectpicker();

                    $('#new_type').val('');
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }, 'json');
        });

        $('#addLocation').click(function() {
            const newLoc = $('#new_location').val().trim();
            if (!newLoc) return;

            $.post('app_ajax.php', {
                action: 'add_location',
                name: newLoc
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="location_id"]');

                    $select.selectpicker('destroy');
                    $select.append(`<option value="${res.id}" selected>${newLoc}</option>`);
                    $select.selectpicker();

                    $('#new_location').val('');
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }, 'json');
        });

        $('#addRpoOffice').click(function() {
            const newOffice = $('#new_rpo_office').val().trim();
            if (!newOffice) return;

            $.post('app_ajax.php', {
                action: 'add_rpo_office',
                name: newOffice
            }, function(res) {
                if (res.status === 'success') {
                    const $select = $('select[name="rpo_office_id"]');

                    $select.selectpicker('destroy');
                    $select.append(`<option value="${res.id}" selected>${newOffice}</option>`);
                    $select.selectpicker();

                    $('#new_rpo_office').val('');
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }
            }, 'json');
        });

        $('#addDocName').click(function() {
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

        $('#addAnnexureName').click(function() {
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

        $('select[name="rpo_office_id"]').change(function() {
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
                        const selected = (user.id == selectedUserId) ? 'selected' : '';
                        options += `<option value="${user.id}" ${selected}>${user.user_id} (Pass: ${user.password})</option>`;
                    });
                    $('#user_id').html(options).selectpicker('refresh');
                } else {
                    alert(res.message);
                }
            }, 'json');
        });

        $('select[name="rpo_office_id"]').trigger('change');
    });
</script>