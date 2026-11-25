<?php

function respond($status, $message) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $status === 'success', 'status' => $status, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', 'Invalid request method.');
}

$appType = trim($_POST['application_type'] ?? '');
if ($appType !== 'intern' && $appType !== 'job') {
    respond('error', 'Invalid application type.');
}

// XAMPP default MySQL connection settings
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // default in XAMPP unless configured

if ($appType === 'intern') {
    $dbname = 'interns_reg';
    $tablename = 'interns_application';
} else {
    $dbname = 'Job_reg';
    $tablename = 'job_applicants';
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbname);
if ($mysqli->connect_errno) {
    respond('error', 'DB connect error: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

if ($appType === 'intern') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $major = trim($_POST['major'] ?? '');
    $year_of_study = trim($_POST['year_of_study'] ?? '');
    $internship_track = trim($_POST['internship_track'] ?? '');
    $technical_skills = trim($_POST['technical_skills'] ?? '');
    $motivation = trim($_POST['motivation'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO `$tablename` (`first_name`,`last_name`,`email`,`phone`,`university`,`major`,`year_of_study`,`internship_track`,`technical_skills`,`motivation`,`submitted_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        respond('error', 'Prepare error: ' . $mysqli->error);
    }
    $stmt->bind_param('ssssssssss', $first_name, $last_name, $email, $phone, $university, $major, $year_of_study, $internship_track, $technical_skills, $motivation);
}

if ($appType === 'job') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $desired_position = trim($_POST['desired_position'] ?? '');
    $years_experience = trim($_POST['years_experience'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $technical_skills = trim($_POST['technical_skills'] ?? '');
    $experience_description = trim($_POST['experience_description'] ?? '');
    $salary_expectation = trim($_POST['salary_expectation'] ?? '');

    $stmt = $mysqli->prepare("INSERT INTO `$tablename` (`first_name`,`last_name`,`email`,`phone`,`desired_position`,`years_experience`,`education`,`technical_skills`,`experience_description`,`salary_expectation`,`submitted_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        respond('error', 'Prepare error: ' . $mysqli->error);
    }
    $stmt->bind_param('ssssssssss', $first_name, $last_name, $email, $phone, $desired_position, $years_experience, $education, $technical_skills, $experience_description, $salary_expectation);
}

if ($stmt->execute()) {
    $stmt->close();
    $mysqli->close();
    respond('success', 'Application submitted successfully.');
} else {
    $stmt->close();
    $mysqli->close();
    respond('error', 'Execute error: ' . $stmt->error);
}
