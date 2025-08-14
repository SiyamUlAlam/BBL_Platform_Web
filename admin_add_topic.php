<?php
session_start();
include("includes/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch available courses
$courses = $conn->query("SELECT id, title FROM courses");

// Learning styles
$styles = [
    'linguistic', 'logical-mathematical', 'spatial',
    'bodily-kinesthetic', 'musical', 'interpersonal',
    'intrapersonal', 'naturalist'
];

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $style = $_POST['style'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $content_type = $_POST['content_type'];

    // Handle file upload
    $file_path = null;
    if (!empty($_FILES['topic_file']['name'])) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES["topic_file"]["name"]);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES["topic_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            $message = "File upload failed.";
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO topics (course_id, style, title, description, content_type, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $course_id, $style, $title, $description, $content_type, $file_path);

    if ($stmt->execute()) {
        $message = "Topic added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
            <style>
                .back-btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    background: #f4f8fb;
                    color: #357ab8;
                    border: none;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    margin-bottom: 1.2rem;
                    margin-left: 0.2rem;
                    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.10);
                    cursor: pointer;
                    transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.18s;
                    text-decoration: none;
                    outline: none;
                }
                .back-btn:hover, .back-btn:focus {
                    background: #357ab8;
                    color: #fff;
                    box-shadow: 0 4px 16px rgba(52, 152, 219, 0.13);
                    transform: translateY(-2px) scale(1.08);
                }
                .back-btn svg {
                    width: 22px;
                    height: 22px;
                    fill: none;
                    stroke: currentColor;
                    stroke-width: 2.2;
                    display: block;
                }
                @media (max-width: 600px) {
                    .back-btn {
                        width: auto;
                        height: auto;
                        border-radius: 6px;
                        padding: 7px 18px 7px 14px;
                        gap: 7px;
                    }
                    .back-btn span {
                        display: inline;
                    }
                }
                .back-btn span {
                    display: none;
                    margin-left: 6px;
                    font-weight: 600;
                    font-size: 1rem;
                }
            </style>
    <meta charset="UTF-8">
    <title>Add Topic - Admin</title>
    <link rel="stylesheet" href="css/style.css">
            <style>
                .footer {
                    background: #232946;
                    color: #fff;
                    width: 100%;
                    margin-top: 0;
                    padding: 0;
                }
                .footer-content {
                    max-width: 1100px;
                    margin: 0 auto;
                    padding: 1.5rem 1rem;
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    align-items: center;
                    gap: 1rem;
                }
                .footer-content strong {
                    color: #43cea2;
                }
                .footer-content a {
                    color: #f4faff;
                    text-decoration: none;
                    margin-right: 10px;
                    transition: color 0.2s;
                }
                .footer-content a:hover {
                    color: #43cea2;
                    text-decoration: underline;
                }
                .footer-content div {
                    flex: 1 1 200px;
                    min-width: 180px;
                }
            body {
                background: linear-gradient(to right, #43cea2, #185a9d);
                min-height: 100vh;
                margin: 0;
                display: flex;
                flex-direction: column;
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
                color: white;
                text-decoration: none;
            }
            .nav-links {
                display: flex;
                gap: 1.5rem;
            }
            .nav-item {
                color: white;
                text-decoration: none;
                font-family: 'Inter', sans-serif;
                font-size: 1rem;
                font-weight: 700;
                padding: 0.5rem 1.2rem;
                border-radius: 6px;
                position: relative;
                transition: background 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
                box-shadow: 0 2px 8px rgba(52, 152, 219, 0);
                display: inline-block;
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
            .main-wrapper { flex: 1 0 auto; }
            form { max-width: 600px; margin: 2rem auto; padding: 1rem; border: 1px solid #ccc; border-radius: 10px; background: #f9f9f9; }
            label { display: block; margin-top: 1rem; }
            input, textarea, select { width: 100%; padding: 0.5rem; margin-top: 0.5rem; }
            button { margin-top: 1rem; padding: 0.5rem 1rem; background: linear-gradient(90deg, #43cea2 0%, #4a90e2 50%, #357ab8 100%); color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 700; }
            button:hover { background: linear-gradient(90deg, #357ab8 0%, #4a90e2 80%, #43cea2 100%); }
            .message { text-align: center; padding: 1rem; color: green; }
        </style>
</head>
<body>

<header>
</header>
    <div class="navbar">
        <div class="logo">
            <a href="admin.php" class="logo" style="display: flex; align-items: center; text-decoration: none; color: white;">
                <img src="images/BBL-Logo.png" alt="Brain-Based Learning Portal" style="height: 40px; width: auto; margin-right: 10px;">
                <span class="name1">Brain<span class="name3">-</span>Based</span><span class="name2">Learning</span>
            </a>
        </div>
        <nav class="nav-links" id="navLinks">
            <a href="admin.php" class="nav-item">Admin Dashboard</a>
            <a href="logout.php" class="nav-item">Logout</a>
        </nav>
        <div class="menu-toggle" onclick="toggleMenu()">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <a class="back-btn" href="#" onclick="history.back(); return false;" title="Go back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        <span>Back</span>
    </a>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="course_id">Select Course:</label>
        <select name="course_id" required>
            <option value="">-- Select Course --</option>
            <?php while ($row = $courses->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></option>
            <?php endwhile; ?>
        </select>

        <label for="style">Select Learning Style:</label>
        <select name="style" required>
            <option value="">-- Select Style --</option>
            <?php foreach ($styles as $style): ?>
                <option value="<?php echo $style; ?>"><?php echo ucwords(str_replace('-', ' ', $style)); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="title">Topic Title:</label>
        <input type="text" name="title" required>

        <label for="description">Description:</label>
        <textarea name="description" rows="4" required></textarea>

        <label for="content_type">Content Type:</label>
        <select name="content_type" required>
            <option value="Article">Article</option>
            <option value="Video">Video</option>
            <option value="PDF">PDF</option>
        </select>

        <label for="topic_file">Upload File (optional):</label>
        <input type="file" name="topic_file" accept=".pdf,.mp4,.mov,.docx,.pptx,.zip">

        <button type="submit">Add Topic</button>
    </form>
    <a href="admin_add_quiz.php" class="button">Add Quiz</a>
</div>

<footer class="footer">
        <div class="footer-content">
            <div>
                <strong>&copy; 2025 Brain-Based Learning Platform</strong><br>
                Empowering learners with science-backed education.
            </div>
            <div>
                <strong>Quick Links</strong><br>
                <a href="dashboard.php">User Dashboard</a>
                <a href="admin.php">Admin Dashboard</a>
                <a href="courses.php">Courses</a>
                <a href="logout.php">Logout</a>
            </div>
            <div>
                <strong>Contact</strong><br>
                Email: <a href="mailto:2002032@icte.bdu.ac.bd">2002032@icte.bdu.ac.bd</a><br>
                <span>Phone: +8801887240900</span>
            </div>
        </div>
</footer>

</body>
<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("active");
    }
</script>
