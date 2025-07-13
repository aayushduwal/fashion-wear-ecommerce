<?php
session_start();
require_once('../database/config.php');

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['test_mode']) || !isset($input['order_id'])) {
        throw new Exception('Invalid request data');
    }
    
    $order_id = $input['order_id'];
    $amount = $input['amount'];
    
    // In test mode, we'll just mark the order as completed
    if ($input['test_mode'] === true) {
        // Update order status in database
        $test_idx = 'TEST_' . time() . '_' . rand(1000, 9999);
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed', khalti_idx = ? WHERE id = ?");
        $stmt->bind_param("si", $test_idx, $order_id);
        
        if ($stmt->execute()) {
            // Clear cart from session
            unset($_SESSION['cart']);
            unset($_SESSION['khalti_order']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Test payment completed successfully',
                'order_id' => $order_id,
                'khalti_idx' => $test_idx
            ]);
        } else {
            throw new Exception('Failed to update order status');
        }
    } else {
        throw new Exception('Test mode not enabled');
    }
    
} catch (Exception $e) {
    error_log('Khalti Test Verification Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>