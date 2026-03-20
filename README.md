# COM2021 Web Development – Submission README

**VM (ECM1417-2020):** http://ml-lab-4d78f073-aa49-4f0e-bce2-31e5254052c7.ukwest.cloudapp.azure.com:49926/

---

## index.php
- Displays "Welcome to Pairs" with personalised username if user is in a registered session
- Displays "Click here to play" button linking to pairs.php for registered users
- Displays "You're not using a registered session? Register now" with hyperlink to registration.php for unregistered users
- Navbar: Home (left), Play Pairs / Leaderboard or Register (right), avatar image shown if registered

## registration.php
- Username input with server-side validation using preg_match against invalid character set
- Error message rendered beneath input field on invalid submission; prior value repopulated
- Avatar selector: six image options (avatar1.png–avatar6.png) as radio inputs with visual highlight on selection
- On success: username and avatar stored in cookies (7-day expiry) and PHP session variables

## pairs.php
- "Start the game" button begins play and disappears on click
- Multi-level game: Level 1 pairs (2 cards), Level 2 triples (3 cards), Level 3+ quads (4 cards)
- Cards drawn from emoji pool, shuffled via Fisher-Yates per level; increasing card count each level
- CSS 3D card flip animation on selection; mismatched cards flip back after 600 ms
- Attempt counter increments on each completed group selection (matched or not)
- Per-level score and cumulative total score tracked throughout session
- Game container background turns gold (#FFD700) when live score exceeds current level best
- Registered users shown score panel on completion with Submit Score and Play Again options
- Play Again resets all scores to zero and restarts from Level 1

## leaderboard.php
- Receives POST score submission from pairs.php as JSON
- Stores best scores per username in leaderboard_store.json (one record per user, updated on improvement)
- Displays sorted table (highest total score first) with blue header cells and border-spacing: 2px
- Columns: username, per-level best scores, total best score