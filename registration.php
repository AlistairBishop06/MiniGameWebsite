<?php
session_start();

$message = '';
$usernameError = '';

// Default values from existing session or cookies
$username = $_SESSION['username'] ?? ($_COOKIE['username'] ?? '');
$avatar = $_SESSION['avatar'] ?? ($_COOKIE['avatar'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $avatar = $_POST['avatar'] ?? '';

    // Invalid characters set: " ! @ # % &ˆ* ( ) + = { } [ ] — ; : “ ’ < > ? /
    $invalidPattern = '/["!@#%\^*\(\)\+=\{\}\[\]—;:“\'<>?\/]/u';

    if ($username === '') {
        $usernameError = 'Please enter a username.';
    } elseif (preg_match($invalidPattern, $username)) {
        $usernameError = 'Username contains invalid characters.';
    }

    if ($usernameError === '') {
        $_SESSION['registered'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $avatar;

        // Store profile information in cookies (no database required)
        $expiry = time() + (7 * 24 * 60 * 60); // 7 days
        setcookie('username', $username, $expiry, '/');
        if ($avatar !== '') {
            setcookie('avatar', $avatar, $expiry, '/');
        }

        $message = 'Registration successful! You can now view the leaderboard and play.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Pairs - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main">
    <div class="main-content">
        <h1>Register Profile</h1>

        <?php if ($message): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="registration.php">
            <div class="form-group">
                <label for="username">Username/nickname</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    value="<?php echo htmlspecialchars($username); ?>"
                >
                <?php if ($usernameError): ?>
                    <div class="error">
                        <?php echo htmlspecialchars($usernameError); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Avatar selector</label>
                <div class="avatar-options">
                    <label>
                        <input
                            type="radio"
                            name="avatar"
                            value="avatar1.png"
                            <?php if ($avatar === 'avatar1.png') echo 'checked'; ?>
                        >
                        <img src="avatar1.png" alt="Avatar 1">
                    </label>
                    <label>
                        <input
                            type="radio"
                            name="avatar"
                            value="avatar2.png"
                            <?php if ($avatar === 'avatar2.png') echo 'checked'; ?>
                        >
                        <img src="avatar2.png" alt="Avatar 2">
                    </label>
                    <label>
                        <input
                            type="radio"
                            name="avatar"
                            value="avatar3.png"
                            <?php if ($avatar === 'avatar3.png') echo 'checked'; ?>
                        >
                        <img src="avatar3.png" alt="Avatar 3">
                    </label>
                </div>
            </div>

            <input type="submit" value="Register">
        </form>
    </div>
</div>

</body>
</html>

