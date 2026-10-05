<?php
require __DIR__ . '/includes/functions.php';
requireLogin();
require __DIR__ . '/db.php';

$id = isset($_GET['id']) ? positiveId($_GET['id']) : null;
$title = $body = '';
$errors = [];
if ($id !== null) {
    $statement = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND user_id = ?');
    $statement->execute([$id, $_SESSION['user_id']]);
    $post = $statement->fetch();
    if (!$post) {
        http_response_code(404);
        exit('Post not found or you do not have permission to edit it.');
    }
    $title = $post['title'];
    $body = $post['body'];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $title = input('title');
    $body = input('body');
    if ($error = validateText($title, 'Title', 150)) {
        $errors[] = $error;
    }
    if ($error = validateText($body, 'Post', 10000)) {
        $errors[] = $error;
    }
    if (!$errors) {
        if ($id === null) {
            $statement = $pdo->prepare('INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)');
            $statement->execute([$_SESSION['user_id'], $title, $body]);
            $id = (int) $pdo->lastInsertId();
        } elseif ($title !== $post['title'] || $body !== $post['body']) {
            // Check ownership again in the UPDATE, not only in the interface.
            $statement = $pdo->prepare('UPDATE posts SET title = ?, body = ?, edited_at = NOW() WHERE id = ? AND user_id = ?');
            $statement->execute([$title, $body, $id, $_SESSION['user_id']]);
        }
        redirect('index.php#post-' . $id);
    }
}
$pageTitle = $id === null ? 'Write a post' : 'Edit your post';
require __DIR__ . '/includes/header.php';
?>
<section class="card" aria-labelledby="post-title">
    <h1 id="post-title"><?= escape($pageTitle) ?></h1>
    <?php require __DIR__ . '/includes/errors.php'; ?>
    <form method="post" action="post.php<?= $id === null ? '' : '?id=' . $id ?>">
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
        <label for="title">Title</label>
        <input id="title" name="title" required maxlength="150" data-trim-required value="<?= escape($title) ?>">
        <label for="body">Your post</label>
        <textarea id="body" name="body" rows="10" required maxlength="10000" data-trim-required aria-describedby="post-help"><?= escape($body) ?></textarea>
        <small id="post-help">Text only. Up to 10,000 characters.</small>
        <div class="actions"><button type="submit"><?= $id === null ? 'Publish post' : 'Save changes' ?></button><a href="index.php">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
