<?php
include 'database/config.php'; // Ensure this file connects to the database.

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = htmlspecialchars($_POST['token']);
    $new_password = htmlspecialchars($_POST['password']);

    // Validate the token
    $sql = "SELECT * FROM registerinfo WHERE reset_token='$token'";  // Enclose token in quotes
    $run = mysqli_query($conn, $sql);

    if (!$run) {
        die("Query failed: " . mysqli_error($conn));  // Error handling for failed query
    }

    $result = mysqli_fetch_assoc($run);  // Correct way to fetch the result

    if ($result) {
        // Update the password and clear the reset token
        $update_sql = "UPDATE registerinfo SET password='$new_password', reset_token=NULL WHERE reset_token='$token'";  // Enclose token in quotes
        if (mysqli_query($conn, $update_sql)) {
            echo "<script>alert('Password has been reset successfully.')</script>";
            header("Location: login.php");
            exit();  // Make sure to exit after header redirection
        } else {
            echo "Error updating password: " . mysqli_error($conn);
        }
    } else {
        echo "Invalid or expired token.";
    }
} else if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);
    // Display the reset password form
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="wrapper">
    <h1>Reset Password</h1>
    <form method="post" action="resetpw.php">
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <div>
        <label for="password-input">
          <box-icon type='solid' name='lock'></box-icon>
        </label>
        <input type="password" name="password" id="password-input" placeholder="New Password" required>
      </div>
      <button type="submit">Reset Password</button>
    </form>
  </div>
</body>

</html>
<?php
}
?>