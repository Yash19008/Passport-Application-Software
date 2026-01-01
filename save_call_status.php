<?php
include 'inc/db.php';

$name = $_POST['name'] ?? '';

if (!$name) {
    echo json_encode(['success' => false, 'error' => 'Status name is required.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO call_status (name) VALUES (?)");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'insert_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to insert status.']);
}
