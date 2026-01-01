<?php
include 'inc/auth.php';
include 'inc/db.php';
$response = ['success' => false];

// Check if delete request is received
if (isset($_GET['delete']) && isset($_GET['id'])) {
    // Retrieve data
    $id = $_GET['id'];

    // Delete record from database
    $sql = "DELETE FROM inquiries WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Successfully Deleted!");location.href="enquiries.php"</script>';
        exit(); // Add an exit to stop further execution
    } else {
        echo '<script>alert("Error while Deleting!");location.href="enquiries.php"</script>';
        exit(); // Add an exit to stop further execution
    }
}

// Validate incoming data
if (!isset($_POST['name'], $_POST['mobile_no'], $_POST['address'])) {
    $response['error'] = "Missing required fields.";
    echo json_encode($response);
    exit;
}

// Gather data
$id          = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
$name        = trim($_POST['name']);
$mobile      = trim($_POST['mobile_no']);
$office_no   = trim($_POST['office_no']);
$address     = trim($_POST['address']);
$remarks     = trim($_POST['remarks'] ?? '');
$now         = date('Y-m-d H:i:s');

$conn->begin_transaction();

try {
    if ($id) {
        // Update existing inquiry
        $stmt = $conn->prepare("UPDATE inquiries SET name=?, mobile=?, office_no=?, address=?, remarks=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $mobile, $office_no, $address, $remarks, $id);
        $stmt->execute();

        // Clear old follow-up calls
        $conn->query("DELETE FROM calls WHERE inq_id = $id");
        $inq_id = $id;
    } else {
        // Insert new inquiry
        $stmt = $conn->prepare("INSERT INTO inquiries (name, mobile, office_no, address, remarks, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $mobile, $office_no, $address, $remarks, $now);
        $stmt->execute();
        $inq_id = $stmt->insert_id;
    }

    // Handle follow-up calls
    if (!empty($_POST['follow_up']['call_name'] ?? null)) {
        foreach ($_POST['follow_up']['call_name'] as $i => $call_name) {
            $call_name     = trim($call_name);
            $call_status   = intval($_POST['follow_up']['call_status'][$i] ?? 0);
            $call_date     = $_POST['follow_up']['call_date'][$i] ?? null;
            $call_time     = $_POST['follow_up']['call_time'][$i] ?? null;
            $next_call     = $_POST['follow_up']['next_call_date'][$i] ?? null;
            $call_remarks  = trim($_POST['follow_up']['remarks'][$i] ?? '');

            if ($call_name && $call_status) {
                $stmt2 = $conn->prepare("INSERT INTO calls (inq_id, type, call_name, call_status_id, call_date, call_time, again_call_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $type = 'follow_up';
                $stmt2->bind_param("ississss", $inq_id, $type, $call_name, $call_status, $call_date, $call_time, $next_call, $call_remarks);
                $stmt2->execute();
            }
        }
    }

    // ✅ Insert remarks if provided
    if (!empty($remarks)) {
        $rStmt = $conn->prepare("INSERT INTO inq_remarks (inq_id, remark, created_at) VALUES (?, ?, NOW())");
        $rStmt->bind_param("is", $inq_id, $remarks);
        $rStmt->execute();
        $rStmt->close();
    }

    $conn->commit();
    $response['success'] = true;
    $response['action'] = $id ? 'updated' : 'added';
} catch (Exception $e) {
    $conn->rollback();
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
