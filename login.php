<?php
session_start();
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE phone_number = '$phone_number'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: home.html");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Phone number not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body style= "background-color: #2980b9; font-family: 'Times New Roman', Times, serif;">
    <div class="page-container">
        <div class="form-container">
            <h2>Login</h2>
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            <form method="POST" action="login.php">
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" required><br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</body>
</html>