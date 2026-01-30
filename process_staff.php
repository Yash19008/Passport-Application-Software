<?php
include 'inc/auth.php';
include 'inc/db.php';

$action = $_POST['action'] ?? '';

/* ADD */
if ($action === 'add') {
    $name = trim($_POST['name']);

    if ($name === '') {
        echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO staff (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Staff added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add staff"]);
    }
    exit;
}

/* EDIT */
if ($action === 'edit') {
    $id   = intval($_POST['id']);
    $name = trim($_POST['name']);

    if ($name === '') {
        echo json_encode(["status" => "error", "message" => "Name cannot be empty"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE staff SET name=? WHERE id=?");
    $stmt->bind_param("si", $name, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Staff updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

/* DELETE */
if ($action === 'delete') {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM staff WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Staff deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}
