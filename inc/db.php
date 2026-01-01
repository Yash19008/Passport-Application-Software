<?php
date_default_timezone_set("Asia/Kolkata");

$conn = new mysqli("localhost", "root", "", "passport");

if (!$conn) {
    die("Unable to connect DB!");
}
