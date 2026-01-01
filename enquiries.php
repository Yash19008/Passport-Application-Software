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
                    <h1 class="m-0">Manage Enquiries</h1>
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
        <div class="container-fluid">
            <!-- DataTales Example -->

            <div class="card shadow mb-4">
                <div class="card-header">
                    <h3>Filter Inquiries</h3>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="row">
                        <div class="form-group col-md-3">
                            <label for="start_date">From</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d') ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="end_date">To</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d') ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="status">Status:</label>
                            <select class="form-control" name="status" id="status">
                                <option value="">ALL</option>
                                <?php foreach ($callStatus as $status) { ?>
                                    <option value="<?php echo $status["id"]; ?>" <?= isset($_GET['status']) && $_GET['status'] == $status['id'] ? "selected" : "" ?>><?php echo $status["name"]; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3" style="display: flex;align-items: end">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex" style="justify-content: space-between;align-items: center">
                    <a href="new-inquiry.php" class="btn btn-primary">Add New +</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tbl" class="table table-striped table-bordered no-wrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Office No.</th>
                                    <th>Location</th>
                                    <th>Remarks</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Office No.</th>
                                    <th>Location</th>
                                    <th>Remarks</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM inquiries";

                                if (isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                                    $startDate = $_GET['start_date'];
                                    $endDate = $_GET['end_date'];
                                    $sql .= " WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
                                } else {
                                    $sql .= " WHERE DATE(created_at) = CURDATE()";
                                }

                                if (isset($_GET['status']) && $_GET['status'] != "") {
                                    $sql .= " AND id IN (SELECT DISTINCT inq_id FROM calls WHERE call_status_id = '{$_GET['status']}')";
                                }

                                $result = $conn->query($sql);

                                // Check if there are any results
                                if ($result->num_rows > 0) {
                                    $i = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $i++ . "</td>";
                                        echo "<td>" . $row['name'] . "</td>";
                                        echo "<td>" . $row['mobile'] . "</td>";
                                        echo "<td>" . ($row['office_no'] ?? "N/A") . "</td>";
                                        echo "<td>" . $row['address'] . "</td>";
                                        echo "<td>" . $row['remarks'] . "</td>";
                                        echo "<td>" . date("d-m-Y H:i A", strtotime($row['created_at'])) . "</td>";
                                        echo "<td>";
                                        echo "<a class='btn btn-warning btn-sm mr-1 text-dark' style='cursor: pointer' href='edit-inquiry.php?id=" . $row['id'] . "'>Edit</a>";
                                        echo "<a onclick='return confirmDelete()' class='btn btn-danger btn-sm' style='cursor: pointer' href='process_inquiry.php?delete=1&id=" . $row['id'] . "'>Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
</div>
<!-- End of Main Content -->
<?php
include 'inc/footer.php';
?>

<script>
    $(function() {
        $('#tbl').DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>