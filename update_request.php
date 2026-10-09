<?php
// C:\xampp\htdocs\herdshield\update_request.php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $request_id = intval($input['request_id'] ?? 0);
    $status     = trim($input['status'] ?? '');

    if (!$request_id || !in_array($status, ['Approved', 'Declined'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid request parameters."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE vaccination_requests SET status = :status WHERE request_id = :request_id");
        $stmt->execute([
            'status'     => $status,
            'request_id' => $request_id
        ]);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Request updated to " . $status]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
}
?>