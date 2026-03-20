<?php
session_start();

const LEADERBOARD_STORE_PATH = __DIR__ . '/leaderboard_store.json';
const MAX_ENTRIES = 10;

function loadLeaderboardStore(): array {
    if (!file_exists(LEADERBOARD_STORE_PATH)) {
        return ['users' => []];
    }

    $decoded = json_decode((string) file_get_contents(LEADERBOARD_STORE_PATH), true);
    return is_array($decoded) ? $decoded : ['users' => []];
}

function saveLeaderboardStore(array $store): void {
    @file_put_contents(LEADERBOARD_STORE_PATH, json_encode($store, JSON_PRETTY_PRINT));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalScore = (int) ($_POST['totalScore'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $levelScores = json_decode((string) ($_POST['levelScores'] ?? '[]'), true);

    $sessionRegistered = !empty($_SESSION['registered']);
    $sessionUsername = $_SESSION['username'] ?? '';

    if ($sessionRegistered && $username !== '' && $username === $sessionUsername && is_array($levelScores)) {
        $store = loadLeaderboardStore();

        if (empty($store['users'][$username])) {
            $store['users'][$username] = [
                'levels' => [0, 0, 0],
                'total' => 0,
            ];
        }

        for ($i = 0; $i < 3; $i++) {
            $submitted = (int) ($levelScores[$i] ?? 0);
            $currentBest = (int) ($store['users'][$username]['levels'][$i] ?? 0);
            if ($submitted > $currentBest) {
                $store['users'][$username]['levels'][$i] = $submitted;
            }
        }

        $currentTotalBest = (int) ($store['users'][$username]['total'] ?? 0);
        if ($totalScore > $currentTotalBest) {
            $store['users'][$username]['total'] = $totalScore;
        }

        saveLeaderboardStore($store);
    }

    header('Location: leaderboard.php');
    exit;
}

$store = loadLeaderboardStore();
$entries = [];
foreach (($store['users'] ?? []) as $u => $uData) {
    $levels = $uData['levels'] ?? [0, 0, 0];
    $levels = is_array($levels) ? $levels : [0, 0, 0];
    $total = (int) ($uData['total'] ?? 0);

    $entries[] = [
        'username' => (string) $u,
        'levels' => array_map(fn($x) => (int) $x, array_slice($levels, 0, 3)),
        'total' => $total,
    ];
}

usort($entries, fn($a, $b) => $b['total'] <=> $a['total']);
$entries = array_slice($entries, 0, MAX_ENTRIES);

$avatar = $_SESSION['avatar'] ?? ($_COOKIE['avatar'] ?? null);
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
    <div class="game-container">
        <h1>Leaderboard</h1>
        <?php if (empty($entries)): ?>
            <p>No scores yet. Play the game and submit your score to appear here!</p>
        <?php else: ?>
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Player</th>
                        <th>Level 1 Best</th>
                        <th>Level 2 Best</th>
                        <th>Level 3 Best</th>
                        <th>Total Best</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $i => $e): ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($e['username']); ?></td>
                            <td><?php echo (int) ($e['levels'][0] ?? 0); ?></td>
                            <td><?php echo (int) ($e['levels'][1] ?? 0); ?></td>
                            <td><?php echo (int) ($e['levels'][2] ?? 0); ?></td>
                            <td><?php echo (int) $e['total']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
