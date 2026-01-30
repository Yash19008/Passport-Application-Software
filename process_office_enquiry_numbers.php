<?php
include 'inc/auth.php';
include 'inc/db.php';

$action = $_POST['action'] ?? '';

function isValidNumber($number)
{
    return preg_match('/^[0-9]{11}$/', $number);
}

/* ADD */
if ($action === 'add') {
    $number = trim($_POST['enquiry_number']);

    if (!isValidNumber($number)) {
        echo json_encode(["status" => "error", "message" => "Enter a valid 11 digit number"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO office_enquiry_numbers (enquiry_number) VALUES (?)");
    $stmt->bind_param("s", $number);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Enquiry number added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Number already exists"]);
    }
    exit;
}

/* EDIT */
if ($action === 'edit') {
    $id = intval($_POST['id']);
    $number = trim($_POST['enquiry_number']);

    if (!isValidNumber($number)) {
        echo json_encode(["status" => "error", "message" => "Enter a valid 11 digit number"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE office_enquiry_numbers SET enquiry_number=? WHERE id=?");
    $stmt->bind_param("si", $number, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

/* DELETE */
if ($action === 'delete') {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM office_enquiry_numbers WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}
