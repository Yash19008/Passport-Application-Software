<?php
include 'inc/auth.php';
include 'inc/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $user_id = trim($_POST['user_id']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $rpo_id = intval($_POST['rpo_office_id']);

    if ($user_id == '' || $rpo_id == 0) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO user_ids (user_id, password, rpo_office_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $user_id, $password, $rpo_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User ID added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add"]);
    }
    exit;
}

if ($action === 'edit') {
    $id = intval($_POST['id']);
    $user_id = trim($_POST['user_id']);
    $rpo_id = intval($_POST['rpo_office_id']);
    $password = trim($_POST['password']);

    if ($password != '') {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user_ids SET user_id=?, password=?, rpo_office_id=? WHERE id=?");
        $stmt->bind_param("ssii", $user_id, $password, $rpo_id, $id);
    } else {
        $stmt = $conn->prepare("UPDATE user_ids SET user_id=?, rpo_office_id=? WHERE id=?");
        $stmt->bind_param("sii", $user_id, $rpo_id, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User ID updated"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM user_ids WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}
