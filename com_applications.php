<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Completed Applications</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Applications</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table id="respondTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Application ID</th>
                                <th>Passport Number</th>
                                <th>Name</th>
                                <th>DOB</th>
                                <th>Mobile</th>
                                <th>Type</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT a.*, l.name as location, t.name as type FROM applications a 
                            LEFT JOIN locations l ON l.id = a.location 
                            LEFT JOIN types t ON t.id = a.type 
                            WHERE a.status = 'completed' ORDER BY a.updated_at ASC");

                            $i = 0;
                            while ($row = $result->fetch_assoc()) {
                                $i++;

                                echo "<tr>
                                        <td>{$i}</td>
                                        <td>{$row['ap_id']}</td>
                                        <td>{$row['passport_no']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['dob']}</td>
                                        <td>{$row['mob_no']}</td>
                                        <td>{$row['type']}</td>
                                        <td>{$row['location']}</td>
                                    </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>

<script>
    $(function() {
        $('#respondTable').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print"]
        }).buttons().container().appendTo('#respondTable_wrapper .col-md-6:eq(0)');
    });
</script>