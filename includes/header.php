<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle) ?> | Campus Journal</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="assets/validation.js" defer></script>
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Campus Journal</a>
    <nav aria-label="Main navigation">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php">News feed</a>
            <a href="post.php">Write a post</a>
            <form action="logout.php" method="post" class="inline-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
                <button class="secondary" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main id="main-content">
