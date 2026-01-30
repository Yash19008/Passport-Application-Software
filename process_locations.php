<?php
include 'inc/auth.php';
include 'inc/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = trim($_POST['name']);
    $rpo_id = intval($_POST['rpo_id']);

    if ($name == '' || $rpo_id == 0) {
        echo json_encode(["status" => "error", "message" => "All fields required"]);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO locations (rpo_id, name, created_at) VALUES (?, ?, NOW())"
    );
    $stmt->bind_param("is", $rpo_id, $name);

    echo $stmt->execute()
        ? json_encode(["status" => "success", "message" => "Location added"])
        : json_encode(["status" => "error", "message" => "Failed"]);
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $rpo_id = intval($_POST['rpo_id']);

    if ($name == '' || $rpo_id == 0) {
        echo json_encode(["status" => "error", "message" => "All fields required"]);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE locations SET rpo_id=?, name=? WHERE id=?"
    );
    $stmt->bind_param("isi", $rpo_id, $name, $id);

    echo $stmt->execute()
        ? json_encode(["status" => "success", "message" => "Location updated"])
        : json_encode(["status" => "error", "message" => "Update failed"]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM locations WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}
