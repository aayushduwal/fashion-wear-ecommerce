<?php
session_start();
include('database/config.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['add_to_cart'])) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in.";
        exit();
    }

    $user_id = $_SESSION['user_id']; // User's ID from session
    $product_id = $_POST['product_id']; // Product ID from the form
    $quantity = 1; // Default quantity

    // Check if the product already exists in the cart
    $check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($check_query);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error); 
    }

    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the product is already in the cart, increment the quantity
        $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($update_query);
        
        if ($stmt === false) {
            die('MySQL prepare error: ' . $conn->error);  
        }

        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // If not in cart, insert the product
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);

        if ($stmt === false) {
            die('MySQL prepare error: ' . $conn->error);  
        }

        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt->execute();
    }

    // Update the cart count session
    $count_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($total_items);
    $stmt->fetch();

    // Set the total items in the session
    $_SESSION['cart_count'] = $total_items;

    // Redirect to the cart page
    header("Location: cart.php");
    exit();
}
?>
