<?php
// app_ajax.php
include 'inc/db.php';

header("Content-Type: application/json");

function respond($status, $message, $extra = [])
{
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name']);
        $dob = $_POST['dob'];
        $mob_no = trim($_POST['mob_no']);
        $office_no = trim($_POST['office_no']);
        $ref_name = trim($_POST['ref_name']);
        $ref_no = trim($_POST['ref_no']);
        $type_id = (int)$_POST['type_id'];
        $location_id = (int)$_POST['location_id'];
        $rpo_office_id = (int)$_POST['rpo_office_id'];
        $user_id = (int)$_POST['user_id'];
        $app_dt = !empty($_POST['app_dt']) ? $_POST['app_dt'] : null;

        $doc_list_array = $_POST['doc_list'] ?? [];
        $doc_list = implode(',', array_map('trim', $doc_list_array));

        $annexure_list_array = $_POST['annexure_list'] ?? [];
        $annexure_list = implode(',', array_map('trim', $annexure_list_array));

        $application_status = trim($_POST['application_status']);
        $payment_status = trim($_POST['payment_status']);

        $remark = trim($_POST['remarks']);

        if (!$name || !$dob || !$mob_no || !$type_id || !$location_id || !$rpo_office_id) {
            respond('error', 'Required fields are missing');
        }

        $ap_id = 'GLOBAL_' . rand(100000, 999999) . "_" . preg_replace("/[^0-9]/", "", $mob_no);

        $status = $app_dt != null ? "processed" : "new";

        $stmt = $conn->prepare("INSERT INTO applications (name, ap_id, dob, mob_no, office_no, ref_name, ref_no, type, location, rpo_office_id, user_id, app_dt, doc_list, annexure_list, status, payment_status, application_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssssssssssss", $name, $ap_id, $dob, $mob_no, $office_no, $ref_name, $ref_no, $type_id, $location_id, $rpo_office_id, $user_id, $app_dt, $doc_list, $annexure_list, $status, $payment_status, $application_status);

        if ($stmt->execute()) {
            $app_id = $stmt->insert_id;
            $stmt->close();

            if ($remark) {
                $rStmt = $conn->prepare("INSERT INTO remarks (app_id, remark, created_at) VALUES (?, ?, NOW())");
                $rStmt->bind_param("is", $app_id, $remark);
                $rStmt->execute();
                $rStmt->close();
            }

            // Handle file uploads
            if (!empty($_FILES['doc_file']['name'][0])) {
                foreach ($_FILES['doc_file']['name'] as $i => $filename) {
                    $doc_type = $_POST['doc_type'][$i] ?? '';
                    $doc_remarks = $_POST['doc_remarks'][$i] ?? '';
                    $tmp = $_FILES['doc_file']['tmp_name'][$i];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $safeName = uniqid() . "." . $ext;

                    if (move_uploaded_file($tmp, "uploads/" . $safeName)) {
                        $conn->query("INSERT INTO documents (app_id, type, file, remarks, created_at) VALUES ('$app_id', '$doc_type', '$safeName', '$doc_remarks', NOW())");
                    }
                }
            }

            respond('success', 'Application added successfully.');
        } else {
            respond('error', 'Failed to insert application.');
        }
    } elseif ($action === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $dob = $_POST['dob'];
        $mob_no = trim($_POST['mob_no']);
        $office_no = trim($_POST['office_no']);
        $ref_name = trim($_POST['ref_name']);
        $ref_no = trim($_POST['ref_no']);
        $type_id = (int)$_POST['type_id'];
        $location_id = (int)$_POST['location_id'];
        $rpo_office_id = (int)$_POST['rpo_office_id'];
        $user_id = (int)$_POST['user_id'];
        $app_dt = !empty($_POST['app_dt']) ? $_POST['app_dt'] : null;

        $doc_list_array = $_POST['doc_list'] ?? [];
        $doc_list = implode(',', array_map('trim', $doc_list_array));

        $annexure_list_array = $_POST['annexure_list'] ?? [];
        $annexure_list = implode(',', array_map('trim', $annexure_list_array));

        $application_status = trim($_POST['application_status']);
        $payment_status = trim($_POST['payment_status']);

        $remarks = trim($_POST['remarks']); // New remarks from form

        if (!$id || !$name || !$dob || !$mob_no || !$type_id || !$location_id || !$rpo_office_id) {
            respond('error', 'Required fields are missing');
        }

        $status = $app_dt != null ? "processed" : "new";

        $stmt = $conn->prepare("
        UPDATE applications 
        SET name=?, dob=?, mob_no=?, office_no=?, ref_name=?, ref_no=?, type=?, location=?, rpo_office_id=?, user_id=?, app_dt=?, doc_list=?, annexure_list=?, status=?, payment_status=?, application_status=?, updated_at=NOW() 
        WHERE id=?
    ");

        $stmt->bind_param(
            "ssssssssissssssssi",
            $name,
            $dob,
            $mob_no,
            $office_no,
            $ref_name,
            $ref_no,
            $type_id,
            $location_id,
            $rpo_office_id,
            $user_id,
            $app_dt,
            $doc_list,
            $annexure_list,
            $status,
            $payment_status,
            $application_status,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();

            // ✅ Insert remarks if provided
            if (!empty($remarks)) {
                $rStmt = $conn->prepare("INSERT INTO remarks (app_id, remark, created_at) VALUES (?, ?, NOW())");
                $rStmt->bind_param("is", $id, $remarks);
                $rStmt->execute();
                $rStmt->close();
            }

            // ✅ Handle new document uploads
            if (!empty($_FILES['doc_file']['name'][0])) {
                foreach ($_FILES['doc_file']['name'] as $i => $filename) {
                    $doc_type = $_POST['doc_type'][$i] ?? '';
                    $doc_remarks = $_POST['doc_remarks'][$i] ?? '';
                    $tmp = $_FILES['doc_file']['tmp_name'][$i];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $safeName = uniqid() . "." . $ext;

                    if (move_uploaded_file($tmp, "uploads/" . $safeName)) {
                        $conn->query("INSERT INTO documents (app_id, type, file, remarks, created_at) VALUES ('$id', '$doc_type', '$safeName', '$doc_remarks', NOW())");
                    }
                }
            }

            respond('success', 'Application updated successfully.');
        } else {
            respond('error', 'Failed to update application.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        if (!$id) respond('error', 'Invalid ID');

        // Delete documents
        $docs = $conn->query("SELECT file FROM documents WHERE app_id = $id");
        while ($doc = $docs->fetch_assoc()) {
            $file = 'uploads/' . $doc['file'];
            if (file_exists($file)) unlink($file);
        }
        $conn->query("DELETE FROM documents WHERE app_id = $id");
        $conn->query("DELETE FROM applications WHERE id = $id");

        respond('success', 'Application and related documents deleted successfully.');
    } elseif ($action === 'delete_doc') {
        $doc_id = (int)$_POST['doc_id'];
        if (!$doc_id) respond('error', 'Invalid Document ID');

        $doc = $conn->query("SELECT file FROM documents WHERE id = $doc_id")->fetch_assoc();
        if ($doc) {
            $file = 'uploads/' . $doc['file'];
            if (file_exists($file)) unlink($file);
            $conn->query("DELETE FROM documents WHERE id = $doc_id");
            respond('success', 'Document deleted successfully.');
        } else {
            respond('error', 'Document not found.');
        }
    } elseif ($action === 'add_type') {
        $name = trim($_POST['name']);
        if (!$name) respond('error', 'Type name is required');
        $stmt = $conn->prepare("INSERT INTO types (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            respond('success', 'Type added', ['id' => $stmt->insert_id]);
        } else {
            respond('error', 'Failed to add type');
        }
    } elseif ($action === 'add_location') {
        $name = trim($_POST['name']);
        if (!$name) respond('error', 'Location name is required');
        $stmt = $conn->prepare("INSERT INTO locations (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            respond('success', 'Location added', ['id' => $stmt->insert_id]);
        } else {
            respond('error', 'Failed to add location');
        }
    } elseif ($action === 'add_rpo_office') {
        $name = trim($_POST['name']);
        if (!$name) respond('error', 'Office name is required');
        $stmt = $conn->prepare("INSERT INTO rpo_offices (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            respond('success', 'Office added', ['id' => $stmt->insert_id]);
        } else {
            respond('error', 'Failed to add RPO Office');
        }
    } elseif ($action === 'get_user_ids') {
        $rpo_id = intval($_POST['rpo_office_id']);
        if (!$rpo_id) respond('error', 'RPO Office ID is required');

        $stmt = $conn->prepare("
        SELECT u.id, u.user_id, u.password
        FROM user_ids u
        LEFT JOIN (
            SELECT user_id, COUNT(*) AS app_count
            FROM applications
            GROUP BY user_id
        ) a ON u.id = a.user_id
        WHERE u.rpo_office_id = ?
          AND (a.app_count IS NULL OR a.app_count < 20)
    ");
        $stmt->bind_param("i", $rpo_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        if (count($data) > 0) {
            respond('success', 'User IDs fetched', ['data' => $data]);
        } else {
            respond('error', 'No eligible user IDs found for the selected RPO office');
        }
    } elseif ($action === 'add_annexure_name') {
        $name = trim($_POST['name']);
        if (!$name) respond('error', 'Annexure name is required');

        $stmt = $conn->prepare("INSERT INTO annexure_types (name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            respond('success', 'Annexure added', ['id' => $stmt->insert_id]);
        } else {
            respond('error', 'Failed to add annexure');
        }
    } elseif ($action === 'get_remarks') {
        $app_id = (int)$_POST['app_id'];
        if (!$app_id) respond('error', 'Invalid Application ID');

        $stmt = $conn->prepare("SELECT remark, created_at FROM remarks WHERE app_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $remarks = [];

        while ($row = $res->fetch_assoc()) {
            $remarks[] = [
                'remark' => htmlspecialchars($row['remark']),
                'created_at' => date("d M Y, h:i A", strtotime($row['created_at']))
            ];
        }
        respond('success', 'Remarks fetched', ['remarks' => $remarks]);
    } elseif ($action === 'get_inq_remarks') {
        $inq_id = (int)$_POST['inq_id'];
        if (!$inq_id) respond('error', 'Invalid Inquiry ID');

        $stmt = $conn->prepare("SELECT remark, created_at FROM inq_remarks WHERE inq_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $inq_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $remarks = [];

        while ($row = $res->fetch_assoc()) {
            $remarks[] = [
                'remark' => htmlspecialchars($row['remark']),
                'created_at' => date("d M Y, h:i A", strtotime($row['created_at']))
            ];
        }
        respond('success', 'Remarks fetched', ['remarks' => $remarks]);
    } elseif ($action === 'add_document_name') {
        $name = trim($_POST['name']);
        if (!$name) respond('error', 'Document name is required');

        $stmt = $conn->prepare("INSERT INTO document_names (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            respond('success', 'Document name added', ['id' => $stmt->insert_id]);
        } else {
            respond('error', 'Failed to add document name');
        }
    } elseif ($_POST['action'] == 'get_application_data') {
        $id = intval($_POST['id']);

        $query = $conn->query("SELECT a.*, 
        (SELECT name FROM types WHERE id = a.type) as type, 
        (SELECT name FROM locations WHERE id = a.location) as location 
        FROM applications a WHERE a.id = $id");

        if ($query->num_rows > 0) {
            $data = $query->fetch_assoc();
            $data['app_dt'] = date("d-m-Y H:i A", strtotime($data['app_dt']));

            // Fetch latest status
            $status_query = $conn->query("SELECT * FROM application_status 
            WHERE app_id = $id 
            ORDER BY created_at DESC 
            LIMIT 1");

            if ($status_query->num_rows > 0) {
                $status = $status_query->fetch_assoc();
                $data['latest_app_status'] = $status['status'];
            } else {
                $data['latest_app_status'] = null;
            }

            respond('success', 'Application Found', ['data' => $data]);
        } else {
            respond('error', 'Application not found.');
        }
    } elseif ($_POST['action'] == 'get_template') {
        $skey = $_POST['skey'];
        $query = $conn->query("SELECT value FROM settings WHERE skey = '$skey'");

        if ($query->num_rows > 0) {
            $value = $query->fetch_assoc()['value'];
            respond('success', 'Template Found', ['value' => $value]);
        } else {
            respond('error', 'Template not found.');
        }
    } elseif ($action === 'update_file_no') {
        $id = intval($_POST['id']);
        $file_no = $conn->real_escape_string($_POST['file_no']);

        $status = "pending";
        $q = $conn->query("UPDATE applications SET file_no = '$file_no', status='$status' WHERE id = $id");

        if ($q) {
            respond('success', 'File number updated.');
        } else {
            respond('error', 'Failed to update file number.');
        }
    } elseif ($action === 'reschedule') {
        $id = intval($_POST['id']);
        $new_dt = $conn->real_escape_string($_POST['new_dt']);

        $status = "rescheduled";
        $q = $conn->query("UPDATE applications SET app_dt = '$new_dt', status = '$status' WHERE id = $id");

        if ($q) {
            respond('success', 'Appointment rescheduled.');
        } else {
            respond('error', 'Failed to reschedule.');
        }
    } elseif ($action === 'close') {
        $id = intval($_POST['id']);
        $passport_no = trim($_POST['passport_no']);

        if ($id && $passport_no) {
            $stmt = $conn->prepare("UPDATE applications SET status = 'completed', passport_no = ? WHERE id = ?");
            $stmt->bind_param('si', $passport_no, $id);
            if ($stmt->execute()) {
                respond('success', 'Application marked as completed.');
            } else {
                respond('error', 'Server Error');
            }
        } else {
            respond('error', 'Missing data.');
        }
    } elseif ($action === 'mark_stuck') {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("UPDATE applications SET status = 'stuck' WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            respond('success', 'Application marked as stuck successfully.');
        } else {
            respond('error', 'Failed to update application status.');
        }
    } else if ($action === 'fetch_status') {
        $id = intval($_POST['id']);
        $file_no = $_POST['file_no'] ?? '';
        $dob = $_POST['dob'] ?? '';

        if (!$id || !$file_no || !$dob) {
            respond('error', 'Server Error. Data Empty!');
        }

        // Prepare data for Flask API
        $apiUrl = "http://localhost:5000/application_status";
        $postData = json_encode([
            'file_no' => $file_no,
            'dob' => date("d-m-Y", strtotime($dob))
        ]);

        // Call Flask API using cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // seconds

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Parse API response
        $data = json_decode($response, true);

        // Handle cURL errors
        if (isset($data['error']) || $response === false || $httpCode !== 200) {
            $errorDetail = $data['error'] ?: 'HTTP Error: ' . $httpCode;
            respond('error', 'Connect API Error: ' . $errorDetail);
        }

        if (!$data || !isset($data['status'])) {
            $apiError = $data['error'] ?? 'Unknown API Error';
            respond('error', 'API Error: ' . $apiError);
        }

        $status = $data['message'];

        // Check for existing same status
        $stmt_check = $conn->prepare("SELECT id, status FROM application_status WHERE app_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $existing = $result->fetch_assoc();

        if ($existing && $existing['status'] === $status) {
            // Update last_checked if no status change
            $stmt_update = $conn->prepare("UPDATE application_status SET last_checked = NOW() WHERE id = ?");
            $stmt_update->bind_param("i", $existing['id']);
            if ($stmt_update->execute()) {
                respond('success', 'Status unchanged. Last checked updated.', ['new_status' => $status]);
            } else {
                respond('error', 'Failed to update last_checked!');
            }
        }

        // Insert new status entry
        $stmt_insert = $conn->prepare("INSERT INTO application_status (app_id, status, created_at, last_checked) VALUES (?, ?, NOW(), NOW())");
        $stmt_insert->bind_param("is", $id, $status);

        if ($stmt_insert->execute()) {
            respond('success', 'Fetched Successfully!', ['new_status' => $status]);
        } else {
            respond('error', 'Server Error: Failed to insert new status!');
        }

        exit;
    }

    respond('error', 'Invalid action');
}

respond('error', 'Invalid request');
