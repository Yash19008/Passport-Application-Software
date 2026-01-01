<?php
include 'inc/auth.php';
include 'inc/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = trim($_POST['name']);
    if ($name == '') {
        echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO call_status (name, created_at) VALUES (?, NOW())");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Call status added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add"]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    if ($name == '') {
        echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
        exit;
    }
    $stmt = $conn->prepare("UPDATE call_status SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Call status updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM call_status WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}
