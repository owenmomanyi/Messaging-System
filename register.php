<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, phone_number, password) VALUES ('$username', '$phone_number', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        $success = "You have successfully registered. You can now <a href='login.php'>login</a>.";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body style= "background-color: #2980b9; font-family: 'Times New Roman', Times, serif;">
    <div class="page-container">
        <div class="form-container">
            <h2>Register</h2>
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            <?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>
            <form method="POST" action="register.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br><br>
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="+254712345678" required><br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br><br>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>
</html>