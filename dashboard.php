<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $is_admin);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background-color: #f3f3f3;
    }

    header {
      background: #185a9d;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    nav a {
      color: white;
      margin-left: 1rem;
      text-decoration: none;
      font-weight: bold;
    }

    nav a:hover {
      text-decoration: underline;
    }

    main {
      text-align: center;
      padding: 2rem;
    }

    .cta-button {
      display: inline-block;
      margin-top: 1.5rem;
      padding: 0.75rem 1.5rem;
      background-color: #4CAF50;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
      transition: background-color 0.3s ease;
    }

    .cta-button:hover {
      background-color: #45a049;
    }

    /* Slider Styles */
    .slider {
      position: relative;
      max-width: 1000px;
      height: 400px;
      margin: 3rem auto 1rem auto;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .slides {
      display: flex;
      transition: transform 0.5s ease-in-out;
      width: 3000px; /* 3 slides * 1000px */
    }

    .slides img {
      width: 1000px;
      height: 400px;
      object-fit: cover;
      flex-shrink: 0;
    }

    .dots {
      text-align: center;
      margin-top: 10px;
    }

    .dot {
      height: 12px;
      width: 12px;
      margin: 0 4px;
      background-color: #bbb;
      border-radius: 50%;
      display: inline-block;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .active-dot {
      background-color: #333;
    }

    footer {
      background: #222;
      color: white;
      padding: 1rem;
      text-align: center;
      margin-top: 2rem;
    }

    @media (max-width: 1020px) {
      .slider, .slides img {
        width: 100%;
        height: auto;
      }

      .slides {
        width: 300%;
      }
    }

    .name1{
      color:rgb(66, 241, 183);
      font-weight: bold;
    }
    .name2{
      color:rgb(242, 249, 140);
      font-weight: bold;
    }
    .name3{
      color:rgb(156, 230, 171);
      font-weight: bold;


    }
  </style>
</head>
<body>

<header>
  <h1><Span class="name1">Brain<span class="name3">-</span>Based</Span><Span class="name2">Learning</Span></h1>
  <nav>
    <a href="courses.php">Explore Courses</a>
    <?php if ($is_admin): ?>
      <a href="admin.php">Admin Panel</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
  <h2>Hello, <?php echo htmlspecialchars($name); ?>!</h2>
  <p>We're glad to have you back. Start your learning journey below.</p>
  <a href="courses.php" class="cta-button">Start Learning</a>

  <!-- Slider Section -->
  <div class="slider">
    <div class="slides" id="slideTrack">
      <img src="images\BBL1.png" alt="Campaign Banner 1">
      <img src="images\BBL2.png" alt="Campaign Banner 2">
      <img src="images\BBL3.png" alt="Campaign Banner 3">
    </div>
  </div>
  <div class="dots">
    <span class="dot" onclick="moveToSlide(0)"></span>
    <span class="dot" onclick="moveToSlide(1)"></span>
    <span class="dot" onclick="moveToSlide(2)"></span>
  </div>
</main>

<footer>
  <p>&copy; 2025 My Learning Platform. All rights reserved.</p>
</footer>

<script>
  let slideIndex = 0;
  const slides = document.getElementById('slideTrack');
  const dots = document.querySelectorAll('.dot');

  function moveToSlide(index) {
    slideIndex = index;
    slides.style.transform = `translateX(-${index * 1000}px)`;
    updateDots();
  }

  function updateDots() {
    dots.forEach(dot => dot.classList.remove('active-dot'));
    if (dots[slideIndex]) {
      dots[slideIndex].classList.add('active-dot');
    }
  }

  function autoSlide() {
    slideIndex = (slideIndex + 1) % 3;
    moveToSlide(slideIndex);
  }

  setInterval(autoSlide, 5000);
  moveToSlide(0);
</script>

</body>
</html>
