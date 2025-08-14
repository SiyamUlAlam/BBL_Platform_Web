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
                <link rel="stylesheet" href="css/style.css">
                <style>
                        body {
                                background: linear-gradient(to right, #43cea2, #185a9d);
                                min-height: 100vh;
                        }
                        .navbar {
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                                padding: 1rem 2rem;
                                background: linear-gradient(90deg, #4a90e2, #357ab8);
                                box-shadow: 0 4px 10px rgba(0,0,0,0.08);
                                position: sticky;
                                top: 0;
                                z-index: 100;
                        }
                        .logo {
                                font-size: 1.5rem;
                                font-weight: 600;
                                display: flex;
                                align-items: center;
                                gap: 10px;
                        }
                        .nav-links {
                                display: flex;
                                gap: 1.5rem;
                        }
                        .nav-item {
                                color: white;
                                text-decoration: none;
                                font-weight: 700;
                                padding: 0.5rem 1.2rem;
                                border-radius: 6px;
                                position: relative;
                                transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
                                box-shadow: 0 2px 8px rgba(52, 152, 219, 0);
                        }
                        .nav-item:hover {
                                background: #fff;
                                color: #357ab8;
                                box-shadow: 0 2px 8px rgba(52, 152, 219, 0.15);
                                transform: translateY(-2px) scale(1.07);
                        }
                        .nav-item.active {
                                background: #fff;
                                color: #357ab8;
                                font-weight: 900;
                                box-shadow: 0 2px 12px rgba(52, 152, 219, 0.18);
                                border: 2px solid #357ab8;
                                transform: scale(1.12);
                                z-index: 2;
                        }
                        .menu-toggle {
                                display: none;
                                cursor: pointer;
                                margin-left: 1rem;
                        }
                        @media (max-width: 900px) {
                                .navbar {
                                        flex-direction: column;
                                        align-items: flex-start;
                                        padding: 1rem;
                                }
                                .nav-links {
                                        width: 100%;
                                        flex-direction: column;
                                        gap: 0.5rem;
                                        display: none;
                                        background: #357ab8;
                                        border-radius: 0 0 10px 10px;
                                        margin-top: 0.5rem;
                                        padding: 1rem 0;
                                }
                                .nav-links.active {
                                        display: flex;
                                }
                                .menu-toggle {
                                        display: block;
                                }
                        }
                        .register-container {
                                background: white;
                                padding: 2.5rem 2.5rem 2rem 2.5rem;
                                border-radius: 16px;
                                box-shadow: 0 8px 24px rgba(0,0,0,0.10);
                                max-width: 400px;
                                margin: 3rem auto;
                                margin-top: 4rem;
                                animation: fadeIn 1s;
                        }
                        .register-container h2 {
                                margin-top: 0;
                                color: #357ab8;
                                text-align: center;
                                font-weight: 700;
                        }
                        .register-container label {
                                display: block;
                                margin-top: 1rem;
                                font-weight: 600;
                                color: #333;
                        }
                        .register-container input[type="text"],
                        .register-container input[type="email"],
                        .register-container input[type="password"] {
                                width: 100%;
                                padding: 0.8rem;
                                margin-top: 0.5rem;
                                border: 1px solid #ccc;
                                border-radius: 8px;
                                margin-bottom: 1rem;
                                font-size: 1rem;
                        }
                                                .register-container input[type="submit"] {
                                                        width: 100%;
                                                        padding: 0.9rem;
                                                        background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%);
                                                        color: #fff;
                                                        border: none;
                                                        border-radius: 8px;
                                                        font-weight: 800;
                                                        font-size: 1.1rem;
                                                        cursor: pointer;
                                                        margin-top: 0.5rem;
                                                        box-shadow: 0 4px 16px rgba(52, 152, 219, 0.10);
                                                        letter-spacing: 1px;
                                                        transition: background 0.3s, box-shadow 0.3s, transform 0.2s;
                                                }
                                                .register-container input[type="submit"]:hover {
                                                        background: linear-gradient(90deg, #357ab8 0%, #4a90e2 80%, #43cea2 100%);
                                                        color: #fff;
                                                        box-shadow: 0 8px 24px rgba(52, 152, 219, 0.18);
                                                        transform: translateY(-2px) scale(1.04);
                                                }
                        .message {
                                text-align: center;
                                margin-bottom: 1rem;
                                color: #27ae60;
                                font-weight: 600;
                        }
                        .error {
                                text-align: center;
                                margin-bottom: 1rem;
                                color: #e74c3c;
                                font-weight: 600;
                        }
                </style>
</head>
<body>
<header>
        <div class="navbar">
                <div class="logo">
                        <a href="dashboard.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
                                <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
                                <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
                        </a>
                </div>
                <nav class="nav-links" id="navLinks">
                        <a href="dashboard.php" class="nav-item">Dashboard</a>
                        <a href="courses.php" class="nav-item">Courses</a>
                        <a href="login.php" class="nav-item active">Login</a>
                </nav>
                <div class="menu-toggle" onclick="toggleMenu()">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </div>
        </div>
</header>

<main>
        <div class="register-container">
                        <h2>Register</h2>
                        <?php if (isset($success)): ?>
                                        <div class="message"><?php echo $success; ?></div>
                        <?php elseif (isset($error)): ?>
                                        <div class="error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                                        <label for="name">Name</label>
                                        <input type="text" id="name" name="name" required>
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" required>
                                        <label for="password">Password</label>
                                        <input type="password" id="password" name="password" required>
                                        <input type="submit" value="Register">
                        </form>
                        <p style="text-align:center; margin-top:1rem;">Already have an account? <a href="login.php">Login</a></p>
        </div>

</main>

<a class="side-nav-btn left" href="#" onclick="history.back(); return false;" title="Go back" style="position: fixed; top: 50%; left: 24px; z-index: 9999; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; background: #f4f8fb; color: #357ab8; border: none; border-radius: 50%; width: 48px; height: 48px; box-shadow: 0 2px 8px rgba(52,152,219,0.10); cursor: pointer; transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s; text-decoration: none; outline: none; font-size: 1.1rem;">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
</a>
<a class="side-nav-btn right" href="#" onclick="history.forward(); return false;" title="Go forward" style="position: fixed; top: 50%; right: 24px; z-index: 9999; transform: translateY(-50%); display: flex; align-items: center; justify-content: center; background: #f4f8fb; color: #357ab8; border: none; border-radius: 50%; width: 48px; height: 48px; box-shadow: 0 2px 8px rgba(52,152,219,0.10); cursor: pointer; transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s; text-decoration: none; outline: none; font-size: 1.1rem;">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" style="transform: scaleX(-1)"><path d="M15 18l-6-6 6-6"/></svg>
</a>

<style>
.side-nav-btn:hover, .side-nav-btn:focus {
        background: #357ab8 !important;
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(52,152,219,0.13) !important;
        transform: translateY(-50%) scale(1.08) !important;
}
@media (max-width: 700px) {
        .side-nav-btn.left { left: 6px !important; }
        .side-nav-btn.right { right: 6px !important; }
        .side-nav-btn { width: 38px !important; height: 38px !important; }
        .side-nav-btn svg { width: 20px !important; height: 20px !important; }
}
</style>

<footer>
        <p style="text-align:center; padding: 1.5rem; margin-top: 2rem; background: #f1f1f1; font-size: 0.9rem; color: #666;">&copy; 2025 My Learning Platform</p>
</footer>

<script>
        function toggleMenu() {
                document.getElementById("navLinks").classList.toggle("active");
        }
</script>

