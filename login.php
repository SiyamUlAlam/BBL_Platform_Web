<?php
session_start();
include("includes/db.php");

if (isset($_SESSION['user_id'])) {
    // If already logged in, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, name, is_admin, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $name, $is_admin, $hashed_password);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        // Verify the password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['is_admin'] = $is_admin;

            // Redirect to admin page or dashboard
            if ($_SESSION['is_admin'] == 1) {
                header("Location: admin.php");
                exit();
            } else {
                header("Location: dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "No user found with this email.";
    }

    $stmt->close();
}
?>

<form method="POST" action="login.php">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" required>
  
  <label for="password">Password</label>
  <input type="password" id="password" name="password" required>
  
  <button type="submit">Login</button>
</form>

<?php if (isset($error)) echo "<p>$error</p>"; ?>
