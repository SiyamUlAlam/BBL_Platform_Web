<?php
include("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $success = "Registration successful! <a href='login.php'>Login now</a>";
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to right, #43cea2, #185a9d);
            margin: 0;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background-color: white;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #185a9d;
            outline: none;
        }

        input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .message {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .success {
            background-color: #e0f7e9;
            color: #2e7d32;
            border: 1px solid #2e7d32;
        }

        .error {
            background-color: #ffebeb;
            color: #d8000c;
            border: 1px solid #d8000c;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Create an Account</h2>
    <form method="POST" action="register.php">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <input type="submit" value="Register">
    </form>

    <?php
    if (isset($success)) echo "<div class='message success'>$success</div>";
    if (isset($error)) echo "<div class='message error'>$error</div>";
    ?>
</div>

</body>
</html>
