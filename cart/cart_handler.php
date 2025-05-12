<?php
session_start();
include('../database/config.php');

$product_id = isset($_GET['id']) ? $_GET['id'] : 1; // Or however you're getting the product ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo "Please login first";
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $productId = $_POST['productId'];
        $productName = $_POST['productName'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];

        // First check if product already exists in cart
        $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->bind_param("ii", $user_id, $productId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // Product exists, update quantity
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;
            
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $row['id']);
            $updateStmt->execute();
            
            echo "Product quantity updated in cart!";
        } else {
            // Product doesn't exist, insert new
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
            if (!$insertStmt) {
                echo "Database error: " . $conn->error;
                exit();
            }
            
            $insertStmt->bind_param("iisdi", $user_id, $productId, $productName, $price, $quantity);
            $insertStmt->execute();
            
            echo "Product added to cart!";
        }
    } elseif ($action === 'count') {
        if (!isset($_SESSION['user_id'])) {
            echo "0";
            exit();
        }
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo $row['total'] ?? 0;
    } elseif ($action === 'fetch') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit();
        }
        $user_id = $_SESSION['user_id'];
        $result = $conn->query("SELECT id, product_name, price, quantity FROM cart WHERE user_id = $user_id");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode($items);
    } elseif ($action === 'remove') {
        if (!isset($_SESSION['user_id'])) {
            echo "Please login first";
            exit();
        }
        $id = $_POST['id'];
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        echo "Item removed";
    }
}
?>
