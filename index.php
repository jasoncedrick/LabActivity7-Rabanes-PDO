<?php
require __DIR__ . '/includes/functions.php';
requireLogin();
require __DIR__ . '/db.php';

$posts = $pdo->query('SELECT posts.*, users.name AS author FROM posts JOIN users ON users.id = posts.user_id ORDER BY posts.created_at DESC, posts.id DESC')->fetchAll();
$comments = $pdo->query('SELECT comments.*, users.name AS author FROM comments JOIN users ON users.id = comments.user_id ORDER BY comments.created_at ASC, comments.id ASC')->fetchAll();
// Group comments once so each post only displays its own comments.
$commentsByPost = [];
foreach ($comments as $comment) {
    $commentsByPost[$comment['post_id']][] = $comment;
}
$pageTitle = 'News feed';
require __DIR__ . '/includes/header.php';
?>
<section class="feed-heading" aria-labelledby="feed-title">
    <p class="eyebrow">A space for your ideas</p>
    <h1 id="feed-title">News feed</h1>
    <p>Hello, <?= escape($_SESSION['user_name']) ?>. See what everyone is sharing.</p>
    <a class="button" href="post.php">Write a post</a>
</section>
<?php if (!$posts): ?>
    <section class="card"><h2>Your story starts here</h2><p>No posts yet. Be the first to share an idea.</p></section>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
    <article class="card post" id="post-<?= (int) $post['id'] ?>" aria-labelledby="title-<?= (int) $post['id'] ?>">
        <header>
            <p class="meta"><?= escape($post['author']) ?> &middot; <time datetime="<?= escape(date('c', strtotime($post['created_at']))) ?>"><?= escape(displayDate($post['created_at'])) ?></time>
                <?php if ($post['edited_at'] !== null): ?><span class="badge" title="<?= escape(displayDate($post['edited_at'])) ?>">Edited</span><?php endif; ?>
            </p>
            <h2 id="title-<?= (int) $post['id'] ?>"><?= escape($post['title']) ?></h2>
        </header>
        <p class="text-content"><?= escape($post['body']) ?></p>
        <div class="actions">
            <a href="comment.php?post_id=<?= (int) $post['id'] ?>">Add a comment</a>
            <?php if ((int) $post['user_id'] === $_SESSION['user_id']): ?>
                <a href="post.php?id=<?= (int) $post['id'] ?>">Edit post</a>
            <?php endif; ?>
        </div>
        <section class="comments" aria-labelledby="comments-<?= (int) $post['id'] ?>">
            <h3 id="comments-<?= (int) $post['id'] ?>">Comments (<?= count($commentsByPost[$post['id']] ?? []) ?>)</h3>
            <?php if (empty($commentsByPost[$post['id']])): ?><p class="meta">No comments yet.</p><?php endif; ?>
            <?php foreach ($commentsByPost[$post['id']] ?? [] as $comment): ?>
                <article class="comment" aria-label="Comment by <?= escape($comment['author']) ?>">
                    <header class="meta"><strong><?= escape($comment['author']) ?></strong> &middot; <time datetime="<?= escape(date('c', strtotime($comment['created_at']))) ?>"><?= escape(displayDate($comment['created_at'])) ?></time>
                        <?php if ($comment['edited_at'] !== null): ?><span class="badge" title="<?= escape(displayDate($comment['edited_at'])) ?>">Edited</span><?php endif; ?>
                    </header>
                    <p class="text-content"><?= escape($comment['body']) ?></p>
                    <?php if ((int) $comment['user_id'] === $_SESSION['user_id']): ?>
                        <a href="comment.php?id=<?= (int) $comment['id'] ?>">Edit comment</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
