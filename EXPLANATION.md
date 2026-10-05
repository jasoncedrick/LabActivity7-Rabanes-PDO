# How to explain the project

## Short introduction

This is a text-only blog built with PHP and MySQL. PDO connects PHP to the database. Sessions remember who is logged in. Users can read everyone's posts, add posts and comments, and edit only their own content.

## 1. Database relationships

- One user can create many posts: `posts.user_id` points to `users.id`.
- A user can comment on many posts, and a post can receive comments from many users.
- `comments` connects users and posts using `user_id` and `post_id`. This is a junction table with additional data: the comment body and timestamps.
- Every table has its own auto-increment ID. The same user can comment on a post more than once, so the pair `(user_id, post_id)` is not unique.
- A foreign key prevents a comment from referring to a nonexistent post or user.
- `ON DELETE CASCADE` removes related rows automatically. Deleting a post removes its comments. Deleting a user removes their posts and comments, including other users' comments on the deleted posts.
- `UNIQUE` prevents two accounts from using the same email address. Emails are trimmed and lowercased before lookup and registration.

## 2. PDO and require

```php
require __DIR__ . '/db.php';
```

`require` loads and executes the connection file. It stops execution if that file is missing. `__DIR__` builds a reliable path from the current file's directory. `db.php` creates `$pdo`, which the page can then use.

```php
$statement = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
$statement->execute([$email]);
$user = $statement->fetch();
```

`prepare` defines the SQL. `?` is a placeholder. `execute` supplies the value separately, which prevents user input from becoming SQL commands. `fetch` retrieves one row. The feed uses `query` for fixed SQL with no user-supplied values.

## 3. Registration and login

Registration validates the form and stores `password_hash($password, PASSWORD_DEFAULT)`. A password hash is a one-way representation, not the original password. PHP generates the salt automatically.

Login searches for the account by email and checks `password_verify($password, $user['password'])`. We do not hash the entered password and compare the resulting strings, because password hashing uses a salt.

On successful login:

```php
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = $user['name'];
```

Regenerating the session ID protects against session fixation. The browser holds a session cookie; the server stores the session data. The code does not store the user's password in the session.

## 4. Guards

`requireGuest()` redirects already logged-in users away from login and registration. `requireLogin()` redirects guests to login. These run before HTML output because PHP must send redirect headers first. `exit` stops the rest of the page after a redirect.

## 5. Creating and editing content

`post.php` without an ID creates a post. `post.php?id=5` edits post 5 if it belongs to the logged-in user. `comment.php?post_id=5` adds a comment to post 5, while `comment.php?id=3` edits comment 3.

The owner ID comes from `$_SESSION['user_id']`, never from a form field. During editing, the SQL includes both the record ID and the owner ID:

```sql
UPDATE posts
SET title = ?, body = ?, edited_at = NOW()
WHERE id = ? AND user_id = ?
```

Hiding the Edit link makes the interface clearer. The server-side ownership check is what prevents unauthorized edits, even when someone manually changes a URL or submits their own request. Owning a post does not give permission to edit another user's comment on it.

`edited_at` begins as NULL. It is set only when the normalized saved text actually changes. The feed displays an Edited badge when that field is not NULL. Saving unchanged text does not create a new edit marker or change an existing timestamp.

After a successful write, PHP redirects to the feed. This is Post/Redirect/Get: refreshing the destination does not resubmit the original form.

## 6. Validation and output safety

| Layer | Example | Purpose |
| --- | --- | --- |
| HTML | `required`, `type="email"`, `maxlength` | Immediate feedback in the browser. |
| JavaScript | Reject whitespace-only text and mismatched passwords | Extra feedback before submitting. |
| PHP | Validate text, email, passwords, IDs, CSRF, and ownership | Protect the application even if browser checks are bypassed. |
| Database | Unique email and foreign keys | Preserve data rules at storage level. |

Posts are limited to 10,000 characters, comments to 2,000, titles to 150, and names to 80. Passwords require 8 characters and are capped at 72 bytes to avoid bcrypt's input truncation. `strlen` measures bytes; `mb_strlen` measures characters. Passwords are not trimmed, because spaces can be intentional.

`escape()` uses `htmlspecialchars()` when displaying names, posts, comments, and form values. If someone enters `<script>`, the site displays that text rather than executing JavaScript. Text-only means the site does not interpret submitted HTML as markup.

Each POST form includes a random CSRF token stored in the session. PHP compares it before changing data. This helps prevent another site from submitting actions using the user's logged-in session. Logout uses a protected POST form too.

## 7. Semantic HTML

The layout uses `<header>`, `<nav>`, `<main>`, and `<footer>` for page structure. Each post and comment uses `<article>`. Related content uses `<section>`, dates use `<time>`, and every visible form input has a matching `<label>`. Links navigate; buttons submit forms. This makes the structure easier to understand and improves accessibility.

## Suggested demonstration

1. Visit the feed as a guest and show the redirect to login.
2. Register account A. Show an invalid email or mismatched password error first.
3. Log in as A and create a post.
4. Open a private browser window, register account B, and add a comment to A's post.
5. Show that B can edit B's comment but cannot edit A's post.
6. Edit A's post from A's session and show the Edited badge.
7. Log out and show that the feed is protected again.
