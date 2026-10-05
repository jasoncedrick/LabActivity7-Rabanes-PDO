<?php
require __DIR__ . '/includes/functions.php';
requireGuest();
require __DIR__ . '/db.php';

$name = $email = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name = input('name');
    $email = strtolower(input('email'));
    // Passwords are deliberately not trimmed.
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmation = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';
    if ($error = validateText($name, 'Name', 80)) {
        $errors[] = $error;
    }
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address with at most 254 characters.';
    }
    if (mb_strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false) {
        $errors[] = 'Password must contain at least 8 characters, at most 72 bytes, and no null characters.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'Passwords must match.';
    }
    if (!$errors) {
        try {
            $statement = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            redirect('login.php?registered=1');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $errors[] = 'That email is already registered.';
            } else {
                throw $exception;
            }
        }
    }
}
$pageTitle = 'Create your account';
require __DIR__ . '/includes/header.php';
?>
<section class="card auth-card" aria-labelledby="register-title">
    <p class="eyebrow">Join the conversation</p>
    <h1 id="register-title">Create your account</h1>
    <p>Share your ideas and connect through comments.</p>
    <?php require __DIR__ . '/includes/errors.php'; ?>
    <form method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
        <label for="name">Name</label>
        <input id="name" name="name" autocomplete="name" required maxlength="80" data-trim-required value="<?= escape($name) ?>">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= escape($email) ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8" maxlength="72" aria-describedby="password-help" data-new-password>
        <small id="password-help">At least 8 characters, at most 72 bytes. Some characters use more than one byte.</small>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" maxlength="72" data-confirm-password>
        <button type="submit">Create account</button>
    </form>
    <p>Already registered? <a href="login.php">Log in</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
