<?php
// C:\xampp\htdocs\herdshield\register.php

require_once 'db.php';

// Retrieve JSON or POST input
$input = json_decode(file_get_contents('php://input'), true);

$full_name      = trim($input['full_name'] ?? $_POST['full_name'] ?? '');
$username       = trim($input['username'] ?? $_POST['username'] ?? '');
$email          = trim($input['email'] ?? $_POST['email'] ?? '');
$password       = trim($input['password'] ?? $_POST['password'] ?? '');
$contact_number = trim($input['contact_number'] ?? $_POST['contact_number'] ?? '');
$role_id        = intval($input['role_id'] ?? $_POST['role_id'] ?? 2); // Default: 2 (Livestock Owner)

// 1. Validation: Empty Fields
if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Full name, username, email, and password are required."
    ]);
    exit();
}

// 2. Validation: Valid Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email address format."
    ]);
    exit();
}

try {
    // 3. Validation: Check existing username or email
    $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username OR email = :email");
    $checkStmt->execute(['username' => $username, 'email' => $email]);
    
    if ($checkStmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            "status" => "error",
            "message" => "Username or email is already taken."
        ]);
        exit();
    }

    // 4. Save Password & Insert User (Supports plain text or BCrypt)
    $insertStmt = $pdo->prepare("
        INSERT INTO users (role_id, full_name, username, email, password, contact_number)
        VALUES (:role_id, :full_name, :username, :email, :password, :contact_number)
    ");

    $insertStmt->execute([
        'role_id'        => $role_id,
        'full_name'      => $full_name,
        'username'       => $username,
        'email'          => $email,
        'password'       => $password, // Direct password string
        'contact_number' => $contact_number
    ]);

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Account successfully created!",
        "user_id" => $pdo->lastInsertId()
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Registration error: " . $e->getMessage()
    ]);
}
?>