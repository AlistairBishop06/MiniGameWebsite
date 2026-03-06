<?php
// Assumes session_start() has already been called in the including page.
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
        <?php
        $avatar = $_SESSION['avatar'] ?? ($_COOKIE['avatar'] ?? null);
        ?>
        <?php if (!empty($avatar)): ?>
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="nav-avatar">
        <?php elseif (!empty($_SESSION['emoji'])): ?>
            <span class="nav-emoji"><?php echo htmlspecialchars($_SESSION['emoji']); ?></span>
        <?php endif; ?>
    </div>
</nav>

