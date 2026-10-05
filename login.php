<?php
require __DIR__ . '/includes/functions.php';
requireGuest();
require __DIR__ . '/db.php';
$email = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = strtolower(input('email'));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password === '' || strlen($password) > 72 || strpos($password, "\0") !== false) {
        $errors[] = 'Enter your password (at most 72 bytes).';
    }
    if (!$errors) {
        $statement = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            unset($_SESSION['csrf_token']);
            redirect('index.php');
        }
        $errors[] = 'Incorrect email or password.';
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<section class="card auth-card" aria-labelledby="login-title">
    <p class="eyebrow">Welcome back</p>
    <h1 id="login-title">Log in to your journal</h1>
    <?php if (isset($_GET['registered'])): ?>
        <p class="success" role="status">Account created. You can now log in.</p>
    <?php endif; ?>
    <?php require __DIR__ . '/includes/errors.php'; ?>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= escape($email) ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required maxlength="72">
        <button type="submit">Log in</button>
    </form>
    <p>New here? <a href="register.php">Create an account</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
