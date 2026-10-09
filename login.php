<?php
// C:\xampp\htdocs\herdshield\login.php

require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? $_POST['username'] ?? '');
$password = trim($input['password'] ?? $_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Empty username or password."
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.role_id, u.full_name, u.username, u.email, u.password, u.is_active, r.role_name 
        FROM users u
        INNER JOIN roles r ON u.role_id = r.role_id
        WHERE u.username = :user_param OR u.email = :email_param
    ");
    
    $stmt->execute([
        'user_param'  => $username,
        'email_param' => $username
    ]);
    
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Account does not exist."
        ]);
        exit();
    }

    if ($user['is_active'] != 1) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "Your account is deactivated."
        ]);
        exit();
    }

    // Supports both plain text and BCrypt hashes
    $passwordMatched = ($password === $user['password']) || password_verify($password, $user['password']);

    if (!$passwordMatched) {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Incorrect username or password."
        ]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Successful login.",
        "user" => [
            "user_id"    => $user['user_id'],
            "role_id"    => $user['role_id'],
            "full_name"  => $user['full_name'],
            "username"   => $user['username'],
            "email"      => $user['email'],
            "role"       => $user['role_name']
        ]
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database Connection Error: Ensure MySQL is running in XAMPP."
    ]);
}
?>