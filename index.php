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

<?php include 'navbar.php'; ?>

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

