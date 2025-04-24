<?php
session_start();
include("db/connection.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    if (!empty($username) && !empty($password) && !empty($email)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        

        $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($con, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo "Username or email already exists!";
        } else {
            $query = "INSERT INTO users (username, password, email) VALUES ('$username', '$hashed_password', '$email')";
            if (mysqli_query($con, $query)) {
                header("Location: login.php");
                die;
            } else {
                echo "Error in registration!";
            }
        }
    } else {
        echo "Please enter valid information!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <div id="box">
        <form method="post">
            <div>Register</div>
            <input type="text" name="username" placeholder="Username"><br><br>
            <input type="password" name="password" placeholder="Password"><br><br>
            <input type="email" name="email" placeholder="Email"><br><br>
            <input type="submit" value="Register"><br><br>
            <a href="login.php">Click to Login</a>
        </form>
    </div>
</body>
</html>