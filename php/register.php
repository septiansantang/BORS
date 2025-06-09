<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Initialize error variable
$error = '';

// Check if form submitted using POST
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Database configuration
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "borsmen";

    // Create database connection
    $conn = new mysqli($host, $user, $password, $dbname);

    // Setelah koneksi database
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        // Check if all required fields are set
        if (
            isset($_POST['name']) && 
            isset($_POST['username']) && 
            isset($_POST['email']) && 
            isset($_POST['password']) && 
            isset($_POST['user_type'])
        ) {
            $name = trim($_POST['name']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $user_type = trim($_POST['user_type']);

            if (!empty($name) && !empty($username) && !empty($email) && !empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                if ($user_type == "business") {
                    $sql = "INSERT INTO user_bisnis (nama_bisnis, username, email, password) 
                            VALUES (?, ?, ?, ?)";
                } else {
                    $sql = "INSERT INTO user_influencer (name, username, email, password) 
                            VALUES (?, ?, ?, ?)";
                }

                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssss", $name, $username, $email, $hashed_password);

                    if ($stmt->execute()) {
                        $_SESSION['username'] = $username;
                        $_SESSION['user_type'] = $user_type;
                        $_SESSION['user_id'] = $conn->insert_id;
                        
                        // Close statement and connection before redirect
                        $stmt->close();
                        $conn->close();
                        
                        if ($user_type == "influencer") {
                            header("Location: form.php");
                        } else {
                            header("Location: formbiz.php");
                        }
                        exit();
                    } else {
                        $error = "Error executing query: " . $stmt->error;
                        // Debugging
                        echo "Error: " . $error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error preparing statement: " . $conn->error;
                }
            } else {
                $error = "All fields are required";
            }
        } else {
            $error = "Missing required fields";
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BORSMEN</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/register.css">
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
            <h1>Registration</h1>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            
            <div class="input-box">
                <input type="text" name="name" placeholder="Nama Lengkap" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class='bx bxs-envelope'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            
            <div class="user-type">
                <p>Who are you?</p>
                <label>
                    <input type="radio" name="user_type" value="business" required>
                    <span>Business</span>
                </label>
                <label>
                    <input type="radio" name="user_type" value="influencer" required>
                    <span>Influencer</span>
                </label>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="register-link">
                <p>Already have an account? 
                    <a href="login.php">Login</a>
                </p>
            </div>
        </form>
    </div>

    <script>
    function validateForm() {
        var userType = document.querySelector('input[name="user_type"]:checked');
        if (!userType) {
            alert("Please select user type");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>