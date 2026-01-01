<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';

// Fetch call_status from the database
$query = "SELECT * FROM call_status ORDER BY id DESC";
$callStatus = $conn->query($query);
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add New Inquiry</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Enquiries</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <!-- Begin Page Content -->
        <div class="container-fluid">
            <form method="post" action="process_inquiry.php" enctype="multipart/form-data">
                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex" style="justify-content: space-between;align-items: center">
                        <h6 class="m-0 font-weight-bold text-primary">New Inquiry</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="name">Name:</label>
                                <input required type="text" class="form-control" placeholder="Enter Name..." name="name" id="name">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="mobile_no">Mobile No:</label>
                                <input required type="tel" class="form-control" placeholder="Enter Mobile No..." name="mobile_no" id="mobile_no" pattern="[0-9]{10}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="office_no">Office No:</label>
                                <input required type="tel" class="form-control" placeholder="Enter Office No..." name="office_no" id="office_no" pattern="[0-9]{10}">
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="address">Location:</label>
                                <input required type="text" class="form-control" placeholder="Enter Location..." name="address" id="address">
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="remarks">Remarks (Optional):</label>
                                <textarea type="text" class="form-control" placeholder="Enter Remarks..." name="remarks" id="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <div class="d-flex" style="justify-content: space-between;align-items: center">
                            <h6 class="m-0 font-weight-bold text-primary">Follow Up</h6>
                            <div id="callStatusHeader"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Follow-up calls -->
                            <table id="followUpTable" class="table">
                                <!-- Table header -->
                                <thead>
                                    <tr>
                                        <th>Call Number</th>
                                        <th>Call Status</th>
                                        <th>Call Date</th>
                                        <th>Call Time</th>
                                        <th>Remarks</th>
                                        <th>Next Call Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <!-- Table body -->
                                <tbody></tbody>
                            </table>

                            <div class="col-md-4 form-group">
                                <button type="button" class="btn btn-primary" id="addfCall">Add Call</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-md-4 form-group text-center">
                    <button class="btn btn-success w-25" name="add" id="submit_inquiry">Submit</button>
                </div>
            </form>
        </div>
        <!-- /.container-fluid -->
    </section>
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

<!-- End of Main Content -->
<?php
include 'inc/footer.php';
?>
<script>
    $(document).ready(function() {
        var callIndex = 0; // Initialize the demo call index

        let callStatusOptions = `
            <option value="">-- SELECT --</option>
            <?php foreach ($callStatus as $status) { ?>
                <option value="<?php echo $status["id"]; ?>"><?php echo $status["name"]; ?></option>
            <?php } ?>
        `;

        // Function to add new follow-up call row
        $("#addfCall").click(function() {
            var newRow = '<tr class="fCallRow">';
            newRow += '<td>';
            newRow += '<input type="text" name="follow_up[call_name][]" class="form-control callName" value="CALL ' + (++callIndex) + '" placeholder="Enter value. (Eg. Call 1)">';
            newRow += '</td>';
            newRow += '<td><select required class="form-control callStatus" name="follow_up[call_status][]">' + callStatusOptions + '</select></td>';
            newRow += '<td>';
            newRow += '<input type="date" name="follow_up[call_date][]" class="form-control callDate" value="<?= date('Y-m-d') ?>">';
            newRow += '</td>';
            newRow += '<td>';
            newRow += '<input type="time" name="follow_up[call_time][]" class="form-control callTime" value="<?= date('H:i') ?>">';
            newRow += '</td>';
            newRow += '<td>';
            newRow += '<input type="text" name="follow_up[remarks][]" class="form-control" placeholder="Enter Remarks...">';
            newRow += '</td>';
            newRow += '<td>';
            newRow += '<input type="date" name="follow_up[next_call_date][]" class="form-control">';
            newRow += '</td>';
            newRow += '<td>';
            newRow += '<button type="button" class="btn btn-danger removeCall">Remove</button>';
            newRow += '</td>';
            newRow += '</tr>';
            $("#followUpTable tbody").append(newRow);
        });

        // Remove course row
        $(document).on("click", ".removeCall", function() {
            $(this).closest("tr").remove();
        });

        var mobileNoInput = document.getElementById('mobile_no');

        mobileNoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 10); // Remove non-numeric characters and limit to 10 digits
        });

        // Form submission with AJAX
        $("#submit_inquiry").on("click", function(e) {
            e.preventDefault();
            var formData = new FormData($("form")[0]);

            $.ajax({
                url: 'process_inquiry.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    const result = JSON.parse(res);
                    if (result.success) {
                        Swal.fire("Success!", "Inquiry added successfully!", "success").then(() => {
                            window.location.href = "enquiries.php";
                        });
                    } else {
                        Swal.fire("Error", result.error || "Something went wrong!", "error");
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
    });
</script>