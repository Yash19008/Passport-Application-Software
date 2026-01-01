<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

$inq_id = $_GET['id'] ?? 0;
$inquiry = null;
$calls = [];

if ($inq_id) {
    $stmt = $conn->prepare("SELECT * FROM inquiries WHERE id = ?");
    $stmt->bind_param("i", $inq_id);
    $stmt->execute();
    $inquiry = $stmt->get_result()->fetch_assoc();

    $stmt_calls = $conn->prepare("SELECT * FROM calls WHERE inq_id = ?");
    $stmt_calls->bind_param("i", $inq_id);
    $stmt_calls->execute();
    $calls = $stmt_calls->get_result()->fetch_all(MYSQLI_ASSOC);

    $callStatus = $conn->query("SELECT * FROM call_status ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
}
?>

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
    <section class="content">
        <div class="container-fluid mt-4">
            <form method="post" id="editInquiryForm">
                <input type="hidden" name="id" value="<?= $inquiry['id'] ?>">
                <div class="card mb-4">
                    <div class="card-header">Edit Inquiry</div>
                    <div class="card-body row">
                        <div class="col-md-3 form-group">
                            <label>Name</label>
                            <input required type="text" class="form-control" name="name" value="<?= $inquiry['name'] ?>">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Mobile</label>
                            <input required type="text" class="form-control" id="mobile_no" name="mobile_no" value="<?= $inquiry['mobile'] ?>">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="office_no">Office No:</label>
                            <input required type="tel" class="form-control" name="office_no" id="office_no" pattern="[0-9]{10}" value="<?= $inquiry['office_no'] ?>">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Location</label>
                            <input required type="text" class="form-control" name="address" value="<?= $inquiry['address'] ?>">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="remarks"><?= $inquiry['remarks'] ?></textarea>
                            <!-- Button to open remarks history modal -->
                            <button type="button" class="btn btn-secondary mb-3" data-toggle="modal" data-target="#remarksModal" id="viewRemarksBtn">
                                View Remarks History
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <div class="d-flex" style="justify-content: space-between;align-items: center">
                            <h6 class="m-0 font-weight-bold text-primary">Follow Up</h6>
                            <div id="callStatusHeader"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table" id="editFollowUpTable">
                            <thead>
                                <tr>
                                    <th>Call Name</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Next Call</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calls as $c): ?>
                                    <tr>
                                        <td><input name="follow_up[call_name][]" class="form-control" value="<?= $c['call_name'] ?>"></td>
                                        <td>
                                            <select name="follow_up[call_status][]" class="form-control callStatus">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($callStatus as $status): ?>
                                                    <option value="<?= $status['id'] ?>" <?= $status['id'] == $c['call_status_id'] ? 'selected' : '' ?>><?= $status['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="date" name="follow_up[call_date][]" value="<?= $c['call_date'] ?>" class="form-control"></td>
                                        <td><input type="time" name="follow_up[call_time][]" value="<?= $c['call_time'] ?>" class="form-control"></td>
                                        <td><input type="date" name="follow_up[next_call_date][]" value="<?= $c['again_call_date'] ?>" class="form-control"></td>
                                        <td><input type="text" name="follow_up[remarks][]" value="<?= $c['remarks'] ?>" class="form-control"></td>
                                        <td><button type="button" class="btn btn-danger removeCall">Remove</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="col-md-4 form-group">
                            <button type="button" class="btn btn-primary" id="addfCall">Add Call</button>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center">
                    <button class="btn btn-success" id="submitEdit">Update Inquiry</button>
                </div>
            </form>
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

<!-- Add Call Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1" role="dialog" aria-labelledby="addStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Call Status</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="text" id="new_status_name" class="form-control" placeholder="Enter status name">
            </div>
            <div class="modal-footer">
                <button type="button" id="saveStatusBtn" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer.php'; ?>

<script>
    $(document).ready(function() {
        let callCount = <?= count($calls) ?>;

        let callStatusOptions = `
            <option value="">-- SELECT --</option>
            <?php foreach ($callStatus as $status) { ?>
                <option value="<?php echo $status["id"]; ?>"><?php echo $status["name"]; ?></option>
            <?php } ?>
        `;

        $("#addfCall").click(function() {
            let row = `<tr>
            <td><input name="follow_up[call_name][]" class="form-control" value="CALL ${++callCount}"></td>
            <td><select required class="form-control callStatus" name="follow_up[call_status][]">${callStatusOptions}</select></td>
            <td><input type="date" name="follow_up[call_date][]" class="form-control"></td>
            <td><input type="time" name="follow_up[call_time][]" class="form-control"></td>
            <td><input type="date" name="follow_up[next_call_date][]" class="form-control"></td>
            <td><input type="text" name="follow_up[remarks][]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger removeCall">Remove</button></td>
        </tr>`;
            $("#editFollowUpTable tbody").append(row);
        });

        $(document).on("click", ".removeCall", function() {
            $(this).closest("tr").remove();
        });

        var mobileNoInput = document.getElementById('mobile_no');

        mobileNoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10); // Remove non-numeric characters and limit to 10 digits
        });

        $("#submitEdit").click(function(e) {
            e.preventDefault();
            let formData = new FormData($("#editInquiryForm")[0]);
            $.ajax({
                url: 'process_inquiry.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    const result = JSON.parse(res);
                    if (result.success) {
                        Swal.fire("Updated!", "Inquiry updated successfully.", "success").then(() => {
                            window.location.href = "enquiries.php";
                        });
                    } else {
                        Swal.fire("Error", result.error || "Failed to update.", "error");
                    }
                }
            });
        });

        // Add Call Status Modal Trigger
        $("#callStatusHeader").append('<button type="button" class="btn btn-sm btn-success ml-2" data-toggle="modal" data-target="#addStatusModal">Add New Call Status</button>');

        $("#saveStatusBtn").on("click", function() {
            let statusName = $("#new_status_name").val().trim();
            if (!statusName) {
                return Swal.fire("Validation", "Please enter a status name", "warning");
            }

            $.post("save_call_status.php", {
                name: statusName
            }, function(res) {
                const result = JSON.parse(res);
                if (result.success && result.insert_id) {
                    Swal.fire("Added!", "New status added.", "success");
                    $("#new_status_name").val('');
                    $('#addStatusModal').modal('hide');

                    // Build new <option>
                    const newOption = `<option value="${result.insert_id}">${statusName}</option>`;
                    callStatusOptions += newOption;

                    // Append new option to all existing selects
                    $(".callStatus").each(function() {
                        $(this).append(newOption);
                    });
                } else {
                    Swal.fire("Error", result.error || "Unable to add.", "error");
                }
            });
        });

        $('#viewRemarksBtn').on('click', function() {
            const inq_id = <?= $inq_id ?>; // Define this PHP variable in your Edit page
            $.post('app_ajax.php', {
                action: 'get_inq_remarks',
                inq_id: inq_id
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
    });
</script>