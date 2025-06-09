<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "borsmen";

    try {
        $conn = new mysqli($host, $user, $password, $dbname);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Check business users first
        $sql = "SELECT id, username, password, 'business' as user_type FROM user_bisnis WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Check influencer users
            $sql = "SELECT id, username, password, 'influencer' as user_type FROM user_influencer WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $username;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] == 'business') {
                    header("Location: dashboard_business.php");
                } else {
                    header("Location: dashboard_influencer.php");
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/register.css">
</head>
<body>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h1>Login</h1>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" 
                       placeholder="Username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" 
                       placeholder="Password" 
                       required>
                <i class='bx bxs-lock-alt'></i>
            </div>

            <button type="submit" class="btn">Login</button>

            <div class="register-link">
                <p>Don't have an account? 
                    <a href="register.php">Register</a>
                </p>
            </div>
        </form>
    </div>
</body>
</html>