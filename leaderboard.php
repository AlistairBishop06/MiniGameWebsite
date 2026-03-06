<?php
session_start();

const LEADERBOARD_COOKIE = 'pairs_leaderboard';
const MAX_ENTRIES = 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = (int) ($_POST['score'] ?? 0);
    $username = trim($_POST['username'] ?? '');

    if ($score > 0 && $username !== '') {
        $entries = [];
        if (!empty($_COOKIE[LEADERBOARD_COOKIE])) {
            $entries = json_decode($_COOKIE[LEADERBOARD_COOKIE], true) ?: [];
        }
        $entries[] = ['username' => $username, 'score' => $score];
        usort($entries, fn($a, $b) => $b['score'] <=> $a['score']);
        $entries = array_slice($entries, 0, MAX_ENTRIES);
        setcookie(LEADERBOARD_COOKIE, json_encode($entries), time() + (365 * 24 * 60 * 60), '/');
    }
    header('Location: leaderboard.php');
    exit;
}

$entries = [];
if (!empty($_COOKIE[LEADERBOARD_COOKIE])) {
    $entries = json_decode($_COOKIE[LEADERBOARD_COOKIE], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Pairs - Leaderboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main">
    <div class="main-content">
        <h1>Leaderboard</h1>
        <?php if (empty($entries)): ?>
            <p>No scores yet. Play the game and submit your score to appear here!</p>
        <?php else: ?>
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $e): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($e['username']); ?></td>
                            <td><?php echo (int) $e['score']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
