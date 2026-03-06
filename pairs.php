<?php
session_start();
$config = require 'config.php';
$emojis = $config['emojis'];
$isRegistered = !empty($_SESSION['registered']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Pairs - Play</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main">
    <div class="game-container" id="gameContainer">
        <div class="game-header" id="gameHeader">
            <button type="button" class="btn btn-primary" id="startBtn">Start the game</button>
        </div>

        <div class="game-board-wrapper" id="gameBoardWrapper" style="display: none;">
            <div class="game-stats">
                <span>Level: <strong id="levelDisplay">1</strong></span>
                <span>Moves: <strong id="movesDisplay">0</strong></span>
                <span>Time: <strong id="timeDisplay">0s</strong></span>
                <span>Score: <strong id="scoreDisplay">0</strong></span>
                <span>Guesses left: <strong id="guessesLeftDisplay">0</strong></span>
            </div>
            <div class="game-board" id="gameBoard"></div>
        </div>

        <div class="game-complete" id="gameComplete" style="display: none;">
            <h2 id="gameResultTitle">Game Complete!</h2>
            <p>Total Score: <strong id="finalScore">0</strong></p>
            <?php if ($isRegistered): ?>
                <div class="game-actions">
                    <form method="post" action="leaderboard.php" id="submitForm" style="display: inline;">
                        <input type="hidden" name="score" id="submitScore" value="0">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">Submit Score</button>
                    </form>
                    <button type="button" class="btn btn-secondary" id="playAgainBtn">Play Again</button>
                </div>
            <?php else: ?>
                <button type="button" class="btn btn-primary" id="playAgainBtn">Play Again</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    const EMOJIS = <?php echo json_encode($emojis); ?>;
    const IS_REGISTERED = <?php echo $isRegistered ? 'true' : 'false'; ?>;

    // Level config: [cardsTotal, matchSize, maxGuesses]
    const LEVELS = [
        [8, 2, 12],   // Level 1: 8 cards, match 2 (4 pairs), 12 guesses
        [12, 3, 18],  // Level 2: 12 cards, match 3 (4 sets), 18 guesses
        [16, 4, 24],  // Level 3: 16 cards, match 4 (4 sets), 24 guesses
    ];

    const STORAGE_KEY = 'pairs_best_scores';

    const startBtn = document.getElementById('startBtn');
    const gameHeader = document.getElementById('gameHeader');
    const gameBoardWrapper = document.getElementById('gameBoardWrapper');
    const gameBoard = document.getElementById('gameBoard');
    const gameComplete = document.getElementById('gameComplete');
    const levelDisplay = document.getElementById('levelDisplay');
    const movesDisplay = document.getElementById('movesDisplay');
    const timeDisplay = document.getElementById('timeDisplay');
    const scoreDisplay = document.getElementById('scoreDisplay');
    const guessesLeftDisplay = document.getElementById('guessesLeftDisplay');
    const finalScoreEl = document.getElementById('finalScore');
    const submitScoreEl = document.getElementById('submitScore');
    const playAgainBtn = document.getElementById('playAgainBtn');
    const gameContainer = document.getElementById('gameContainer');
    const gameResultTitle = document.getElementById('gameResultTitle');

    let state = {
        level: 0,
        cards: [],
        flipped: [],
        matched: 0,
        moves: 0,
        startTime: null,
        timerId: null,
        totalScore: 0,
        levelScores: [],
        bestScores: JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'),
        guessesLeft: 0,
    };

    function shuffle(arr) {
        const a = [...arr];
        for (let i = a.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [a[i], a[j]] = [a[j], a[i]];
        }
        return a;
    }

    function buildDeck(numCards, matchSize) {
        const numSymbols = numCards / matchSize;
        const pool = shuffle([...EMOJIS]).slice(0, numSymbols);
        const deck = [];
        for (let i = 0; i < matchSize; i++) {
            deck.push(...pool);
        }
        return shuffle(deck);
    }

    function getLevelConfig() {
        return LEVELS[state.level] || null;
    }

    function startLevel() {
        const cfg = getLevelConfig();
        if (!cfg) {
            endGame(true);
            return;
        }
        const [numCards, matchSize, maxGuesses] = cfg;
        state.cards = buildDeck(numCards, matchSize);
        state.flipped = [];
        state.matched = 0;
        state.moves = 0;
        state.guessesLeft = maxGuesses;
        state.startTime = Date.now();
        if (state.timerId) clearInterval(state.timerId);
        state.timerId = setInterval(updateTimer, 1000);

        levelDisplay.textContent = state.level + 1;
        movesDisplay.textContent = '0';
        timeDisplay.textContent = '0s';
        guessesLeftDisplay.textContent = state.guessesLeft;
        gameBoard.innerHTML = '';
        gameBoard.className = 'game-board';
        gameBoard.style.setProperty('--cols', Math.ceil(Math.sqrt(numCards)));

        state.cards.forEach((emoji, i) => {
            const card = document.createElement('div');
            card.className = 'card';
            card.dataset.index = i;
            card.dataset.emoji = emoji;
            card.innerHTML = `<span class="card-back">?</span><span class="card-front">${emoji}</span>`;
            card.addEventListener('click', () => handleCardClick(card));
            gameBoard.appendChild(card);
        });
    }

    function updateTimer() {
        if (!state.startTime) return;
        const sec = Math.floor((Date.now() - state.startTime) / 1000);
        timeDisplay.textContent = sec + 's';
    }

    function handleCardClick(card) {
        if (card.classList.contains('flipped') || card.classList.contains('matched')) return;
        if (state.flipped.length >= LEVELS[state.level][1]) return;

        card.classList.add('flipped');
        state.flipped.push(card);

        if (state.flipped.length === LEVELS[state.level][1]) {
            state.moves++;
            movesDisplay.textContent = state.moves;
            const emojis = state.flipped.map(c => c.dataset.emoji);
            const allSame = emojis.every(e => e === emojis[0]);

            if (allSame) {
                state.flipped.forEach(c => {
                    c.classList.add('matched');
                });
                state.matched += LEVELS[state.level][1];
                state.flipped = [];

                if (state.matched === state.cards.length) {
                    levelComplete();
                }
            } else {
                setTimeout(() => {
                    state.flipped.forEach(c => c.classList.remove('flipped'));
                    state.flipped = [];
                    state.guessesLeft--;
                    guessesLeftDisplay.textContent = state.guessesLeft;
                    if (state.guessesLeft <= 0) {
                        endGame(false);
                    }
                }, 600);
            }
        }
    }

    function levelComplete() {
        clearInterval(state.timerId);
        const timeSec = Math.floor((Date.now() - state.startTime) / 1000);
        const cfg = LEVELS[state.level];
        const levelPoints = Math.max(0, 1000 - state.moves * 25 - timeSec * 5);
        state.levelScores.push(levelPoints);
        state.totalScore += levelPoints;

        const levelKey = 'level' + (state.level + 1);
        const prevBest = (state.bestScores[levelKey] || 0);
        if (levelPoints > prevBest) {
            state.bestScores[levelKey] = levelPoints;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state.bestScores));
            gameContainer.classList.add('best-score');
        }

        scoreDisplay.textContent = state.totalScore;
        state.level++;
        const nextCfg = getLevelConfig();
        if (nextCfg) {
            setTimeout(() => startLevel(), 800);
        } else {
            endGame(true);
        }
    }

    function endGame(isWin = true) {
        clearInterval(state.timerId);
        gameBoardWrapper.style.display = 'none';
        gameComplete.style.display = 'block';
        gameContainer.classList.remove('best-score');
        gameResultTitle.textContent = isWin ? 'Game Complete!' : 'Game Over';
        finalScoreEl.textContent = state.totalScore;
        if (submitScoreEl) submitScoreEl.value = state.totalScore;
    }

    function startGame() {
        state.level = 0;
        state.totalScore = 0;
        state.levelScores = [];
        state.bestScores = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
        gameContainer.classList.remove('best-score');
        gameComplete.style.display = 'none';
        gameHeader.style.display = 'none';
        gameBoardWrapper.style.display = 'block';
        scoreDisplay.textContent = '0';
        startLevel();
    }

    function init() {
        startBtn.addEventListener('click', () => {
            startGame();
        });

        if (playAgainBtn) {
            playAgainBtn.addEventListener('click', () => {
                state.totalScore = 0;
                state.levelScores = [];
                gameComplete.style.display = 'none';
                gameBoardWrapper.style.display = 'block';
                gameHeader.style.display = 'none';
                gameContainer.classList.remove('best-score');
                startGame();
            });
        }
    }

    init();
})();
</script>

</body>
</html>
