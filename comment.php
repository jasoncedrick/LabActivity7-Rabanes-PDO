<?php
require __DIR__ . '/includes/functions.php';
requireLogin();
require __DIR__ . '/db.php';

$id = isset($_GET['id']) ? positiveId($_GET['id']) : null;
$body = '';
$errors = [];
if ($id !== null) {
    $statement = $pdo->prepare('SELECT * FROM comments WHERE id = ? AND user_id = ?');
    $statement->execute([$id, $_SESSION['user_id']]);
    $comment = $statement->fetch();
    if (!$comment) {
        http_response_code(404);
        exit('Comment not found or you do not have permission to edit it.');
    }
    $postId = (int) $comment['post_id'];
    $body = $comment['body'];
} else {
    $postId = positiveId($_GET['post_id'] ?? null);
}
$statement = $pdo->prepare('SELECT title FROM posts WHERE id = ?');
$statement->execute([$postId]);
$post = $statement->fetch();
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $body = input('body');
    if ($error = validateText($body, 'Comment', 2000)) {
        $errors[] = $error;
    }
    if (!$errors) {
        if ($id === null) {
            $statement = $pdo->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)');
            $statement->execute([$postId, $_SESSION['user_id'], $body]);
        } elseif ($body !== $comment['body']) {
            $statement = $pdo->prepare('UPDATE comments SET body = ?, edited_at = NOW() WHERE id = ? AND user_id = ?');
            $statement->execute([$body, $id, $_SESSION['user_id']]);
        }
        redirect('index.php#post-' . $postId);
    }
}
$pageTitle = $id === null ? 'Add a comment' : 'Edit your comment';
require __DIR__ . '/includes/header.php';
?>
<section class="card" aria-labelledby="comment-title">
    <h1 id="comment-title"><?= escape($pageTitle) ?></h1>
    <p>On: <strong><?= escape($post['title']) ?></strong></p>
    <?php require __DIR__ . '/includes/errors.php'; ?>
    <form method="post" action="comment.php?<?= $id === null ? 'post_id=' . $postId : 'id=' . $id ?>">
        <input type="hidden" name="csrf_token" value="<?= escape(csrfToken()) ?>">
        <label for="body">Your comment</label>
        <textarea id="body" name="body" rows="5" required maxlength="2000" data-trim-required aria-describedby="comment-help"><?= escape($body) ?></textarea>
        <small id="comment-help">Text only. Up to 2,000 characters.</small>
        <div class="actions"><button type="submit"><?= $id === null ? 'Add comment' : 'Save changes' ?></button><a href="index.php#post-<?= $postId ?>">Cancel</a></div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
