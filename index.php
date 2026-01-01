<?php
include 'inc/auth.php';
include 'inc/db.php';
include 'inc/header.php';
include 'inc/sidebar.php';
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Dashboard</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-12">
          <?php
          // Fetch inquiries with call type "follow-up" and again_call_date as today
          $today = date("Y-m-d");
          $sql = "SELECT 
                            i.*, 
                            c.remarks AS last_call_remarks, 
                            c.call_time AS last_call_time, 
                            c.call_date AS last_call_date, 
                            cs.name AS last_call_status 
                        FROM 
                            (
                                SELECT 
                                    inq_id, 
                                    MAX(call_date) AS max_call_date,
                                    MAX(again_call_date) AS max_again_call_date
                                FROM 
                                    calls 
                                WHERE 
                                    type = 'follow_up' 
                                    AND inq_id NOT IN (
                                        SELECT DISTINCT inq_id
                                        FROM calls
                                        WHERE call_status_id IN (1, 6)
                                    )
                                GROUP BY 
                                    inq_id
                            ) AS latest_calls 
                        INNER JOIN 
                            calls AS c 
                        ON 
                            latest_calls.inq_id = c.inq_id 
                            AND latest_calls.max_call_date = c.call_date 
                            AND latest_calls.max_again_call_date <= '$today' 
                        INNER JOIN 
                            inquiries AS i 
                        ON 
                            c.inq_id = i.id 
                        LEFT JOIN 
                            call_status AS cs 
                        ON 
                            c.call_status_id = cs.id 
                        WHERE 
                            c.call_status_id NOT IN (1, 6);";
          $result = $conn->query($sql);


          $sql2 = "SELECT * FROM inquiries WHERE id NOT IN (SELECT inq_id FROM calls)";
          $oneFollowData = $conn->query($sql2);
          ?>
          <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex" style="justify-content: space-between;align-items: center">
              <h6 class="m-0 font-weight-bold text-primary">Inquiries To Follow Up Today</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="tbl table table-striped table-bordered no-wrap">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>EDIT</th>
                      <th>Date</th>
                      <th>Name</th>
                      <th>Phone</th>
                      <th>Address</th>
                      <th>Last Call Date</th>
                      <th>Last Call Time</th>
                      <th>Last Call Status</th>
                      <th>Last Call Remark</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $i = 1;
                    if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td>
                            <a href="edit-inquiry.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">EDIT</a>
                          </td>
                          <td><?= date("d-m-Y", strtotime($row['created_at'])) ?></td>
                          <td><?= $row['name'] ?></td>
                          <td><?= $row['mobile'] ?></td>
                          <td><?= $row['address'] ?></td>
                          <td><?= $row['last_call_date'] == null ? "N/A" : date("d-m-Y", strtotime($row['last_call_date'])) ?></td>
                          <td><?= $row['last_call_time'] ?? "N/A" ?></td>
                          <td><?= $row['last_call_status'] ?? "N/A" ?></td>
                          <td><?= $row['last_call_remarks'] ?? "N/A" ?></td>
                        </tr>
                      <?php
                      }
                    }

                    if ($oneFollowData->num_rows > 0) {
                      while ($row = $oneFollowData->fetch_assoc()) {
                      ?>
                        <tr>
                          <td><?= $i++ ?></td>
                          <td>
                            <a href="edit-inquiry.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">EDIT</a>
                          </td>
                          <td><?= date("d-m-Y", strtotime($row['created_at'])) ?></td>
                          <td><?= $row['name'] ?></td>
                          <td><?= $row['mobile'] ?></td>
                          <td><?= $row['address'] ?></td>
                          <td><?= $row['last_call_date'] == null ? "N/A" : date("d-m-Y", strtotime($row['last_call_date'])) ?></td>
                          <td><?= $row['last_call_time'] ?? "N/A" ?></td>
                          <td><?= $row['last_call_status'] ?? "N/A" ?></td>
                          <td><?= $row['last_call_remarks'] ?? "N/A" ?></td>
                        </tr>
                    <?php
                      }
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>

<?php
include 'inc/footer.php';
?>

<script>
  $(function() {
    $('.tbl').DataTable({
      "responsive": true,
      "lengthChange": true,
      "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
  });
</script>