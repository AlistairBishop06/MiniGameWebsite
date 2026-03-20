<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Pairs - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
// Inline navbar (navbar.php removed for the strict page structure).
$avatar = $_SESSION['avatar'] ?? ($_COOKIE['avatar'] ?? null);
?>
<nav class="navbar-custom">
    <div class="nav-left">
        <a href="index.php" name="home">Home</a>
    </div>
    <div class="nav-right">
        <a href="pairs.php" name="memory">Play Pairs</a>
        <?php if (!empty($_SESSION['registered'])): ?>
            <a href="leaderboard.php" name="leaderboard">Leaderboard</a>
        <?php else: ?>
            <a href="registration.php" name="register">Register</a>
        <?php endif; ?>
        <?php if (!empty($avatar)): ?>
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="nav-avatar">
        <?php elseif (!empty($_SESSION['emoji'])): ?>
            <span class="nav-emoji"><?php echo htmlspecialchars($_SESSION['emoji']); ?></span>
        <?php endif; ?>
    </div>
</nav>

<div id="main">
    <div class="main-content">
        <?php if (!empty($_SESSION['registered'])): ?>
            <h1>Welcome to Pairs</h1>
            <p>
                <a href="pairs.php" class="btn btn-primary">Click here to play</a>
            </p>
        <?php else: ?>
            <p>
                You’re not using a registered session? <a href="registration.php">Register now</a>
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

