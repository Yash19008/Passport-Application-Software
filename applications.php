<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>RPO Wise Passport Applications</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="application_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Application
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <?php
            $rpoQuery = "SELECT * FROM rpo_offices ORDER BY name ASC";
            $rpoResult = $conn->query($rpoQuery);
            $tableIndex = 0;

            while ($rpo = $rpoResult->fetch_assoc()) {
                $tableIndex++;

                // Count applications for this RPO
                $countRes = $conn->query("
                    SELECT COUNT(*) AS total 
                    FROM applications 
                    WHERE rpo_office_id = {$rpo['id']}
                ");
                $countRow = $countRes->fetch_assoc();
            ?>

                <!-- RPO CARD -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php echo $rpo['name']; ?>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light">
                                <?php echo $countRow['total']; ?> / 20 Applications
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="rpoTable<?php echo $tableIndex; ?>"
                            class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Application ID</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Mobile</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Application Status</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $appQuery = "
                                    SELECT 
                                        a.*,
                                        l.name AS location_name,
                                        t.name AS type_name
                                    FROM applications a
                                    LEFT JOIN locations l ON l.id = a.location
                                    LEFT JOIN types t ON t.id = a.type
                                    WHERE a.rpo_office_id = {$rpo['id']}
                                    ORDER BY a.id DESC
                                ";
                                $apps = $conn->query($appQuery);
                                $i = 0;

                                while ($row = $apps->fetch_assoc()) {
                                    $i++;

                                    $appStatus = ($row['application_status'] == "submitted")
                                        ? 'Submitted'
                                        : 'Draft';
                                ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $row['ap_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['dob']; ?></td>
                                        <td><?php echo $row['mob_no']; ?></td>
                                        <td><?php echo $row['type_name']; ?></td>
                                        <td><?php echo $row['location_name']; ?></td>

                                        <td>
                                            <span class="badge badge-<?php echo ($appStatus == 'Submitted') ? 'success' : 'secondary'; ?>">
                                                <?php echo $appStatus; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge badge-info">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($row['status'] === 'new') { ?>
                                                <a href="application_edit.php?id=<?php echo $row['id']; ?>"
                                                    class="btn btn-sm btn-primary">
                                                    Fill Application
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted">N/A</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php } ?>

        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>

<script>
    $(function() {
        <?php for ($i = 1; $i <= $tableIndex; $i++) { ?>
            $('#rpoTable<?php echo $i; ?>').DataTable({
                    responsive: true,
                    lengthChange: true,
                    autoWidth: false,
                    pageLength: 10,
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
                }).buttons().container()
                .appendTo('#rpoTable<?php echo $i; ?>_wrapper .col-md-6:eq(0)');
        <?php } ?>
    });
</script>