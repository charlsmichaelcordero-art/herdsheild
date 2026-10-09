<?php
// C:\xampp\htdocs\herdshield\vaccination_request.php

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $user_id        = intval($input['user_id'] ?? 0);
    $animal_tag     = trim($input['animal_tag'] ?? '');
    $vaccine_type   = trim($input['vaccine_type'] ?? '');
    $requested_date = trim($input['requested_date'] ?? '');

    if (!$user_id || empty($animal_tag) || empty($vaccine_type) || empty($requested_date)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO vaccination_requests (user_id, animal_tag, vaccine_type, requested_date, status)
            VALUES (:user_id, :animal_tag, :vaccine_type, :requested_date, 'Pending')
        ");
        $stmt->execute([
            'user_id'        => $user_id,
            'animal_tag'     => $animal_tag,
            'vaccine_type'   => $vaccine_type,
            'requested_date' => $requested_date
        ]);

        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Vaccination request submitted successfully!"]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
} elseif ($method === 'GET') {
    $user_id = intval($_GET['user_id'] ?? 0);
    $role_id = intval($_GET['role_id'] ?? 2);

    try {
        if ($role_id == 1) {
            // Vet/Admin: Fetch all pending & processed requests with farmer names
            $stmt = $pdo->query("
                SELECT vr.*, u.full_name as farmer_name 
                FROM vaccination_requests vr
                JOIN users u ON vr.user_id = u.user_id
                ORDER BY vr.created_at DESC
            ");
        } else {
            // Farmer: Fetch only their own requests
            $stmt = $pdo->prepare("
                SELECT * FROM vaccination_requests 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC
            ");
            $stmt->execute(['user_id' => $user_id]);
        }

        $requests = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $requests]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
}
?>